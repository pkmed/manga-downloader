<?php

namespace App\Downloader\Interface;

use App\Dto\Manga\MangaMetadataDto;

interface MangaMetadataDownloaderInterface
{
    /**
     * Downloads manga metadata by slug url
     *
     * @param string $slugUrl Manga's slug from url
     * @return MangaMetadataDto Manga metadata
     */
    public function downloadMangaMetadata(string $slugUrl): MangaMetadataDto;
}