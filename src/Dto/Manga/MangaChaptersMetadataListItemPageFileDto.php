<?php

namespace App\Dto\Manga;

class MangaChaptersMetadataListItemPageFileDto
{
    public function __construct(
        public int $pageNumber,
        public string $imageName,
        public string $imageUrl
    )
    {
    }
}