<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormStep extends Model
{
    use HasUuids;

    protected $fillable = [
        'form_id',
        'type',
        'question',
        'options',
        'order_index',
        'logic',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'logic' => 'array',
            'order_index' => 'integer',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
