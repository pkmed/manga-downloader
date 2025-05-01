<?php

namespace App\Dto\Manga;

class MangaChaptersListItemDto
{
    public function __construct(
        public int $number,
        public int $volume
    )
    {
    }
}