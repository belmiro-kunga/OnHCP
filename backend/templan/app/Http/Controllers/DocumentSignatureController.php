<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentSignature;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DocumentSignatureController extends Controller
{
    /**
     * Display a listing of document signatures for a user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $query = DocumentSignature::with(['document:id,name,type,description', 'user:id,name'])
            ->where('user_id', $user->id)
            ->select('id', 'document_id', 'user_id', 'status', 'signed_at', 'rejection_reason', 'created_at');

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $signatures = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($signatures);
    }

    /**
     * Get pending documents for signature.
     */
    public function pending(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Get documents that require signature and are not yet signed by this user
        $documents = Document::with(['department:id,name'])
            ->where('requires_signature', true)
            ->where('status', 'active')
            ->whereDoesntHave('signatures', function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('status', 'signed');
            })
            ->select('id', 'name', 'description', 'type', 'file_name', 'department_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($documents);
    }

    /**
     * Sign a document.
     */
    public function sign(Request $request, Document $document): JsonResponse
    {
        $user = Auth::user();
        
        // Check if document requires signature
        if (!$document->requires_signature) {
            return response()->json([
                'message' => 'This document does not require signature'
            ], 422);
        }

        // Check if document is active
        if ($document->status !== 'active') {
            return response()->json([
                'message' => 'This document is not available for signature'
            ], 422);
        }

        // Check if user already signed this document
        $existingSignature = DocumentSignature::where('document_id', $document->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingSignature && $existingSignature->status === 'signed') {
            return response()->json([
                'message' => 'You have already signed this document'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'signature_data' => 'required|string', // Base64 encoded signature image
            'accept_terms' => 'required|boolean|accepted'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create or update signature
        if ($existingSignature) {
            $existingSignature->markAsSigned(
                $request->signature_data,
                $request->ip(),
                $request->userAgent()
            );
            $signature = $existingSignature;
        } else {
            $signature = DocumentSignature::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'signature_data' => $request->signature_data,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'signed_at' => now(),
                'status' => 'signed'
            ]);
        }

        $signature->load(['document:id,name', 'user:id,name']);

        return response()->json([
            'message' => 'Document signed successfully',
            'signature' => $signature
        ]);
    }

    /**
     * Reject a document signature.
     */
    public function reject(Request $request, Document $document): JsonResponse
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if signature exists
        $signature = DocumentSignature::where('document_id', $document->id)
            ->where('user_id', $user->id)
            ->first();

        if ($signature) {
            $signature->markAsRejected($request->reason);
        } else {
            $signature = DocumentSignature::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'status' => 'rejected',
                'rejection_reason' => $request->reason
            ]);
        }

        $signature->load(['document:id,name', 'user:id,name']);

        return response()->json([
            'message' => 'Document signature rejected',
            'signature' => $signature
        ]);
    }

    /**
     * Get signature details.
     */
    public function show(DocumentSignature $signature): JsonResponse
    {
        // Check if user can view this signature
        if ($signature->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $signature->load(['document:id,name,type,description', 'user:id,name']);
        
        return response()->json($signature);
    }

    /**
     * Get document signature status for current user.
     */
    public function status(Document $document): JsonResponse
    {
        $user = Auth::user();
        
        $signature = DocumentSignature::where('document_id', $document->id)
            ->where('user_id', $user->id)
            ->first();

        $status = $signature ? $signature->status : 'pending';
        
        return response()->json([
            'status' => $status,
            'signature' => $signature ? $signature->only(['id', 'signed_at', 'rejection_reason']) : null
        ]);
    }

    /**
     * Admin: Get all signatures for a document.
     */
    public function documentSignatures(Document $document): JsonResponse
    {
        // Check if user is admin
        if (!Auth::user()->hasRole('admin')) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $signatures = DocumentSignature::with(['user:id,name,email'])
            ->where('document_id', $document->id)
            ->select('id', 'user_id', 'status', 'signed_at', 'rejection_reason', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($signatures);
    }

    /**
     * Admin: Get signature statistics.
     */
    public function statistics(): JsonResponse
    {
        // Check if user is admin
        if (!Auth::user()->hasRole('admin')) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $stats = [
            'total_signatures' => DocumentSignature::count(),
            'signed_count' => DocumentSignature::where('status', 'signed')->count(),
            'pending_count' => DocumentSignature::where('status', 'pending')->count(),
            'rejected_count' => DocumentSignature::where('status', 'rejected')->count(),
            'documents_requiring_signature' => Document::where('requires_signature', true)
                ->where('status', 'active')
                ->count()
        ];

        return response()->json($stats);
    }
}