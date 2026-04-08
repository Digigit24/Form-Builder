<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    use BelongsToTenant, HasUuids;

    protected $fillable = [
        'tenant_id',
        'title',
        'slug',
        'description',
        'is_published',
        'theme_config',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'theme_config' => 'array',
        ];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(FormStep::class)->orderBy('order_index');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }
}
