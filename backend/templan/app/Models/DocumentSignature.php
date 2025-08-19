<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'signature_data',
        'ip_address',
        'user_agent',
        'signed_at',
        'status',
        'rejection_reason'
    ];

    protected $casts = [
        'signed_at' => 'datetime'
    ];

    /**
     * Get the document that owns the signature.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the user who signed the document.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include signed documents.
     */
    public function scopeSigned($query)
    {
        return $query->where('status', 'signed');
    }

    /**
     * Scope a query to only include pending signatures.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include rejected signatures.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if the signature is signed.
     */
    public function isSigned()
    {
        return $this->status === 'signed';
    }

    /**
     * Check if the signature is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the signature is rejected.
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Mark the signature as signed.
     */
    public function markAsSigned($signatureData, $ipAddress, $userAgent)
    {
        $this->update([
            'signature_data' => $signatureData,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'signed_at' => now(),
            'status' => 'signed'
        ]);
    }

    /**
     * Mark the signature as rejected.
     */
    public function markAsRejected($reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason
        ]);
    }
}