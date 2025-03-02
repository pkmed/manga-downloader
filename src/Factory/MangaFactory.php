<?php

namespace App\Factory;


use App\Entity\Manga;

class MangaFactory
{
    /**
     * @param array $metadata Manga metadata
     * @return Manga
     */
    public static function createManga(array $metadata): Manga
    {
        return new Manga(
            title: $metadata['title'],
            releaseYear: $metadata['releaseYear'],
            chaptersCount: $metadata['chaptersCount'],
            summary: $metadata['summary']
        );
    }
}