<?php

namespace App\Downloader;

use App\Downloader\Interface\MangaChapterDownloaderInterface;
use App\Dto\Manga\MangaChaptersListItemDto;
use App\Dto\Manga\MangaChaptersListDto;
use App\Dto\Manga\MangaChaptersMetadataListDto;
use App\Dto\Manga\MangaChaptersMetadataListItemDto;
use App\Dto\Manga\MangaChaptersMetadataListItemPageFileDto;
use App\Factory\GuzzleClient\GuzzleClientFactory;
use App\Factory\GuzzleClient\GuzzleClientParameters;
use App\Factory\GuzzleClient\GuzzleRequestParameters;
use App\Middleware\GuzzleMiddleware\RequestIntervalMiddleware;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Middleware;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Downloads the chapters of chosen manga
 */
readonly class MangaChapterDownloader implements MangaChapterDownloaderInterface
{
    /**
     * @var GuzzleClient Http client to download chapters metadata
     */
    private GuzzleClient $chaptersMetadataDownloader;

    private GuzzleClient $mangaPageLoader;

    private const RETRY_LIMIT = 5;

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
        $this->chaptersMetadataDownloader = GuzzleClientFactory::createClient(
            guzzleClientParams: [
                GuzzleClientParameters::BASE_URI->value => $this->chaptersMetadataApiBaseUri,
                GuzzleClientParameters::VERIFY->value   => false,
            ],
            guzzleClientMiddlewares: [
                new RequestIntervalMiddleware(),
            ]
        );

        $this->mangaPageLoader = GuzzleClientFactory::createClient(
            guzzleClientParams: [
                GuzzleClientParameters::VERIFY->value   => false,
                GuzzleClientParameters::BASE_URI->value => $this->imgServerBaseUri,
                GuzzleClientParameters::TIMEOUT->value => 20,
                GuzzleRequestParameters::CONNECTION_TIMEOUT->value => 30
            ],
            guzzleClientMiddlewares: [
                new RequestIntervalMiddleware(),
                Middleware::retry(
                    decider: function ($retries, RequestInterface $request, ?ResponseInterface $response, ?TransferException $exception) {
                        $retry = true;
                        if ($response && $retries < self::RETRY_LIMIT) {
                            $retry = in_array($response->getStatusCode(), [429, 499, 500])
                                && $response->getBody()->getSize() !== (int) $response->getHeaderLine('content-length')
                                && !empty($response->getBody()->getSize());
                        }

                        if ($exception instanceof TransferException) {
                            $retry = true; // Retry on network errors
                        }

                        if ($retry) {
                            $this->logger->info('Retrying request: '.$request->getUri());

                            return true;
                        }

                        return false;
                    },
                    delay: function (int $retries) { $this->logger->debug('retry delay 5ms'); return 5; }
                )
            ]
        );
    }

    /**
     * {@inheritDoc}
     * @throws GuzzleException if request to api failed
     * @throws \JsonException if response is not a valid json
     * @throws \Throwable if pages downloading failed
     */
    public function downloadAllMangaChapters(string $slugUrl, string $mangaTitle): MangaChaptersMetadataListDto
    {
        $this->logger->info("Downloading chapters list...");
        $chaptersList = $this->downloadChaptersList($slugUrl);
        $this->logger->info("Chapters list downloaded successfully");

        $this->logger->info("Downloading chapters metadata...");
        $chaptersMeta = $this->downloadChaptersMetadata($slugUrl, $chaptersList);
        $this->logger->info("Chapters metadata downloaded successfully");

        foreach ($chaptersMeta->mangaChaptersListIterator() as $chapterMeta) {
            $downloadPath             = $this->createChapterDirectory($mangaTitle, $chapterMeta);
            $chapterMeta->fileDirPath = $downloadPath;
            if (!$this->checkChapterDirectoryIsEmpty($downloadPath)) {
                $this->logger->info("Chapter $chapterMeta->number already downloaded to: $chapterMeta->fileDirPath");
                continue;
            }
            $this->logger->info("Downloading chapter $chapterMeta->number pages...");
            $this->downloadChapterPages($chapterMeta);
            $this->logger->info("Chapter $chapterMeta->number pages downloaded successfully to: $chapterMeta->fileDirPath");
        }

        return $chaptersMeta;
    }

    /**
     * {@inheritDoc}
     */
    public function downloadSingleMangaChapter(string $slugUrl, string $mangaTitle, int $chapterNumber): array
    {
        return [];
    }

    /**
     * Downloads a manga's chapter list
     *
     * @param string $slugUrl A manga slug url
     * @return MangaChaptersListDto Manga's chapters list
     * @throws GuzzleException if request to api failed
     * @throws \JsonException if response is not a valid json
     */
    private function downloadChaptersList(string $slugUrl): MangaChaptersListDto
    {
        $requestUri = "$slugUrl/chapters";
        try {
            $chapterList = $this->chaptersMetadataDownloader->get($requestUri);
        } catch (GuzzleException $guzzleException) {
            $this->logger->critical("Chapters list download failed!", ['requestUrl' => $this->chaptersMetadataApiBaseUri.$requestUri]);
            throw $guzzleException;
        }
        try {
            $chaptersMetaJson = json_decode(json: $chapterList->getBody()->getContents(), associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $jsonException) {
            $this->logger->critical(
                "Chapters list response is not a valid json!", ['responseBody' => $chapterList->getBody()->getContents()]
            );
            throw $jsonException;
        }
        $mangaChaptersListMetadataDto = new MangaChaptersListDto();

        foreach ($chaptersMetaJson['data'] as $chapterMeta) {
            $mangaChaptersListMetadataDto->addMangaChaptersListItem(
                new MangaChaptersListItemDto(
                    number: $chapterMeta['number'],
                    volume: $chapterMeta['volume']
                )
            );
        }

        return $mangaChaptersListMetadataDto;
    }

    /**
     * Downloads the metadata of the chapters
     *
     * @param string $slugUrl A manga slug url
     * @param MangaChaptersListDto $chaptersList A manga's chapters list
     * @return MangaChaptersMetadataListDto List of every chapter metadata
     */
    private function downloadChaptersMetadata(string $slugUrl, MangaChaptersListDto $chaptersList): MangaChaptersMetadataListDto
    {
        $chapterMetadataRequests = [];
        foreach ($chaptersList->mangaChaptersListIterator() as $chapter) {
            /** @var MangaChaptersListItemDto $chapter */
            $chapterMetadataRequests[] = $this->chaptersMetadataDownloader->requestAsync(
                method: 'GET', uri: "$slugUrl/chapter?number={$chapter->number}&volume={$chapter->volume}"
            );
        }

        $chaptersMetadata = new MangaChaptersMetadataListDto();
        $rejectedRequests = [];

        $chaptersMetaRequestsPool = new EachPromise(
            $chapterMetadataRequests,
            [
                'concurrency' => $this->concurrency,
                'fulfilled'   => function (Response $response) use (&$chaptersMetadata) {
                    $chapterMetaJson          = json_decode($response->getBody()->getContents(), true);
                    $chaptersMetadata->addMangaChaptersListItem(
                        new MangaChaptersMetadataListItemDto(
                            name: $chapterMetaJson['data']['name'],
                            volume: $chapterMetaJson['data']['volume'],
                            number: $chapterMetaJson['data']['number'],
                            pages: array_map(
                                fn (array $pageMeta): MangaChaptersMetadataListItemPageFileDto => new MangaChaptersMetadataListItemPageFileDto($pageMeta['image'], $pageMeta['url']),
                                $chapterMetaJson['data']['pages']
                            ),
                        )
                    );
                },
                'rejected'    => function (TransferException $reason) use (&$rejectedRequests) {
                    $rejectedRequests[] = [
                        'request' => $reason->getRequest() ?? null,
                        'reason'  => $reason->getMessage()
                    ];
                },
            ]
        );

        $promises = $chaptersMetaRequestsPool->promise();
        $promises->wait();

        //todo: add rejected requests retry
        if (!empty($rejectedRequests)) {
            $this->logger->debug("There are rejected image requests");
        }

        unset($rejectedRequests);

        return $chaptersMetadata;
    }

    /**
     * Creates save directory for a manga chapter
     *
     * @param string $mangaTitle Title of the manga to download, used to create parent directory for chapters
     * @param MangaChaptersMetadataListItemDto $chapterMeta Chapter metadata
     * @return string Path to a manga directory, where chapter pages should be saved
     */
    private function createChapterDirectory(string $mangaTitle, MangaChaptersMetadataListItemDto $chapterMeta): string
    {
        $downloadPath = $this->mangaDirectorySavePath.escapeshellarg($mangaTitle).'/'.$chapterMeta->volume.'_'.$chapterMeta->number;

        if (!is_dir($downloadPath)) {
            mkdir($downloadPath, 0777, true);
        }

        return $downloadPath;
    }

    /**
     * Downloads pages (pictures) to directory specified in MangaChaptersMetadataListItemPageFileDto->fileDirPath field
     *
     * @param MangaChaptersMetadataListItemDto $chapterMeta Chapter metadata
     * @return void
     * @throws \Throwable if pages downloading failed
     */
    private function downloadChapterPages(MangaChaptersMetadataListItemDto $chapterMeta): void
    {
        $chapterPagesRequests = [];
        $chapterPagesRetryRequests = [];

        $chapterMeta->pages = array_slice($chapterMeta->pages,0,10);

        $requestsIterator = function ($chapterMeta) use (&$chapterPagesRequests) {
            foreach ($chapterMeta->pages as $pageMeta) {
                /** @var MangaChaptersMetadataListItemPageFileDto $pageMeta */
                $request = new Request(
                    method: 'GET',
                    uri:$this->imgServerBaseUri.$pageMeta->imageUrl,
                    headers: [
                        'Host'            => str_replace(['https://', 'http://'], '', $this->imgServerBaseUri),
                        'User-Agent'      => 'Mozilla/5.0 (X11; Linux x86_64; rv:134.0) Gecko/20100101 Firefox/134.0',
                        'Referer'         => 'https://mangalib.me/',
                        'Accept'          => 'image/avif,image/webp,image/png,image/svg+xml,image/*;q=0.8,*/*;q=0.5',
                        'Accept-Encoding' => 'gzip, deflate, br, zstd'
                    ]
                );
                $chapterPagesRequests[] = [
                    'request' => $request,
                    'sink' => $chapterMeta->fileDirPath.'/'.$pageMeta->imageName
                ];

                yield $request;
            }
        };

        $chaptersMetaRequestsPool = new Pool(
            $this->mangaPageLoader,
            $requestsIterator($chapterMeta),
            [
                'concurrency' => $this->concurrency,
                'fulfilled'   => function (Response $response, $index) use (&$chapterPagesRequests, &$chapterPagesRetryRequests) {
                    $contentLength = $response->getHeaderLine('content-length');

                    if ($response->getBody()->getSize() !== (int) $contentLength && !empty($contentLength)) {
                        $chapterPagesRetryRequests[$index] = $chapterPagesRequests[$index];
                    } else {
                        file_put_contents($chapterPagesRequests[$index]['sink'], $response->getBody());
                    }
                },
                'rejected'    => function ($reason, $index) {
                    $this->logger->error(
                        'rejected request',
                        [
                            'requestUri'     => $reason->getRequest()?->getUri(),
                            'reason'         => $reason->getMessage(),
                            'responseBody'   => $reason->getResponse()?->getBody()->getContents(),
                            'responseStatus' => $reason->getResponse()?->getStatusCode()
                        ]
                    );
                },
            ]
        );

        $promises = $chaptersMetaRequestsPool->promise();
        $promises->wait();

        unset($chapterPagesRetryRequests);
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