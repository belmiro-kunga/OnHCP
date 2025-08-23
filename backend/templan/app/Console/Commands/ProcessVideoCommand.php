<?php

namespace App\Console\Commands;

use App\Models\VideoUpload;
use App\Jobs\ProcessVideoJob;
use Illuminate\Console\Command;

class ProcessVideoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:process {upload_id : The ID of the video upload to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process a video upload manually';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $uploadId = $this->argument('upload_id');
        
        $videoUpload = VideoUpload::find($uploadId);
        
        if (!$videoUpload) {
            $this->error("Video upload with ID {$uploadId} not found.");
            return 1;
        }
        
        if ($videoUpload->status === 'processing') {
            $this->error("Video upload {$uploadId} is already being processed.");
            return 1;
        }
        
        if ($videoUpload->status === 'completed') {
            $this->warn("Video upload {$uploadId} is already completed.");
            $this->ask('Do you want to reprocess it? (y/N)', 'n');
            
            if (strtolower($this->ask('Do you want to reprocess it? (y/N)', 'n')) !== 'y') {
                return 0;
            }
        }
        
        $this->info("Starting video processing for upload ID: {$uploadId}");
        $this->info("Original filename: {$videoUpload->original_filename}");
        $this->info("S3 Key: {$videoUpload->s3_key}");
        $this->info("Current status: {$videoUpload->status}");
        
        try {
            // Dispatch the job
            ProcessVideoJob::dispatch($videoUpload);
            
            $this->info("Video processing job has been dispatched successfully.");
            $this->info("You can monitor the progress by checking the video upload status.");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to dispatch video processing job: {$e->getMessage()}");
            return 1;
        }
    }
}