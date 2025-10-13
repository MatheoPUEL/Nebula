<?php

namespace App\Command;

use App\Entity\Apod;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:download-apod-images',
    description: 'Télécharge les images et HD images des APOD déjà en base.',
)]
class DownloadApodImagesCommand extends Command
{
    private string $basePath = 'assets/images/apod';
    private int $mediaTypeImage = 1;
    private int $mediaTypeVideo = 2;

    public function __construct(
        private EntityManagerInterface $em,
        private HttpClientInterface $client
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filesystem = new Filesystem();

        // Récupérer tous les APOD
        $apods = $this->em->getRepository(Apod::class)->findAll();

        foreach ($apods as $apod) {
            $date = $apod->getDateApod();
            $datePath = $date->format('Y-m-d');

            // ==== image normale ====
            if ($apod->getUrl() && $apod->getMediaType() === $this->mediaTypeImage) {
                $imageUrl = $apod->getUrl();
                $normalPath = "{$this->basePath}/{$datePath}.jpg";

                try {
                    file_put_contents($normalPath, $this->client->request('GET', $imageUrl)->getContent());
                    $apod->setPath("/{$datePath}.jpg");
                    $output->writeln("Downloaded normal image for {$datePath}");
                } catch (\Exception $e) {
                    $output->writeln("Error downloading normal image for {$datePath}: {$imageUrl}");
                    $apod->setPath('no_image');
                }
            }

            // ==== image HD ====
            if ($apod->getHdurl()) {
                $hdUrl = $apod->getHdurl();
                $hdPath = "{$this->basePath}/{$datePath}-HD.jpg";

                try {
                    file_put_contents($hdPath, $this->client->request('GET', $hdUrl)->getContent());
                    $apod->setHdpath("/{$datePath}-HD.jpg");
                    $output->writeln("Downloaded HD image for {$datePath}");
                } catch (\Exception $e) {
                    $output->writeln("Error downloading HD image for {$datePath}: {$hdUrl}");
                    $apod->setHdpath(null);
                }
            }

            // Si c'est une vidéo, on ne fait rien, le champ url reste tel quel
            $this->em->persist($apod);
        }

        $this->em->flush();
        $output->writeln("All images processed.");

        return Command::SUCCESS;
    }
}
