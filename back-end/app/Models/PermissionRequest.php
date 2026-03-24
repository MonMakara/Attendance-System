<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PermissionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'start_date',
        'end_date',
        'reason_type',
        'description',
        'attachment_url',
        'status',
        'processed_by',
        'processed_at'
    ];

    // 🔗 Relationships
    public function student() {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function class() {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function processedBy() {
        return $this->belongsTo(User::class, 'processed_by');
    }
}