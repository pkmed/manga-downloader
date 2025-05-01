<?php

namespace App\Entity;

use App\Repository\MangaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MangaRepository::class)]
class Manga
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, MangaChapter>
     */
    #[ORM\OneToMany(targetEntity: MangaChapter::class, mappedBy: 'manga', cascade: ['persist'], orphanRemoval: true)]
    private Collection $chapters;

    public function __construct(
        #[ORM\Column(length: 255)]
        private string  $title,
        #[ORM\Column(type: Types::SMALLINT)]
        private int     $releaseYear,
        #[ORM\Column(type: Types::SMALLINT)]
        private int     $chaptersCount,
        #[ORM\Column(type: Types::TEXT, nullable: true)]
        private ?string $summary = null
        //todo: add 'mangaDirectoryPath' field
    )
    {
        $this->chapters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getReleaseYear(): int
    {
        return $this->releaseYear;
    }

    public function setReleaseYear(int $releaseYear): static
    {
        $this->releaseYear = $releaseYear;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): static
    {
        $this->summary = $summary;

        return $this;
    }

    public function getChaptersCount(): ?int
    {
        return $this->chaptersCount;
    }

    public function setChaptersCount(int $chaptersCount): static
    {
        $this->chaptersCount = $chaptersCount;

        return $this;
    }

    /**
     * @return Collection<int, MangaChapter>
     */
    public function getChapters(): Collection
    {
        return $this->chapters;
    }

    public function addChapter(MangaChapter $chapter): static
    {
        if (!$this->chapters->contains($chapter)) {
            $this->chapters->add($chapter);
            $chapter->setManga($this);
        }

        return $this;
    }

    public function removeChapter(MangaChapter $chapter): static
    {
        if ($this->chapters->removeElement($chapter)) {
            // set the owning side to null (unless already changed)
            if ($chapter->getManga() === $this) {
                $chapter->setManga(null);
            }
        }

        return $this;
    }
}
