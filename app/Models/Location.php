<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\Location\Category;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Location extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'name',
        'category',
        'latitude',
        'longitude',
        'authors',
        'external_id',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'category' => Category::class,
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'published_at' => 'datetime',
    ];

    /**
     * Get the images for the location.
     *
     * @return HasMany
     */
    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    /**
     * Get the infos for the location.
     *
     * @return HasMany
     */
    public function infos(): HasMany
    {
        return $this->hasMany(Info::class);
    }

    /**
     * Scope a query to only include published locations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    /**
     * Scope a query to only include pending locations (not published).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->whereNull('published_at');
    }

    /**
     * Approve the location by setting published_at.
     *
     * @return bool
     */
    public function approve(): bool
    {
        return $this->update(['published_at' => now()]);
    }

    /**
     * Check if location is pending approval.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return is_null($this->published_at);
    }

    /**
     * Check if location is approved.
     *
     * @return bool
     */
    public function isApproved(): bool
    {
        return !is_null($this->published_at);
    }
}
