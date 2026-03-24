<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'category',
        'score',
        'weight',
        'added_by'
    ];

    // 🔗 Relationships
    public function student() {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function class() {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function teacher() {
        return $this->belongsTo(User::class, 'added_by');
    }
}