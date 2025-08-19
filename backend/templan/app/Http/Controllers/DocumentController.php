<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Display a listing of the documents.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Document::with(['department:id,name', 'uploader:id,name'])
            ->select('id', 'name', 'description', 'type', 'file_name', 'mime_type', 
                    'file_size', 'requires_signature', 'is_mandatory', 'department_id', 
                    'uploaded_by', 'status', 'created_at');

        // Filter by department
        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by mandatory
        if ($request->has('is_mandatory') && $request->is_mandatory !== null) {
            $query->where('is_mandatory', $request->boolean('is_mandatory'));
        }

        // Filter by requires signature
        if ($request->has('requires_signature') && $request->requires_signature !== null) {
            $query->where('requires_signature', $request->boolean('requires_signature'));
        }

        // Search by name or description
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        $documents = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($documents);
    }

    /**
     * Store a newly created document.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:contract,policy,manual,form,certificate,other',
            'file' => 'required|file|mimes:pdf,doc,docx,txt,jpg,jpeg,png|max:10240', // 10MB
            'requires_signature' => 'boolean',
            'is_mandatory' => 'boolean',
            'department_id' => 'nullable|exists:departments,id',
            'status' => 'in:draft,active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('file');
        $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('documents', $fileName, 'public');

        $document = Document::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'requires_signature' => $request->boolean('requires_signature'),
            'is_mandatory' => $request->boolean('is_mandatory'),
            'department_id' => $request->department_id,
            'uploaded_by' => Auth::id(),
            'status' => $request->status ?? 'active'
        ]);

        $document->load(['department:id,name', 'uploader:id,name']);

        return response()->json([
            'message' => 'Document uploaded successfully',
            'document' => $document
        ], 201);
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document): JsonResponse
    {
        $document->load(['department:id,name', 'uploader:id,name']);
        
        return response()->json($document);
    }

    /**
     * Update the specified document.
     */
    public function update(Request $request, Document $document): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|required|in:contract,policy,manual,form,certificate,other',
            'requires_signature' => 'boolean',
            'is_mandatory' => 'boolean',
            'department_id' => 'nullable|exists:departments,id',
            'status' => 'in:draft,active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $document->update($request->only([
            'name', 'description', 'type', 'requires_signature', 
            'is_mandatory', 'department_id', 'status'
        ]));

        $document->load(['department:id,name', 'uploader:id,name']);

        return response()->json([
            'message' => 'Document updated successfully',
            'document' => $document
        ]);
    }

    /**
     * Remove the specified document.
     */
    public function destroy(Document $document): JsonResponse
    {
        // Delete the file from storage
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return response()->json([
            'message' => 'Document deleted successfully'
        ]);
    }

    /**
     * Download the specified document.
     */
    public function download(Document $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            return response()->json([
                'message' => 'File not found'
            ], 404);
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Get document types for dropdown.
     */
    public function types(): JsonResponse
    {
        $types = [
            ['value' => 'contract', 'label' => 'Contract'],
            ['value' => 'policy', 'label' => 'Policy'],
            ['value' => 'manual', 'label' => 'Manual'],
            ['value' => 'form', 'label' => 'Form'],
            ['value' => 'certificate', 'label' => 'Certificate'],
            ['value' => 'other', 'label' => 'Other']
        ];

        return response()->json($types);
    }

    /**
     * Get departments for dropdown.
     */
    public function departments(): JsonResponse
    {
        $departments = Department::select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($departments);
    }

    /**
     * Replace document file.
     */
    public function replaceFile(Request $request, Document $document): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,doc,docx,txt,jpg,jpeg,png|max:10240' // 10MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Delete old file
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Upload new file
        $file = $request->file('file');
        $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('documents', $fileName, 'public');

        $document->update([
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize()
        ]);

        return response()->json([
            'message' => 'Document file replaced successfully',
            'document' => $document
        ]);
    }
}