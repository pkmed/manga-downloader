<?php

namespace App\Mapper;

use App\Entity\Manga;
use App\Model\DownloadMangaResponseModel;

class MangaResponseModelMapper
{
    /**
     * @param Manga $manga
     * @return DownloadMangaResponseModel
     */
    public static function mapMangaResponseModel(Manga $manga): DownloadMangaResponseModel
    {
        return new DownloadMangaResponseModel(
            id: $manga->getId(),
            title: $manga->getTitle(),
            summary: $manga->getSummary(),
            releaseYear: $manga->getReleaseYear(),
            chaptersCount: $manga->getChaptersCount()
        );
    }
}