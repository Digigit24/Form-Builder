<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResponseAnswer extends Model
{
    protected $fillable = [
        'response_id',
        'step_id',
        'answer',
    ];

    protected function casts(): array
    {
        return [
            'answer' => 'array',
        ];
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(FormStep::class, 'step_id');
    }
}
