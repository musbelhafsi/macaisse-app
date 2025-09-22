<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bordereau extends Model
{
    use HasFactory;
    protected $table = 'bordereaux'; // forcer le pluriel correct
    protected $fillable = ['numero','date_envoi','note','status','created_by'];

    public function lignes() { return $this->hasMany(BordereauLigne::class); }
}