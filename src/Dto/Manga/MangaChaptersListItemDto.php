<?php

namespace App\Dto\Manga;

class MangaChaptersListItemDto
{
    public function __construct(
        public float $number,
        public int $volume
    )
    {
    }
}