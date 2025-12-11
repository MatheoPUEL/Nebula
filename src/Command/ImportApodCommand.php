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
    description: 'Importe les APOD manquants et télécharge les images avec la bonne extension.',
)]
class ImportApodCommand extends Command
{
    private string $apiKey;
    private string $basePath = 'assets/images/apod';
    private string $firstDate = '1995-06-16';
    private int $mediaTypeImage = 1;
    private int $mediaTypeVideo = 2;
    private int $chunkYears = 1;

    public function __construct(
        private EntityManagerInterface $em,
        private HttpClientInterface $client
    ) {
        parent::__construct();
        $this->apiKey = $_ENV['NASA_API_KEY'] ?? 'DEMO_KEY';
    }

    /**
     * Déduire l’extension en fonction du Content-Type
     */
    private function getExtensionFromMime(?string $mime): string
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png'               => 'png',
            'image/gif'               => 'gif',
            default                   => 'jpg',
        };
    }

    /**
     * Télécharge une image et retourne le chemin si OK, sinon null
     */
    private function downloadImage(string $url, string $fileBase): ?string
    {
        try {
            $response = $this->client->request('GET', $url);

            // Détecter le content-type
            $mime = $response->getHeaders()['content-type'][0] ?? null;
            $ext = $this->getExtensionFromMime($mime);

            // Construire chemin complet
            $filePath = "{$this->basePath}/{$fileBase}.{$ext}";

            file_put_contents($filePath, $response->getContent());

            return "/{$fileBase}.{$ext}";
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filesystem = new Filesystem();

        if (!$filesystem->exists($this->basePath)) {
            $filesystem->mkdir($this->basePath, 0755);
        }

        // 1️⃣ Trouver dernière date en base
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
            $output->writeln("Everything is up to date.");
            return Command::SUCCESS;
        }

        $currentStart = clone $startDate;

        while ($currentStart <= $endDate) {
            $currentEnd = $currentStart->modify("+{$this->chunkYears} month");
            if ($currentEnd > $endDate) {
                $currentEnd = $endDate;
            }

            $url = sprintf(
                'https://api.nasa.gov/planetary/apod?api_key=%s&start_date=%s&end_date=%s',
                $this->apiKey,
                $currentStart->format('Y-m-d'),
                $currentEnd->format('Y-m-d')
            );

            $output->writeln("Current url: " . $url);
            $output->writeln("Fetching {$currentStart->format('Y-m-d')} → {$currentEnd->format('Y-m-d')}");

            try {
                $apods = $this->client->request('GET', $url)->toArray();
            } catch (\Exception $e) {
                $output->writeln("API error: " . $e->getMessage());
                $currentStart = $currentEnd->modify('+1 day');
                continue;
            }

            foreach ($apods as $apodData) {
                $date = new \DateTimeImmutable($apodData['date']);
                $dateString = $date->format('Y-m-d');

                // Déjà en BDD ?
                if ($this->em->getRepository(Apod::class)->findOneBy(['date_apod' => $date])) {
                    $output->writeln("Already exists : $dateString");
                    continue;
                }

                $apod = new Apod();
                $apod->setDateApod($date);
                $apod->setTitle($apodData['title'] ?? null);
                $apod->setExplanation($apodData['explanation'] ?? null);
                $apod->setCopyright($apodData['copyright'] ?? null);

                // Pas d’URL → image manquante
                if (!isset($apodData['url'])) {
                    $apod->setMediaType($this->mediaTypeImage);
                    $apod->setPath('no_image');
                    $apod->setHdpath(null);
                    $output->writeln("⚠ No URL → no_image ($dateString)");
                }

                // IMAGE
                elseif ($apodData['media_type'] === 'image') {
                    $apod->setMediaType($this->mediaTypeImage);
                    $apod->setUrl($apodData['url']);

                    // Image normale
                    $normalPath = $this->downloadImage(
                        $apodData['url'],
                        $dateString
                    );

                    if ($normalPath) {
                        $apod->setPath($normalPath);
                        $output->writeln("Image downloaded : $normalPath");
                    } else {
                        $apod->setPath('no_image');
                        $output->writeln("Failed normal image : {$apodData['url']}");
                    }

                    // Image HD
                    if (isset($apodData['hdurl'])) {
                        $apod->setHdurl($apodData['hdurl']);

                        $hdPath = $this->downloadImage(
                            $apodData['hdurl'],
                            "{$dateString}-HD"
                        );

                        if ($hdPath) {
                            $apod->setHdpath($hdPath);
                            $output->writeln("HD image downloaded : $hdPath");
                        } else {
                            $apod->setHdpath(null);
                            $output->writeln("Failed HD image : {$apodData['hdurl']}");
                        }
                    }
                }

                // VIDEO
                else {
                    $apod->setMediaType($this->mediaTypeVideo);
                    $apod->setPath($apodData['url']);
                }

                $this->em->persist($apod);
                $this->em->flush();

                $output->writeln("Imported : $dateString");
            }

            $currentStart = $currentEnd->modify('+1 day');
        }

        $output->writeln("Import completed!");
        return Command::SUCCESS;
    }
}
