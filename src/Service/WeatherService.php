<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface; // Corrected import

class WeatherService
{
    private $httpClient;
    private $apiKey;

    // Corrected type hint for ParameterBagInterface
    public function __construct(HttpClientInterface $httpClient, ParameterBagInterface $params)
    {
        $this->httpClient = $httpClient;
        // Use the corrected parameter name from services.yaml
        $this->apiKey = $params->get('openweathermap_api_key'); 
    }

    public function getCurrentWeather(string $location): array
    {
        if ($this->apiKey === 'FAKE_WEATHER') {
            // Generate Fake Weather Data
            $descriptions = ['clear sky', 'few clouds', 'scattered clouds', 'broken clouds', 'shower rain', 'rain', 'thunderstorm', 'snow', 'mist'];
            $icons = ['01d', '02d', '03d', '04d', '09d', '10d', '11d', '13d', '50d']; // Corresponding day icons
            $cities = ['Fakeville', 'Mockburg', 'Testington', 'Sample City', 'Demo Town'];

            return [
                'temperature' => rand(5, 30),
                'feels_like' => rand(3, 32),
                'description' => $descriptions[array_rand($descriptions)],
                'icon' => $icons[array_rand($icons)],
                'city' => $cities[array_rand($cities)], // Removed (Fake) suffix
                'country' => 'FK',
            ];
        }

        if (empty($this->apiKey)) {
            return ['error' => 'Weather API key is not configured.'];
        }

        // Validate if API key is the placeholder from .env.dist or similar
        if ($this->apiKey === 'YOUR_OPENWEATHERMAP_API_KEY_HERE' || $this->apiKey === '' || str_starts_with($this->apiKey, 'env(')) {
             return ['error' => 'Invalid Weather API key configuration. Please check your .env file.'];
        }

        $url = 'https://api.openweathermap.org/data/2.5/weather';

        try {
            $response = $this->httpClient->request('GET', $url, [
                'query' => [
                    'q' => $location,
                    'appid' => $this->apiKey,
                    'units' => 'metric' // Or 'imperial' for Fahrenheit
                ],
                'headers' => [
                    'Accept' => 'application/json',
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                 // Try to get more details from the API response if it's an error
                $errorDetails = $response->toArray(false); // Don't throw on non-200 for details
                $message = $errorDetails['message'] ?? 'No additional details from API.';
                return [
                    'error' => 'Weather API request failed.', 
                    'details' => 'Status: ' . $response->getStatusCode() . '. Message: ' . $message
                ];
            }

            $data = $response->toArray(); // Throws on non-200 if not handled above

            // Extract only the data we need
            return [
                'temperature' => $data['main']['temp'] ?? null,
                'feels_like' => $data['main']['feels_like'] ?? null,
                'description' => $data['weather'][0]['description'] ?? null,
                'icon' => $data['weather'][0]['icon'] ?? null, // Icon code
                'city' => $data['name'] ?? null,
                'country' => $data['sys']['country'] ?? null,
            ];

        } catch (\Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface $e) {
            // Specific for network/transport errors
             return ['error' => 'Weather API transport error.', 'details' => $e->getMessage()];
        } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {
            // Specific for 4xx errors
            $errorContent = $e->getResponse()->toArray(false);
            $message = $errorContent['message'] ?? $e->getMessage();
            return ['error' => 'Weather API client error.', 'details' => 'Status: ' . $e->getResponse()->getStatusCode() . '. Message: ' . $message];
        } catch (\Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface $e) {
            // Specific for 5xx errors from OpenWeatherMap
            $errorContent = $e->getResponse()->toArray(false);
            $message = $errorContent['message'] ?? $e->getMessage();
            return ['error' => 'Weather API server error.', 'details' => 'Status: ' . $e->getResponse()->getStatusCode() . '. Message: ' . $message];
        } 
        catch (\Exception $e) { // General fallback
            return ['error' => 'An exception occurred while calling Weather API.', 'details' => $e->getMessage()];
        }
    }
}
