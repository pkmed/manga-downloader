<?php

namespace App\Command;

use App\Model\DownloadMangaRequestModel;
use App\Service\MangaDownloaderService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'manga-downloader:download-manga',
    description: 'Add a short description for your command',
)]
class MangaDownloaderDownloadMangaCommand extends Command
{
    public function __construct(
        private readonly MangaDownloaderService $mangaDownloaderService,
        private readonly ValidatorInterface     $validator
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('slug', InputArgument::REQUIRED, 'Slug url of a manga on mangalib');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io   = new SymfonyStyle($input, $output);
        $slug = $input->getArgument('slug');

        $mangaRequestModel = new DownloadMangaRequestModel($slug);

        $errors = $this->validator->validate($mangaRequestModel);
        if ($errors->count() > 0) {
            foreach ($errors as $error) {
                $io->error($error);
            }

            return Command::INVALID;
        }

        $io->info("Started downloading the manga...");
        try {
            $responseModel = $this->mangaDownloaderService->downloadMangaBySlugUrl($mangaRequestModel);
        } catch (\RuntimeException $re) {
            $io->error($re->getMessage());

            return Command::FAILURE;
        }
        $io->info("Manga and pages were downloaded and saved successfully!");

        $io->success('Metadata json:');
        foreach ($responseModel as $key => $property) {
            $io->writeln("$key: $property");
        }

        return Command::SUCCESS;
    }
}
