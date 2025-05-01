<?php

namespace App\Dto\Manga;

class MangaChaptersMetadataListDto
{
    /** @var MangaChaptersMetadataListItemDto[] */
    private array $mangaChaptersMetaList;

    public function addMangaChaptersListItem(MangaChaptersMetadataListItemDto $mangaChapterMetadata): void
    {
        $this->mangaChaptersMetaList[] = $mangaChapterMetadata;
    }

    public function removeMangaChaptersListItem(MangaChaptersMetadataListItemDto $mangaChapterMetadata): void
    {
        unset($this->mangaChaptersMetaList[array_search($mangaChapterMetadata, $this->mangaChaptersMetaList)]);
    }

    public function mangaChaptersListIterator(): \Generator
    {
        foreach ($this->mangaChaptersMetaList as $mangaChapterMetadataDto) {
            yield $mangaChapterMetadataDto;
        }
    }
}