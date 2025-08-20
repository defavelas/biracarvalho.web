<?php

declare(strict_types=1);

namespace App\Services\Kobo;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

final class NonAccessibleLocationsService
{
    public function __construct(private readonly KoboClient $koboClient) {}

    /**
     * Fetch non-accessible locations data from Kobo API.
     *
     * @return array<string, mixed>
     */
    public function fetchData(): array
    {
        $formId = config('kobo.non_accessible_form_id');

        if (empty($formId)) {
            throw new InvalidArgumentException('Non-accessible form ID is not configured');
        }

        try {
            Log::info('Fetching non-accessible locations from Kobo', [
                'form_id' => $formId,
            ]);

            $response = $this->koboClient->getFormData($formId);

            if ( ! $response->successful()) {
                throw new RequestException($response);
            }

            $data = $response->json();

            Log::info('Successfully fetched non-accessible locations', [
                'count' => $data['count'] ?? 0,
                'results_count' => count($data['results'] ?? []),
            ]);

            return $data;

        } catch (Exception $e) {
            Log::error('Failed to fetch non-accessible locations from Kobo', [
                'form_id' => $formId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Fetch all non-accessible locations with pagination support.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchAllData(): array
    {
        $allResults = [];
        $nextUrl = null;

        do {
            $params = [];
            if ($nextUrl) {
                // Extract query parameters from next URL if pagination is supported
                $parsedUrl = parse_url($nextUrl);
                if (isset($parsedUrl['query'])) {
                    parse_str($parsedUrl['query'], $params);
                }
            }

            $data = $this->fetchData();

            if (isset($data['results']) && is_array($data['results'])) {
                $allResults = array_merge($allResults, $data['results']);
            }

            $nextUrl = $data['next'] ?? null;

        } while ($nextUrl);

        Log::info('Completed fetching all non-accessible locations', [
            'total_results' => count($allResults),
        ]);

        return $allResults;
    }
}
