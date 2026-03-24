<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class AttendanceRecord extends Model {
    use HasFactory;
    public function student() {
        return $this->belongsTo(User::class,'student_id');
    }

    public function class() {
        return $this->belongsTo(Classes::class,'class_id');
    }

    public function teacher() {
        return $this->belongsTo(User::class,'marked_by');
    }
}
