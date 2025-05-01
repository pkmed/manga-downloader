<?php

namespace App\Downloader\Interface;

use App\Dto\Manga\MangaMetadataDto;

interface MangaMetadataDownloaderInterface
{
    public function downloadMangaMetadata(string $slugUrl): MangaMetadataDto;
}