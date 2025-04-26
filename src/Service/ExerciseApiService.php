<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ExerciseApiService
{
    private const API_BASE_URL = 'https://exercisedb.p.rapidapi.com';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey
    ) {}

    /**
     * @return array
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function getBodyPartList(): array
    {
        $response = $this->httpClient->request(
            'GET',
            self::API_BASE_URL.'/exercises/bodyPartList',
            ['headers' => $this->getHeaders()]
        );
        return $response->toArray();
    }

    /**
     * @param string $bodyPart
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getExercisesByBodyPart(string $bodyPart): array
    {
        $response = $this->httpClient->request(
            'GET',
            self::API_BASE_URL."/exercises/bodyPart/{$bodyPart}",
            ['headers' => $this->getHeaders()]
        );
        return $response->toArray();
    }

    /**
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getAllExerciseNames(): array
    {
        $response = $this->httpClient->request(
            'GET',
            self::API_BASE_URL.'/exercises',
            ['headers' => $this->getHeaders(), 'query' => ['limit' => 1300]] // Added limit parameter
        );

        $exercises = $response->toArray();

        return array_map(fn($ex) => [
            'id' => $ex['id'],
            'name' => $ex['name'],
            'bodyPart' => $ex['bodyPart'],
            'target' => $ex['target'],
            'equipment' => $ex['equipment'] ?? null,
            'gifUrl' => $ex['gifUrl'] ?? null
        ], $exercises);
    }

    /**
     * @param string $apiId
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function fetchExerciseDetails(string $apiId): array
    {
        // Convert ID to 4-digit format with leading zeros
        $formattedId = str_pad(ltrim($apiId, '0'), 4, '0', STR_PAD_LEFT);

        $response = $this->httpClient->request(
            'GET',
            self::API_BASE_URL."/exercises/exercise/{$formattedId}",
            ['headers' => $this->getHeaders()]
        );

        $data = $response->toArray();

        return [
            'id' => $data['id'],
            'name' => $data['name'],
            'target' => $data['target'],
            'bodyPart' => $data['bodyPart'],
            'equipment' => $data['equipment'] ?? null,
            'gifUrl' => $data['gifUrl'] ?? null,
            'instructions' => $data['instructions'] ?? []
        ];
    }

    private function getHeaders(): array
    {
        return [
            'X-RapidAPI-Host' => 'exercisedb.p.rapidapi.com',
            'X-RapidAPI-Key' => $this->apiKey,
        ];
    }
}