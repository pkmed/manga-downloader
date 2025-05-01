<?php

namespace App\Dto\Manga;

class MangaChaptersListDto
{
    /** @var MangaChaptersListItemDto[] */
    private array $mangaChaptersMetaList;

    public function addMangaChaptersListItem(MangaChaptersListItemDto $mangaChapterMetadata): void
    {
        $this->mangaChaptersMetaList[] = $mangaChapterMetadata;
    }

    public function removeMangaChaptersListItem(MangaChaptersListItemDto $mangaChapterMetadata): void
    {
        unset($this->mangaChaptersMetaList[array_search($mangaChapterMetadata, $this->mangaChaptersMetaList)]);
    }

    public function mangaChaptersListIterator()
    {
        foreach ($this->mangaChaptersMetaList as $mangaChapterMetadataDto) {
            yield $mangaChapterMetadataDto;
        }
    }
}