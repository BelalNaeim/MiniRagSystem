<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'pdf_id',
        'user_id',
        'content',
        'chunk_index',
        'vector_id',
        'section_number',
        'sort_order',
        'guid',
    ];

    protected $casts = [
        'meta_data' => 'array',
    ];

    public function pdf()
    {
        return $this->belongsTo(Pdf::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function siblings()
    {
        return Chunk::where('pdf_id', $this->pdf_id)
            ->whereIn('chunk_index', [$this->chunk_index - 1, $this->chunk_index + 1])
            ->orderBy('chunk_index')
            ->get();
    }
}
