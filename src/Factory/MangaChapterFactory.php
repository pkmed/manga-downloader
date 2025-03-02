<?php

namespace App\Factory;

use App\Entity\Manga;
use App\Entity\MangaChapter;

class MangaChapterFactory
{
    /**
     * @param Manga $manga Manga metadata
     * @param array $chaptersMeta Manga chapters metadata
     * @return MangaChapter[]
     */
    public static function createMangaChapters(Manga $manga, array $chaptersMeta): array
    {
        $chapterEntities = [];
        foreach ($chaptersMeta as $chapterMeta) {
            $chapter = new MangaChapter(
                name: $chapterMeta['name'],
                volume: $chapterMeta['volume'],
                number: (float) $chapterMeta['number'],
                pageCount: count($chapterMeta['pages']),
                manga: $manga
            );

            $manga->addChapter($chapter);

            $chapterEntities[] = $chapter;
        }

        return $chapterEntities;
    }
}