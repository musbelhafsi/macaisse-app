<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity','status','total_rows','processed_rows','success_count','error_count','errors','file_path','created_by','started_at','finished_at'
    ];

    protected $casts = [
        'errors' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class, 'created_by'); }
    public function creator() { return $this->user(); }
}