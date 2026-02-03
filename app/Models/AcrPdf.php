<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AcrPdf extends Model
{
    use HasFactory;

    protected $table = 'acr_pdfs';

    protected $fillable = [
        'acr_id',
        'employee_id',
        'reporting_year',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'checksum',
        'is_partial',
        'partial_sequence',
        'generated_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_partial' => 'boolean',
        'partial_sequence' => 'integer',
        'generated_at' => 'datetime',
    ];

    /**
     * Get the ACR this PDF belongs to
     */
    public function acr(): BelongsTo
    {
        return $this->belongsTo(ACR::class);
    }

    /**
     * Get the employee this PDF belongs to
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the full URL to the PDF
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get human-readable file size
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Verify file integrity using checksum
     */
    public function verifyIntegrity(): bool
    {
        if (!$this->checksum || !Storage::exists($this->file_path)) {
            return false;
        }

        $currentHash = hash_file('sha256', Storage::path($this->file_path));
        return $currentHash === $this->checksum;
    }

    /**
     * Delete the PDF file from storage
     */
    public function deleteFile(): bool
    {
        if (Storage::exists($this->file_path)) {
            return Storage::delete($this->file_path);
        }
        return true;
    }

    /**
     * Scope for PDFs of a specific year
     */
    public function scopeOfYear($query, string $year)
    {
        return $query->where('reporting_year', $year);
    }

    /**
     * Scope for partial ACRs
     */
    public function scopePartial($query)
    {
        return $query->where('is_partial', true);
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Delete file when model is deleted
        static::deleting(function ($pdf) {
            $pdf->deleteFile();
        });
    }
}
