<?php

namespace App\Console\Commands;

use App\Models\VideoUpload;
use Illuminate\Console\Command;

class ListVideoUploadsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:list {--status= : Filter by status} {--lesson= : Filter by lesson ID} {--limit=10 : Number of records to show}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List video uploads with their current status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = VideoUpload::with('lesson');
        
        // Apply filters
        if ($status = $this->option('status')) {
            $query->where('status', $status);
        }
        
        if ($lessonId = $this->option('lesson')) {
            $query->where('course_lesson_id', $lessonId);
        }
        
        $limit = (int) $this->option('limit');
        $uploads = $query->orderBy('created_at', 'desc')
                        ->limit($limit)
                        ->get();
        
        if ($uploads->isEmpty()) {
            $this->info('No video uploads found.');
            return 0;
        }
        
        $headers = ['ID', 'Filename', 'Lesson', 'Status', 'Type', 'Size', 'Progress', 'Created', 'Updated'];
        $rows = [];
        
        foreach ($uploads as $upload) {
            $rows[] = [
                $upload->id,
                $this->truncate($upload->original_filename, 30),
                $upload->lesson ? $this->truncate($upload->lesson->title, 20) : 'N/A',
                $this->getStatusWithColor($upload->status),
                $upload->upload_type,
                $this->formatFileSize($upload->file_size),
                $upload->progress_percentage ? $upload->progress_percentage . '%' : 'N/A',
                $upload->created_at->format('Y-m-d H:i'),
                $upload->updated_at->format('Y-m-d H:i'),
            ];
        }
        
        $this->table($headers, $rows);
        
        // Show summary
        $statusCounts = VideoUpload::selectRaw('status, COUNT(*) as count')
                                  ->groupBy('status')
                                  ->pluck('count', 'status')
                                  ->toArray();
        
        $this->info('\nStatus Summary:');
        foreach ($statusCounts as $status => $count) {
            $this->line("  {$status}: {$count}");
        }
        
        return 0;
    }
    
    /**
     * Get status with color formatting
     */
    private function getStatusWithColor($status)
    {
        switch ($status) {
            case 'completed':
                return "<fg=green>{$status}</>"; 
            case 'processing':
                return "<fg=yellow>{$status}</>";
            case 'failed':
                return "<fg=red>{$status}</>";
            case 'uploaded':
                return "<fg=blue>{$status}</>";
            default:
                return $status;
        }
    }
    
    /**
     * Truncate string to specified length
     */
    private function truncate($string, $length)
    {
        return strlen($string) > $length ? substr($string, 0, $length - 3) . '...' : $string;
    }
    
    /**
     * Format file size in human readable format
     */
    private function formatFileSize($bytes)
    {
        if ($bytes === null) return 'N/A';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}