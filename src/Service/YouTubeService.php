<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class YouTubeService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient, ParameterBagInterface $params)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $params->get('youtube_api_key'); // We'll define this in services.yaml
    }

    public function searchVideos(string $query, int $maxResults = 5): array
    {
        if (empty($this->apiKey)) {
            // Or throw an exception, or return a specific error structure
            return ['error' => 'YouTube API key is not configured.'];
        }

        $url = 'https://www.googleapis.com/youtube/v3/search';
        
        try {
            $response = $this->httpClient->request('GET', $url, [
                'query' => [
                    'part' => 'snippet',
                    'q' => $query,
                    'type' => 'video',
                    'maxResults' => $maxResults,
                    'key' => $this->apiKey,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                // Handle non-200 responses (e.g., log error, return error array)
                return ['error' => 'YouTube API request failed.', 'details' => $response->getContent(false)];
            }

            $data = $response->toArray();
            $videos = [];
            foreach ($data['items'] ?? [] as $item) {
                $videos[] = [
                    'id' => $item['id']['videoId'] ?? null,
                    'title' => $item['snippet']['title'] ?? 'No title',
                    'description' => $item['snippet']['description'] ?? '',
                    'thumbnail' => $item['snippet']['thumbnails']['default']['url'] ?? null,
                ];
            }
            return $videos;

        } catch (\Exception $e) {
            // Handle exceptions (e.g., network errors, log error)
            return ['error' => 'An exception occurred while calling YouTube API.', 'details' => $e->getMessage()];
        }
    }
}
