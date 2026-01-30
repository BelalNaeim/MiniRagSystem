<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pdf extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'file_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
