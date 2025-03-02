<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

readonly class DownloadMangaRequestModel
{
    public function __construct(
        #[Assert\Regex('~^\d+--[\w-]+$~')]
        public string $slugUrl
    )
    {
    }
}