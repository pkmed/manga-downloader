<?php

namespace App\Downloader\Interface;

interface MangaChapterDownloaderInterface
{
    public function downloadAllMangaChapters(string $slugUrl, string $mangaTitle): array;

    public function downloadSingleMangaChapter(string $slugUrl, string $mangaTitle, int $chapterNumber): array;
}