<?php

namespace App\Downloader\Interface;

use App\Dto\Manga\MangaChaptersMetadataListDto;

interface MangaChapterDownloaderInterface
{
    /**
     * Downloads the chapters of a manga by manga's slug url
     *
     * @param string $slugUrl A manga slug url
     * @param string $mangaTitle Title of the manga to download, used to create parent directory for the chapters
     * @return MangaChaptersMetadataListDto List of downloaded chapters' metadata
     */
    public function downloadAllMangaChapters(string $slugUrl, string $mangaTitle): MangaChaptersMetadataListDto;

    /**
     * Downloads a single chapter of a manga by manga's slug url and chapter number
     *
     * @param string $slugUrl A manga slug url
     * @param string $mangaTitle Title of the manga to download, used to create parent directory for a chapter
     * @param int    $chapterNumber Number of a chapter to download
     * @return array Single chapter
     */
    public function downloadSingleMangaChapter(string $slugUrl, string $mangaTitle, int $chapterNumber): array;
}