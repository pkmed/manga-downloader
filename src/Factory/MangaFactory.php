<?php

namespace App\Factory;


use App\Dto\Manga\MangaMetadataDto;
use App\Entity\Manga;

class MangaFactory
{
    /**
     * @param MangaMetadataDto $metadata Manga metadata
     * @return Manga
     */
    public static function createManga(MangaMetadataDto $metadata): Manga
    {
        return new Manga(
            title: $metadata->title,
            releaseYear: $metadata->releaseYear,
            chaptersCount: $metadata->chaptersCount,
            summary: $metadata->summary
        );
    }
}