<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'primary_color',
        'secondary_color',
        'background_color',
        'font_family',
    ];

    protected $attributes = [
        'primary_color' => '#6366f1',
        'secondary_color' => '#ec4899',
        'background_color' => '#0f172a',
        'font_family' => 'Inter',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function forms(): HasMany
    {
        return $this->hasMany(Form::class);
    }
}
