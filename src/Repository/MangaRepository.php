<?php

namespace App\Repository;

use App\Dto\Manga\MangaMetadataDto;
use App\Entity\Manga;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Manga>
 */
class MangaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Manga::class);
    }

    public function isMangaExist(MangaMetadataDto $mangaMetadataDto): bool
    {
        $result = $this->findOneBy(['mangaSource' => $mangaMetadataDto->mangaSource, 'slugUrl' => $mangaMetadataDto->slugUrl]);

        return is_object($result);
    }
}
