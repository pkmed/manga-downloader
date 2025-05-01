<?php

namespace App\Dto\Manga;

class MangaMetadataDto
{
    public function __construct(
        public string $title,
        public string $summary,
        public string $releaseYear,
        public string $chaptersCount
    )
    {
    }
}