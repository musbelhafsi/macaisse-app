<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BordereauLigne extends Model
{
    use HasFactory;

    protected $fillable = ['bordereau_id','type','reference_id','numero_ref','montant','meta'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function bordereau() { return $this->belongsTo(Bordereau::class); }
}