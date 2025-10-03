<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code'];

    //relation avec les chèques
    public function cheques()
    {
        return $this->hasMany(Cheque::class);
    }
    //relation avec les recouvrementbons
    public function recouvrementbons()
    {
        return $this->hasMany(Recouvrementbon::class);
    }
}

    
 