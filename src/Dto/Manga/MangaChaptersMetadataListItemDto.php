<?php

namespace App\Dto\Manga;

class MangaChaptersMetadataListItemDto
{
    public string $fileDirPath;

    public function __construct(
        public string $name,
        public int $volume,
        public float $number,
        public array $pages
    )
    {
    }
}