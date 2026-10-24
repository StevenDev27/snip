<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Click extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['link_id', 'clicked_at', 'referer', 'user_agent'];

    protected $casts = ['clicked_at' => 'datetime'];

    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }
}
