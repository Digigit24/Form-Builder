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
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'theme_config' => 'array',
            'settings' => 'array',
        ];
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
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
