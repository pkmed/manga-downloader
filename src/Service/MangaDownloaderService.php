<?php

namespace App\Service;

use App\Downloader\Interface\MangaChapterDownloaderInterface;
use App\Downloader\Interface\MangaMetadataDownloaderInterface;
use App\Entity\Manga;
use App\Factory\MangaChapterFactory;
use App\Factory\MangaFactory;
use App\Mapper\MangaResponseModelMapper;
use App\Model\DownloadMangaRequestModel;
use App\Model\DownloadMangaResponseModel;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

/**
 * Downloads a manga
 */
readonly class MangaDownloaderService
{
    public function __construct(
        private MangaMetadataDownloaderInterface $metadataDownloader,
        private MangaChapterDownloaderInterface  $chapterDownloader,
        private EntityManagerInterface           $entityManager,
        private LoggerInterface                  $logger
    )
    {
    }


    /**
     * Downloads manga metadata and pages and saves them.
     *
     * @param DownloadMangaRequestModel $mangaRequestModel
     * @return DownloadMangaResponseModel
     * @throws GuzzleException if request to api failed
     * @throws \JsonException if response is not a valid json
     * @throws \Throwable if pages downloading failed
     */
    public function downloadMangaBySlugUrl(DownloadMangaRequestModel $mangaRequestModel): DownloadMangaResponseModel
    {
        $this->logger->info("Started downloading the manga...");
        $mangaMeta    = $this->metadataDownloader->downloadMangaMetadata($mangaRequestModel->slugUrl);
        if ($this->entityManager->getRepository(Manga::class)->isMangaExist($mangaMeta)) {
            throw new \RuntimeException("Manga '{$mangaMeta->title}' was already downloaded!");
        }
        $chaptersMeta = $this->chapterDownloader->downloadAllMangaChapters($mangaMeta->slugUrl, $mangaMeta->title);
        $manga        = MangaFactory::createManga($mangaMeta);
        foreach ($chaptersMeta->mangaChaptersListIterator() as $chapterMeta) {
            $manga->addChapter(MangaChapterFactory::createMangaChapter($manga, $chapterMeta));
        }
        $this->entityManager->persist($manga);
        $this->entityManager->flush();
        $this->logger->info("Manga and pages were downloaded and saved successfully!");

        return MangaResponseModelMapper::mapMangaResponseModel($manga);
    }
}