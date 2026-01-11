<?php

declare(strict_types=1);

namespace App\Services\Kobo;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class KoboClient
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = config('kobo.api_url');
        $this->token = config('kobo.api_token');
    }

    /**
     * Make a GET request to the Kobo API.
     *
     * @throws Exception when connection fails or returns non-200 status
     */
    public function get(string $endpoint, array $params = []): Response
    {
        $url = $this->buildUrl($endpoint);

        Log::info('Making request to Kobo API', [
            'url' => $url,
            'params' => $params,
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
            ])
                ->timeout(60)
                ->get($url, $params);

            if ( ! $response->successful()) {
                $errorMessage = "Kobo API request failed with status {$response->status()}";

                Log::error('Kobo API connection failed - non-200 status returned', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'params' => $params,
                    'error' => $errorMessage,
                ]);

                throw new RequestException($response);
            }

            Log::info('Kobo API request successful', [
                'url' => $url,
                'status' => $response->status(),
            ]);

            return $response;

        } catch (ConnectionException $e) {
            Log::error('Kobo API connection failed - network error', [
                'url' => $url,
                'error' => $e->getMessage(),
                'params' => $params,
            ]);

            throw new Exception("Failed to connect to Kobo API: {$e->getMessage()}", 0, $e);

        } catch (RequestException $e) {
            // Re-throw RequestException for non-200 status codes
            throw $e;

        } catch (Exception $e) {
            Log::error('Kobo API request failed - unexpected error', [
                'url' => $url,
                'error' => $e->getMessage(),
                'params' => $params,
            ]);

            throw new Exception("Kobo API request failed: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Get form data from a specific asset.
     *
     * @throws Exception when connection fails or returns non-200 status
     */
    public function getFormData(string $id, array $params = []): Response
    {
        $endpoint = "/api/v2/assets/{$id}/data";
        $defaultParams = ['format' => 'json'];

        return $this->get($endpoint, array_merge($defaultParams, $params));
    }

    /**
     * Test connection to Kobo API.
     *
     * @return bool true if connection is successful, false otherwise
     */
    public function testConnection(): bool
    {
        try {
            Log::info('Testing Kobo API connection');

            // Use a lightweight endpoint to test connectivity
            $response = $this->get('/api/v2/assets/', ['limit' => 1]);

            Log::info('Kobo API connection test successful');
            return true;

        } catch (Exception $e) {
            Log::error('Kobo API connection test failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Build the full URL for the API endpoint.
     */
    private function buildUrl(string $endpoint): string
    {
        return mb_rtrim($this->baseUrl, '/') . '/' . mb_ltrim($endpoint, '/');
    }
}
