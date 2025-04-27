<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

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
            ['headers' => $this->getHeaders(), 'query' => ['limit' => 1300]]
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
     * @param string|null $uploadDir
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function fetchExerciseDetails(string $apiId, ?string $uploadDir = null): array
    {
        $formattedId = str_pad(ltrim($apiId, '0'), 4, '0', STR_PAD_LEFT);

        $response = $this->httpClient->request(
            'GET',
            self::API_BASE_URL."/exercises/exercise/{$formattedId}",
            ['headers' => $this->getHeaders()]
        );

        $data = $response->toArray();

        // Download and save image if upload directory is provided
        if ($uploadDir && isset($data['gifUrl'])) {
            try {
                $filesystem = new Filesystem();
                $imageResponse = $this->httpClient->request('GET', $data['gifUrl']);

                if (200 === $imageResponse->getStatusCode()) {
                    if (!$filesystem->exists($uploadDir)) {
                        $filesystem->mkdir($uploadDir);
                    }

                    $filename = 'exercise_'.$formattedId.'_'.md5(uniqid()).'.gif';
                    $filePath = $uploadDir.'/'.$filename;
                    $filesystem->dumpFile($filePath, $imageResponse->getContent());

                    $data['gifUrl'] = '/uploads/exercises/'.$filename;
                }
            } catch (\Exception $e) {
                // Log error but continue with remote URL
                error_log('Failed to save exercise image: '.$e->getMessage());
            }
        }

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