<?php

namespace App\Factory;

use App\Dto\Manga\MangaChaptersMetadataListItemDto;
use App\Entity\Manga;
use App\Entity\MangaChapter;

class MangaChapterFactory
{
    /**
     * Creates single manga chapter
     *
     * @param Manga $manga Manga metadata
     * @param MangaChaptersMetadataListItemDto $chapterMeta Metadata of a manga chapter
     * @return MangaChapter
     */
    public static function createMangaChapter(Manga $manga, MangaChaptersMetadataListItemDto $chapterMeta): MangaChapter
    {
        return new MangaChapter(
            name: $chapterMeta->name,
            volume: $chapterMeta->volume,
            number: $chapterMeta->number,
            pageCount: count($chapterMeta->pages),
            manga: $manga,
            chapterDirectoryPath: $chapterMeta->fileDirPath
        );
    }
}