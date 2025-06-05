<?php

namespace App\Downloader;

use App\Downloader\Interface\MangaMetadataDownloaderInterface;
use App\Dto\Manga\MangaMetadataDto;
use App\Factory\GuzzleClient\GuzzleClientFactory;
use App\Factory\GuzzleClient\GuzzleClientParameters;
use App\Middleware\GuzzleMiddleware\ProxyMiddleware;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

/**
 * Downloads metadata of a chosen manga
 */
readonly class MangaMetadataDownloader implements MangaMetadataDownloaderInterface
{
    /**
     * @var GuzzleClient Http client to download manga metadata
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
        $this->mangaMetadataDownloader = GuzzleClientFactory::createClient(
            guzzleClientParams: [
                GuzzleClientParameters::BASE_URI->value => $this->mangaMetadataApiUri,
                GuzzleClientParameters::VERIFY->value   => false,
            ],
            guzzleClientMiddlewares: [
                new ProxyMiddleware()
            ]
        );
    }

    /**
     * {@inheritDoc}
     * @throws GuzzleException if request to api failed
     * @throws \JsonException if response is not a valid json
     */
    public function downloadMangaMetadata(string $slugUrl): MangaMetadataDto
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
            $this->logger->critical("Manga metadata download failed!", ['requestUrl' => $this->mangaMetadataApiUri.$slugUrl]);
            throw $guzzleException;
        }

        try {
            $responseJson = json_decode(json: $response->getBody()->getContents(), associative: true, flags: JSON_THROW_ON_ERROR);
            $this->logger->info("Manga metadata downloaded successfully");

            return new MangaMetadataDto(
                title: $responseJson['data']['name'],
                summary: $responseJson['data']['summary'],
                releaseYear: $responseJson['data']['releaseDate'],
                chaptersCount: $responseJson['data']['items_count']['uploaded']
            );
        } catch (\JsonException $jsonException) {
            $this->logger->critical(
                "Manga metadata response is not a valid json!", ['responseBody' => $response->getBody()->getContents()]
            );
            throw $jsonException;
        }
    }
}