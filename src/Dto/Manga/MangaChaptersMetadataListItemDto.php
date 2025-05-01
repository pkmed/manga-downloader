<?php

namespace App\Dto\Manga;

class MangaChaptersMetadataListItemDto
{
    public function __construct(
        public string $name,
        public int $volume,
        public int $number,
        public array $pages
    )
    {
    }
}