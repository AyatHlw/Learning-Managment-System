<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PremiumUsers extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'end_date'
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
