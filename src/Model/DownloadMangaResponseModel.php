<?php

namespace App\Model;

readonly class DownloadMangaResponseModel
{
    public function __construct(
        public int    $id,
        public string $title,
        public string $summary,
        public int    $releaseYear,
        public int    $chaptersCount,
    )
    {
    }
}