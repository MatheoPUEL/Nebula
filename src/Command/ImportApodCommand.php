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
    name: 'app:import-apod',
    description: 'Importe les APOD manquants depuis la dernière date en BDD jusqu’à aujourd’hui, tranche par tranche pour éviter les timeout.',
)]
class ImportApodCommand extends Command
{
    private string $apiKey;
    private string $basePath = 'assets/images/apod';
    private string $firstDate = '1995-06-16';
    private int $mediaTypeImage = 1;
    private int $mediaTypeVideo = 2;
    private int $chunkYears = 1; // récupère 1 an à la fois

    public function __construct(
        private EntityManagerInterface $em,
        private HttpClientInterface $client
    ) {
        parent::__construct();
        $this->apiKey = $_ENV['NASA_API_KEY'] ?? 'EMjs1bQOgX0edW7IBsKJCJLDE5fbYSi6yuKbfcO7';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filesystem = new Filesystem();

        // Déterminer la date de début
        $lastApod = $this->em->getRepository(Apod::class)
            ->createQueryBuilder('a')
            ->orderBy('a.date_apod', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $startDate = $lastApod
            ? $lastApod->getDateApod()->modify('+1 day')
            : new \DateTimeImmutable($this->firstDate);
        $endDate = new \DateTimeImmutable('today');

        if ($startDate > $endDate) {
            $output->writeln("Everything is up to date (last date : {$lastApod?->getDateApod()->format('Y-m-d')})");
            return Command::SUCCESS;
        }

        $output->writeln("Import from {$startDate->format('Y-m-d')} at {$endDate->format('Y-m-d')}");

        $currentStart = $startDate;

        // Boucle par tranche pour éviter timeout
        while ($currentStart <= $endDate) {
            $currentEnd = $currentStart->modify("+{$this->chunkYears} year");
            if ($currentEnd > $endDate) {
                $currentEnd = $endDate;
            }

            $url = sprintf(
                'https://api.nasa.gov/planetary/apod?api_key=%s&start_date=%s&end_date=%s',
                $this->apiKey,
                $currentStart->format('Y-m-d'),
                $currentEnd->format('Y-m-d')
            );

            $output->writeln("🔗 Récupération de {$currentStart->format('Y-m-d')} à {$currentEnd->format('Y-m-d')}");

            try {
                $apods = $this->client->request('GET', $url)->toArray();
            } catch (\Exception $e) {
                $output->writeln("Error API : " . $e->getMessage());
                $currentStart = $currentEnd->modify('+1 day');
                continue;
            }

            foreach ($apods as $apodData) {
                $date = new \DateTimeImmutable($apodData['date']);

                // Vérifier si déjà en base
                $existing = $this->em->getRepository(Apod::class)
                    ->findOneBy(['date_apod' => $date]);
                if ($existing) {
                    $output->writeln("Already in base : {$apodData['date']}");
                    continue;
                }

                $apod = new Apod();
                $apod->setDateApod($date);
                $apod->setTitle($apodData['title'] ?? null);
                $apod->setExplanation($apodData['explanation'] ?? null);
                $apod->setCopyright($apodData['copyright'] ?? null);

                if (!isset($apodData['url']) || empty($apodData['url'])) {
                    $apod->setMediaType($this->mediaTypeImage);
                    $apod->setPath('no_image');
                    $apod->setHdpath(null);

                    $output->writeln("No URL for {$apodData['date']} save as 'no_image'");
                } elseif ($apodData['media_type'] === 'image') {
                    $apod->setMediaType($this->mediaTypeImage);

                    $datePath = $date->format('Y-m-d');

                    // ==== image normale ====
                    $imageUrl = $apodData['url'];
                    $normalPath = "{$this->basePath}/{$datePath}.jpg";

                    try {
                        file_put_contents($normalPath, $this->client->request('GET', $imageUrl)->getContent());
                        $apod->setPath("/{$datePath}.jpg");
                    } catch (\Exception $e) {
                        $output->writeln("Error download normal : {$imageUrl}");
                        $apod->setPath('no_image');
                    }

                    // ==== image HD ====
                    if (isset($apodData['hdurl'])) {
                        $hdUrl = $apodData['hdurl'];
                        $hdPath = "{$this->basePath}/{$datePath}-HD.jpg";

                        try {
                            file_put_contents($hdPath, $this->client->request('GET', $hdUrl)->getContent());
                            $apod->setHdpath("/{$datePath}-HD.jpg");
                        } catch (\Exception $e) {
                            $output->writeln("Error download in HD : {$hdUrl}");
                            $apod->setHdpath(null);
                        }
                    }
                } else {
                    // ==== vidéo ====
                    $apod->setMediaType($this->mediaTypeVideo);
                    $apod->setPath($apodData['url']);
                }

                $this->em->persist($apod);
                $this->em->flush();
                $output->writeln("Imported : {$apodData['date']}");
            }
            $currentStart = $currentEnd->modify('+1 day');
        }

        $output->writeln("Import done");
        return Command::SUCCESS;
    }
}
