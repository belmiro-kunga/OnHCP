<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'requires_signature',
        'is_mandatory',
        'department_id',
        'uploaded_by',
        'status'
    ];

    protected $casts = [
        'requires_signature' => 'boolean',
        'is_mandatory' => 'boolean',
        'file_size' => 'integer'
    ];

    /**
     * Get the department that owns the document.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the user who uploaded the document.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the signatures for this document.
     */
    public function signatures(): HasMany
    {
        return $this->hasMany(DocumentSignature::class);
    }

    /**
     * Scope a query to only include active documents.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include mandatory documents.
     */
    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    /**
     * Get the file URL.
     */
    public function getFileUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get the formatted file size.
     */
    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if document is signed by a specific user.
     */
    public function isSignedBy(User $user)
    {
        return $this->signatures()
            ->where('user_id', $user->id)
            ->where('status', 'signed')
            ->exists();
    }

    /**
     * Get signature status for a specific user.
     */
    public function getSignatureStatusFor(User $user)
    {
        $signature = $this->signatures()
            ->where('user_id', $user->id)
            ->first();
            
        return $signature ? $signature->status : 'pending';
    }
}