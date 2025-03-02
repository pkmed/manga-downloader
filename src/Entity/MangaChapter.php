<?php

namespace App\Entity;

use App\Repository\MangaChapterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MangaChapterRepository::class)]
class MangaChapter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\Column(length: 255)]
        private string $name,
        #[ORM\Column(type: Types::SMALLINT)]
        private int    $volume,
        #[ORM\Column(type: Types::FLOAT, precision: 2, scale: 5)]
        private float  $number,
        #[ORM\Column(type: Types::SMALLINT)]
        private int    $pageCount,
        #[ORM\ManyToOne(inversedBy: 'chapters')]
        #[ORM\JoinColumn(nullable: false)]
        private Manga  $manga,
    )
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getVolume(): int
    {
        return $this->volume;
    }

    public function setVolume(int $volume): static
    {
        $this->volume = $volume;

        return $this;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setNumber(int $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getPageCount(): int
    {
        return $this->pageCount;
    }

    public function setPageCount(int $pageCount): static
    {
        $this->pageCount = $pageCount;

        return $this;
    }

    public function getManga(): ?Manga
    {
        return $this->manga;
    }

    public function setManga(?Manga $manga): static
    {
        $this->manga = $manga;

        return $this;
    }
}
