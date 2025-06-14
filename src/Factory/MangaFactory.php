<?php

namespace App\Factory;


use App\Dto\Manga\MangaMetadataDto;
use App\Entity\Manga;

class MangaFactory
{
    /**
     * Creates Manga entity
     *
     * @param MangaMetadataDto $mangaMetadata Manga metadata
     * @return Manga
     */
    public static function createManga(MangaMetadataDto $mangaMetadata): Manga
    {
        return new Manga(
            title: $mangaMetadata->title,
            releaseYear: $mangaMetadata->releaseYear,
            chaptersCount: $mangaMetadata->chaptersCount,
            mangaSource: $mangaMetadata->mangaSource,
            slugUrl: $mangaMetadata->slugUrl,
            summary: $mangaMetadata->summary
        );
    }
}