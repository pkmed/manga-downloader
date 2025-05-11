<?php

namespace App\Dto\Manga;

class MangaChaptersMetadataListItemPageFileDto
{
    public function __construct(
        public string $imageName,
        public string $imageUrl
    )
    {
    }
}