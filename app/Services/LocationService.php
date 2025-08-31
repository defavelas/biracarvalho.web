<?php

declare(strict_types=1);

namespace App\Services;

use App\Enum\Location\Type;
use App\Models\Location;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class LocationService
{
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Get all published locations with caching for map performance.
     *
     * @return Collection<int, Location>
     */
    public function getPublishedLocationsForMap(): Collection
    {
        return Cache::remember('locations.published.map', self::CACHE_TTL, function () {
            Log::info('Loading published locations for map from database');
            
            return Location::query()
                ->published()
                ->with(['images', 'infos'])
                ->select([
                    'id',
                    'name', 
                    'type',
                    'latitude',
                    'longitude',
                    'type',
                    'authors',
                    'published_at',
                    'created_at'
                ])
                ->orderBy('published_at', 'desc')
                ->get();
        });
    }

    /**
     * Search locations with filters and caching.
     *
     * @param string $search
     * @param array<string> $categories
     * @return Collection<int, Location>
     */
    public function searchLocations(string $search = '', array $categories = []): Collection
    {
        $cacheKey = $this->generateSearchCacheKey($search, $categories);
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($search, $categories) {
            Log::debug('Searching locations in database', [
                'search' => $search,
                'categories' => $categories,
            ]);

            $query = Location::query()
                ->published()
                ->with(['images', 'infos']);

            // Apply search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('type', 'like', "%{$search}%")
                      ->orWhere('authors', 'like', "%{$search}%");
                });
            }

            // Apply type filters
            if (!empty($categories)) {
                $query->whereIn('type', $categories);
            }

            return $query
                ->orderBy('published_at', 'desc')
                ->get();
        });
    }

    /**
     * Get a specific location by ID with caching.
     *
     * @param string $locationId
     * @return Location|null
     */
    public function getLocationById(string $locationId): ?Location
    {
        return Cache::remember("location.{$locationId}", self::CACHE_TTL, function () use ($locationId) {
            return Location::query()
                ->published()
                ->with(['images', 'infos'])
                ->find($locationId);
        });
    }

    /**
     * Get location statistics for dashboard.
     *
     * @return array{total: int, published: int, pending: int, by_type: array<string, int>}
     */
    public function getLocationStats(): array
    {
        return Cache::remember('locations.stats', self::CACHE_TTL, function () {
            $total = Location::count();
            $published = Location::published()->count();
            $pending = Location::pending()->count();
            
            $byType = [];
            foreach (Type::cases() as $type) {
                $byType[$type->value] = Location::published()
                    ->where('type', $type->value)
                    ->count();
            }

            return [
                'total' => $total,
                'published' => $published,
                'pending' => $pending,
                'by_type' => $byType,
            ];
        });
    }

    /**
     * Transform location for map marker data.
     *
     * @param Location $location
     * @return array<string, mixed>
     */
    public function transformForMap(Location $location): array
    {
        return [
            'id' => $location->id,
            'name' => $location->name,
            'type' => $location->type->value,
            'typeLabel' => $location->type->label(),
            'typeColor' => $location->type->color(),
            'latitude' => (float) $location->latitude,
            'longitude' => (float) $location->longitude,
            'type' => $location->type,
            'authors' => $location->authors,
            'publishedAt' => $location->published_at?->format('d/m/Y H:i'),
            'createdAt' => $location->created_at->format('d/m/Y H:i'),
            'images' => $location->images->map(fn($image) => [
                'id' => $image->id,
                'path' => $image->image_path,
                'url' => asset('storage/' . $image->image_path),
            ])->toArray(),
            'infos' => $location->infos->map(fn($info) => [
                'id' => $info->id,
                'title' => $info->title,
                'value' => $info->value,
            ])->toArray(),
        ];
    }

    /**
     * Transform multiple locations for map.
     *
     * @param Collection<int, Location> $locations
     * @return array<int, array<string, mixed>>
     */
    public function transformCollectionForMap(Collection $locations): array
    {
        return $locations->map(fn($location) => $this->transformForMap($location))->toArray();
    }

    /**
     * Clear location caches.
     */
    public function clearCache(): void
    {
        Cache::forget('locations.published.map');
        Cache::forget('locations.stats');
        
        // Clear search caches (pattern-based clearing)
        $tags = ['locations.search'];
        if (method_exists(Cache::getStore(), 'tags')) {
            Cache::tags($tags)->flush();
        }
        
        Log::info('Location caches cleared');
    }

    /**
     * Generate cache key for search results.
     *
     * @param string $search
     * @param array<string> $categories
     */
    private function generateSearchCacheKey(string $search, array $categories): string
    {
        $searchHash = md5($search);
        
        // Ensure categories is always an array and sort it for consistent cache keys
        $categories = is_array($categories) ? $categories : [];
        sort($categories);
        $categoriesHash = md5(implode(',', $categories));
        
        return "locations.search.{$searchHash}.{$categoriesHash}";
    }
}