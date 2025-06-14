<?php

namespace App\Dto\Manga;

use App\Entity\Enum\MangaSource;

class MangaMetadataDto
{
    public function __construct(
        public string $title,
        public string $summary,
        public string $releaseYear,
        public string $chaptersCount,
        public MangaSource $mangaSource,
        public string $slugUrl
    )
    {
    }
}