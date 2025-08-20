<?php

declare(strict_types=1);

namespace App\Services\Kobo;

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
     */
    public function get(string $endpoint, array $params = []): Response
    {
        $url = $this->buildUrl($endpoint);

        Log::info('Making request to Kobo API', [
            'url' => $url,
            'params' => $params,
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])
            ->timeout(60)
            ->get($url, $params);

        if ( ! $response->successful()) {
            Log::error('Kobo API request failed', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }

        return $response;
    }

    /**
     * Get form data from a specific asset.
     */
    public function getFormData(string $id, array $params = []): Response
    {
        $endpoint = "/api/v2/assets/{$id}/data";
        $defaultParams = ['format' => 'json'];

        return $this->get($endpoint, array_merge($defaultParams, $params));
    }

    /**
     * Build the full URL for the API endpoint.
     */
    private function buildUrl(string $endpoint): string
    {
        return mb_rtrim($this->baseUrl, '/') . '/' . mb_ltrim($endpoint, '/');
    }
}
