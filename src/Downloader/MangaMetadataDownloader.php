<?php

namespace App\Downloader;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

/**
 * Downloads metadata of a chosen manga
 */
readonly class MangaMetadataDownloader
{
    /**
     * @var GuzzleClient Http client to download a manga's metadata
     */
    private GuzzleClient $mangaMetadataDownloader;

    /**
     * @param string $mangaMetadataApiUri Api endpoint to download manga metadata from
     * @param LoggerInterface $logger
     */
    public function __construct(
        private string          $mangaMetadataApiUri,
        private LoggerInterface $logger
    )
    {
        $this->mangaMetadataDownloader = new GuzzleClient(['base_uri' => $this->mangaMetadataApiUri]);
    }

    /**
     * Downloads a manga's metadata by slug url
     *
     * @param string $slugUrl A manga's slug from url
     * @return array A manga's metadata
     * @throws GuzzleException if request to api failed
     * @throws \JsonException if response is not a valid json
     */
    public function downloadMangaMetadata(string $slugUrl): array
    {
        try {
            $response = $this->mangaMetadataDownloader->get($slugUrl, [
                'query' => [
                    'fields' => [
                        'eng_name',
                        'otherNames',
                        'summary',
                        'releaseDate',
                        'type_id',
                        'genres',
                        'tags',
                        'authors',
                        'publisher',
                        'chap_count',
                        'status_id',
                        'artists'
                    ]
                ]
            ]);
        } catch (GuzzleException $guzzleException) {
            $this->logger->critical("Chapters list download failed!", ['requestUrl' => $this->mangaMetadataApiUri.$slugUrl]);
            throw $guzzleException;
        }

        try {
            $responseJson = json_decode(json: $response->getBody()->getContents(), associative: true, flags: JSON_THROW_ON_ERROR);
            $this->logger->info("Manga's metadata downloaded successfully");
            //todo: add dto
            return [
                'title'         => $responseJson['data']['name'],
                'summary'       => $responseJson['data']['summary'],
                'releaseYear'   => $responseJson['data']['releaseDate'],
                'chaptersCount' => $responseJson['data']['items_count']['uploaded']
            ];
        } catch (\JsonException $jsonException) {
            $this->logger->critical(
                "Chapters list response is not a valid json!", ['responseBody' => $response->getBody()->getContents()]
            );
            throw $jsonException;
        }
    }
}