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
     * Test connection to Kobo API before attempting data operations.
     *
     * @return bool true if connection is successful
     * @throws Exception when connection fails
     */
    public function validateConnection(): bool
    {
        Log::info('Validating Kobo API connection for non-accessible locations');

        if ( ! $this->koboClient->testConnection()) {
            $errorMessage = 'Kobo API connection validation failed - cannot proceed with data operations';

            Log::error($errorMessage, [
                'service' => 'NonAccessibleLocationsService',
                'operation' => 'connection_validation',
            ]);

            throw new Exception($errorMessage);
        }

        Log::info('Kobo API connection validation successful for non-accessible locations');
        return true;
    }

    /**
     * Fetch non-accessible locations data from Kobo API.
     *
     * @return array<string, mixed>
     * @throws Exception when connection fails or returns non-200 status
     */
    public function fetchData(): array
    {
        $formId = config('kobo.non_accessible_form_id');

        if (empty($formId)) {
            throw new InvalidArgumentException('Non-accessible form ID is not configured');
        }

        // Validate connection before attempting to fetch data
        $this->validateConnection();

        try {
            Log::info('Fetching non-accessible locations from Kobo', [
                'form_id' => $formId,
            ]);

            // KoboClient now throws exceptions for non-200 responses
            $response = $this->koboClient->getFormData($formId);

            $data = $response->json();

            Log::info('Successfully fetched non-accessible locations', [
                'count' => $data['count'] ?? 0,
                'results_count' => count($data['results'] ?? []),
            ]);

            return $data;

        } catch (RequestException $e) {
            $errorMessage = 'Kobo API returned non-200 status for non-accessible locations';

            Log::error($errorMessage, [
                'form_id' => $formId,
                'status_code' => $e->response->status(),
                'error' => $e->getMessage(),
                'service' => 'NonAccessibleLocationsService',
            ]);

            throw new Exception("{$errorMessage}: {$e->getMessage()}", 0, $e);

        } catch (Exception $e) {
            Log::error('Failed to fetch non-accessible locations from Kobo', [
                'form_id' => $formId,
                'error' => $e->getMessage(),
                'service' => 'NonAccessibleLocationsService',
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

            // Connection is already validated in fetchData(), no need to re-validate
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
