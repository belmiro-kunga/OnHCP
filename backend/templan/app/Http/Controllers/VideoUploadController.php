<?php

namespace App\Http\Controllers;

use App\Services\VideoUploadService;
use App\Models\VideoUpload;
use App\Http\Resources\VideoUploadResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class VideoUploadController extends Controller
{
    private VideoUploadService $videoUploadService;

    public function __construct(VideoUploadService $videoUploadService)
    {
        $this->videoUploadService = $videoUploadService;
    }

    /**
     * Initialize video upload (multipart or direct)
     */
    public function initializeUpload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lessonId' => 'required|integer|exists:course_lessons,id',
            'fileName' => 'required|string|max:255',
            'fileSize' => 'required|integer|min:1',
            'contentType' => 'required|string',
            'chunkSize' => 'nullable|integer|min:5242880', // 5MB minimum for S3 multipart
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors()
            ], 422);
        }

        try {
            $lessonId = $request->input('lessonId');
            $fileName = $request->input('fileName');
            $fileSize = $request->input('fileSize');
            $contentType = $request->input('contentType');
            $chunkSize = $request->input('chunkSize', 10 * 1024 * 1024); // 10MB default

            // For files larger than 100MB, use multipart upload
            if ($fileSize > 100 * 1024 * 1024) {
                $totalParts = ceil($fileSize / $chunkSize);
                
                $uploadData = $this->videoUploadService->initializeMultipartUpload($lessonId, $fileName, $fileSize, $contentType);
                $presignedUrls = $this->videoUploadService->generatePresignedUrls(
                    $uploadData['uploadId'],
                    $uploadData['key'],
                    $totalParts
                );

                return response()->json([
                    'data' => [
                        'uploadId' => $uploadData['uploadId'],
                        'key' => $uploadData['key'],
                        'uploadType' => 'multipart',
                        'totalParts' => $totalParts,
                        'chunkSize' => $chunkSize,
                        'presignedUrls' => $presignedUrls,
                    ],
                    'message' => 'Multipart upload initialized successfully'
                ]);
            } else {
                // For smaller files, return direct upload URL
                return response()->json([
                    'data' => [
                        'uploadType' => 'direct',
                        'message' => 'Use direct upload for files under 100MB'
                    ],
                    'message' => 'Direct upload recommended for this file size'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Video upload initialization failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to initialize upload',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete multipart upload
     */
    public function completeUpload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'uploadId' => 'required|string',
            'key' => 'required|string',
            'parts' => 'required|array',
            'parts.*.PartNumber' => 'required|integer',
            'parts.*.ETag' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors()
            ], 422);
        }

        try {
            $uploadId = $request->input('uploadId');
            $key = $request->input('key');
            $parts = $request->input('parts');

            $result = $this->videoUploadService->completeMultipartUpload($uploadId, $key, $parts);
            $playbackUrls = $this->videoUploadService->generatePlaybackUrls($key);

            return response()->json([
                'data' => [
                    'key' => $result['key'],
                    'location' => $result['location'],
                    'etag' => $result['etag'],
                    'playbackUrls' => $playbackUrls,
                ],
                'message' => 'Video upload completed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Video upload completion failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to complete upload',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Direct upload for small files
     */
    public function directUpload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lessonId' => 'required|integer|exists:course_lessons,id',
            'video' => 'required|file|mimes:mp4,avi,mov,wmv,flv,webm|max:102400', // 100MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors()
            ], 422);
        }

        try {
            $lessonId = $request->input('lessonId');
            $file = $request->file('video');
            $result = $this->videoUploadService->uploadDirect($lessonId, $file);
            $playbackUrls = $this->videoUploadService->generatePlaybackUrls($result['key']);

            return response()->json([
                'data' => [
                    'key' => $result['key'],
                    'url' => $result['url'],
                    'size' => $result['size'],
                    'original_name' => $result['original_name'],
                    'videoUploadId' => $result['videoUploadId'],
                    'playbackUrls' => $playbackUrls,
                ],
                'message' => 'Video uploaded successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Direct video upload failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to upload video',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Abort multipart upload
     */
    public function abortUpload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'uploadId' => 'required|string',
            'key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors()
            ], 422);
        }

        try {
            $uploadId = $request->input('uploadId');
            $key = $request->input('key');

            $success = $this->videoUploadService->abortMultipartUpload($uploadId, $key);

            if ($success) {
                return response()->json([
                    'message' => 'Upload aborted successfully'
                ]);
            } else {
                return response()->json([
                    'error' => 'Failed to abort upload'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Failed to abort upload: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to abort upload',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get playback URLs for a video
     */
    public function getPlaybackUrls(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors()
            ], 422);
        }

        try {
            $key = $request->input('key');
            $playbackUrls = $this->videoUploadService->generatePlaybackUrls($key);
            $metadata = $this->videoUploadService->getVideoMetadata($key);

            return response()->json([
                'data' => [
                    'playbackUrls' => $playbackUrls,
                    'metadata' => $metadata,
                ],
                'message' => 'Playback URLs retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get playback URLs: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to get playback URLs',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a video
     */
    public function deleteVideo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors()
            ], 422);
        }

        try {
            $key = $request->input('key');
            $success = $this->videoUploadService->deleteVideo($key);

            if ($success) {
                return response()->json([
                    'message' => 'Video deleted successfully'
                ]);
            } else {
                return response()->json([
                    'error' => 'Failed to delete video'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete video: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to delete video',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get upload status by ID
     */
    public function getUploadStatus(int $uploadId): JsonResponse
    {
        try {
            $videoUpload = VideoUpload::findOrFail($uploadId);
            
            return response()->json([
                'data' => new VideoUploadResource($videoUpload),
                'message' => 'Upload status retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get upload status: ' . $e->getMessage());
            return response()->json([
                'error' => 'Upload not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get all uploads for a lesson
     */
    public function getLessonUploads(int $lessonId): JsonResponse
    {
        try {
            $uploads = VideoUpload::where('course_lesson_id', $lessonId)
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'data' => VideoUploadResource::collection($uploads),
                'message' => 'Lesson uploads retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get lesson uploads: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to retrieve uploads',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}