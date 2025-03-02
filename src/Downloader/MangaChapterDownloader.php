<?php

namespace App\Downloader;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;

/**
 * Downloads the chapters of chosen manga
 */
readonly class MangaChapterDownloader
{
    /**
     * @param string $imgServerBaseUri Api endpoint to download pictures from
     * @param string $chaptersMetadataApiBaseUri Api endpoint to download chapters metadata from
     * @param int $concurrency Controls the concurrency of chapters' metadata request pool
     * @param string $mangaDirectorySavePath A path where to create manga directories to save downloaded chapters
     * @param LoggerInterface $logger
     */
    public function __construct(
        private string          $imgServerBaseUri, //todo: replace with a server selector service
        private string          $chaptersMetadataApiBaseUri,
        private int             $concurrency,
        private string          $mangaDirectorySavePath,
        private LoggerInterface $logger
    )
    {
    }

    /**
     * Downloads the chapters of a manga by manga's slug url
     *
     * @param string $slugUrl A manga slug url
     * @param string $mangaTitle Title of the manga to download, used to create parent directory for chapters
     * @return array List of every chapter metadata
     * @throws GuzzleException if request to api failed
     * @throws \JsonException if response is not a valid json
     * @throws \Throwable if pages downloading failed
     */
    public function downloadAllMangaChapters(string $slugUrl, string $mangaTitle): array
    {
        $chaptersList = $this->downloadChaptersList($slugUrl);
        $this->logger->info("Chapters list downloaded successfully");

        $chaptersMeta = $this->downloadChaptersMetadata($slugUrl, $chaptersList);
        $this->logger->info("Chapters metadata downloaded successfully");

        foreach ($chaptersMeta as &$chapterMeta) {
            $downloadPath = $this->createChapterDirectory($mangaTitle, $chapterMeta);
            $chapterMeta['filesPath'] = $downloadPath;
            if (!$this->checkChapterDirectoryIsEmpty($downloadPath)) {
                $this->logger->info("Chapter {$chapterMeta['number']} already downloaded to: {$chapterMeta['filesPath']}");
                continue;
            }
            $this->downloadChapterPages($chapterMeta);
            $this->logger->info("Chapter {$chapterMeta['number']} pages downloaded successfully to: {$chapterMeta['filesPath']}");
        }

        return $chaptersMeta;
    }

    /**
     * Downloads a manga's chapter list
     *
     * @param string $slugUrl A manga slug url
     * @return array Manga's chapters list
     * @throws GuzzleException if request to api failed
     * @throws \JsonException if response is not a valid json
     */
    private function downloadChaptersList(string $slugUrl): array
    {
        $chaptersListDownloader = new GuzzleClient(['base_uri' => $this->chaptersMetadataApiBaseUri]);
        $requestUri             = "$slugUrl/chapters";
        try {
            $chapterList = $chaptersListDownloader->get($requestUri);
        } catch (GuzzleException $guzzleException) {
            $this->logger->critical("Chapters list download failed!", ['requestUrl' => $this->chaptersMetadataApiBaseUri.$requestUri]);
            throw $guzzleException;
        }
        try {
            $chapterMetaJson = json_decode(json: $chapterList->getBody()->getContents(), associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $jsonException) {
            $this->logger->critical(
                "Chapters list response is not a valid json!", ['responseBody' => $chapterList->getBody()->getContents()]
            );
            throw $jsonException;
        }
        //todo: add dto
        return $chapterMetaJson['data'];
    }

    /**
     * Downloads the metadata of the chapters
     *
     * @param string $slugUrl A manga slug url
     * @param array $chaptersList A manga's chapters list
     * @return array List of every chapter metadata
     */
    private function downloadChaptersMetadata(string $slugUrl, array $chaptersList): array
    {
        $chapterMetadataRequests = [];
        foreach ($chaptersList as $chapter) {
            $chapterMetadataRequests[] = new Request(
                method: 'GET', uri: "$slugUrl/chapter?number={$chapter['number']}&volume={$chapter['volume']}"
            );
        }

        //todo: add dto
        $chaptersMetadata = [];
        $rejectedRequests = [];

        $chaptersMetaRequestsPool = new Pool(
            new GuzzleClient(['base_uri' => $this->chaptersMetadataApiBaseUri]),
            $chapterMetadataRequests,
            [
                'concurrency' => $this->concurrency,
                'fulfilled'   => function (Response $response, $index) use (&$chaptersMetadata) {
                    $chapterMetaJson          = json_decode($response->getBody()->getContents(), true);
                    $chaptersMetadata[$index] = [
                        'name'   => $chapterMetaJson['data']['name'],
                        'volume' => $chapterMetaJson['data']['volume'],
                        'number' => $chapterMetaJson['data']['number'],
                        'pages'  => array_map(
                            fn (array $pageMeta): array => ['image' => $pageMeta['image'], 'url' => $pageMeta['url']],
                            $chapterMetaJson['data']['pages']
                        ),
                    ];
                },
                'rejected'    => function (RequestException $reason, $index) use (&$rejectedRequests) {
                    $rejectedRequests[] = [
                        'request' => $reason->getRequest(),
                        'reason'  => $reason->getMessage()
                    ];
                },
            ]
        );

        $promises = $chaptersMetaRequestsPool->promise();
        $promises->wait();

        unset($rejectedRequests);

        return $chaptersMetadata;
    }

    /**
     * Downloads pages (pictures) to directory specified in chapter's meta 'filesPath' field
     *
     * @param array $chapterMeta Chapter metadata
     * @return void
     * @throws \Throwable if pages downloading failed
     */
    private function downloadChapterPages(array $chapterMeta): void
    {
        $pageLoader = new GuzzleClient();

        $chapterPagesPromises = [];
        foreach ($chapterMeta['pages'] as $pageMeta) {
            $sinkPath               = $chapterMeta['filesPath'].'/'.$pageMeta['image'];
            $chapterPagesPromises[] = $pageLoader->getAsync(
                $this->imgServerBaseUri.$pageMeta['url'],
                [RequestOptions::SINK => $sinkPath]
            );
        }

        try {
            Utils::unwrap($chapterPagesPromises);
        } catch (\Throwable $exception) {
            $this->logger->critical("Pages downloading failed!");
            throw $exception;
        }

        unset($chapterPagesPromises);
    }

    /**
     * Creates save directory for a manga chapter
     *
     * @param string $mangaTitle Title of the manga to download, used to create parent directory for chapters
     * @param array $chapterMeta Chapter metadata
     * @return string Path to a manga directory, where chapter pages should be saved
     */
    private function createChapterDirectory(string $mangaTitle, array $chapterMeta): string
    {
        $downloadPath = $this->mangaDirectorySavePath.escapeshellarg($mangaTitle).'/';
        $saveDirName = $chapterMeta['volume'].'_'.$chapterMeta['number'];
        $downloadPath .= $saveDirName;

        if (!is_dir($downloadPath)) {
            mkdir($downloadPath, 0777, true);
        }

        return $downloadPath;
    }

    /**
     * Checks if chapter directory created and empty
     *
     * @param string $downloadPath Path to a manga directory, where chapter pages should be saved
     * @return bool True if directory is present and empty
     */
    private function checkChapterDirectoryIsEmpty(string $downloadPath): bool
    {
        //the only 2 elements in directory should be '.' and '..'
        return is_dir($downloadPath) && count(scandir($downloadPath)) === 2;
    }
}