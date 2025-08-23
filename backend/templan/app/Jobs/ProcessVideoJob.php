<?php

namespace App\Jobs;

use App\Models\VideoUpload;
use App\Services\VideoProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600; // 1 hour timeout
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public VideoUpload $videoUpload
    ) {}

    /**
     * Execute the job.
     */
    public function handle(VideoProcessingService $videoProcessingService): void
    {
        try {
            Log::info('Starting video processing', [
                'video_upload_id' => $this->videoUpload->id,
                's3_key' => $this->videoUpload->s3_key,
            ]);

            // Update status to processing
            $this->videoUpload->updateStatus('processing');

            // Process the video
            $result = $videoProcessingService->processVideo($this->videoUpload);

            // Update video upload with processing results
            $this->videoUpload->update([
                'status' => 'completed',
                'mp4_url' => $result['mp4_url'] ?? null,
                'hls_url' => $result['hls_url'] ?? null,
                'thumbnail_url' => $result['thumbnail_url'] ?? null,
                'duration_seconds' => $result['duration_seconds'] ?? null,
                'processing_data' => $result['processing_data'] ?? null,
                'processing_completed_at' => now(),
            ]);

            Log::info('Video processing completed successfully', [
                'video_upload_id' => $this->videoUpload->id,
                'duration_seconds' => $result['duration_seconds'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Video processing failed', [
                'video_upload_id' => $this->videoUpload->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Mark as failed
            $this->videoUpload->markAsFailed($e->getMessage());

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Video processing job failed permanently', [
            'video_upload_id' => $this->videoUpload->id,
            'error' => $exception->getMessage(),
        ]);

        $this->videoUpload->markAsFailed(
            'Processing failed after ' . $this->tries . ' attempts: ' . $exception->getMessage()
        );
    }
}