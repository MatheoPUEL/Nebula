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
    description: 'Télécharge les images APOD uniquement si elles ne sont pas déjà en local.',
)]
class DownloadApodImagesCommand extends Command
{
    private string $basePath = 'assets/images/apod';
    private int $mediaTypeImage = 1;

    public function __construct(
        private EntityManagerInterface $em,
        private HttpClientInterface $client
    ) {
        parent::__construct();
    }

    private function getExtensionFromMime(?string $mime): string
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png'               => 'png',
            'image/gif'               => 'gif',
            default                   => 'jpg',
        };
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filesystem = new Filesystem();

        // Crée le dossier si nécessaire
        if (!$filesystem->exists($this->basePath)) {
            $filesystem->mkdir($this->basePath, 0755);
        }

        $apods = $this->em->getRepository(Apod::class)->findAll();

        foreach ($apods as $apod) {
            $datePath = $apod->getDateApod()->format('Y-m-d');

            /*
            |--------------------------------------------------------------------------
            | TÉLÉCHARGEMENT DE L'IMAGE NORMALE
            |--------------------------------------------------------------------------
            */
            if ($apod->getUrl() && $apod->getMediaType() === $this->mediaTypeImage) {

                // Vérifie si une image est déjà enregistrée en base
                if ($apod->getPath()) {
                    $localFile = $this->basePath . $apod->getPath();

                    if ($filesystem->exists($localFile)) {
                        $output->writeln("Image déjà existante : {$localFile}");
                        goto HD_IMAGE; // passe à l'image HD
                    }
                }

                $imageUrl = $apod->getUrl();

                try {
                    $response = $this->client->request('GET', $imageUrl);

                    $mime = $response->getHeaders()['content-type'][0] ?? null;
                    $ext = $this->getExtensionFromMime($mime);

                    $normalFile = "{$this->basePath}/{$datePath}.{$ext}";
                    file_put_contents($normalFile, $response->getContent());

                    $apod->setPath("/{$datePath}.{$ext}");
                    $output->writeln("✔ Image téléchargée : {$normalFile}");
                } catch (\Exception $e) {
                    $output->writeln("❌ Erreur image normale {$datePath} : {$imageUrl}");
                    $apod->setPath('no_image');
                }
            }

            /*
            |--------------------------------------------------------------------------
            | TÉLÉCHARGEMENT DE L’IMAGE HD
            |--------------------------------------------------------------------------
            */
            HD_IMAGE:

            if ($apod->getHdurl()) {

                // Vérifie si elle existe déjà
                if ($apod->getHdpath()) {
                    $hdLocal = $this->basePath . $apod->getHdpath();

                    if ($filesystem->exists($hdLocal)) {
                        $output->writeln("HD déjà existante : {$hdLocal}");
                        $this->em->persist($apod);
                        continue;
                    }
                }

                $hdUrl = $apod->getHdurl();

                try {
                    $response = $this->client->request('GET', $hdUrl);

                    $mime = $response->getHeaders()['content-type'][0] ?? null;
                    $ext = $this->getExtensionFromMime($mime);

                    $hdFile = "{$this->basePath}/{$datePath}-HD.{$ext}";
                    file_put_contents($hdFile, $response->getContent());

                    $apod->setHdpath("/{$datePath}-HD.{$ext}");
                    $output->writeln("✔ HD téléchargée : {$hdFile}");

                } catch (\Exception $e) {
                    $output->writeln("❌ Erreur HD {$datePath} : {$hdUrl}");
                    $apod->setHdpath(null);
                }
            }

            $this->em->persist($apod);
        }

        $this->em->flush();
        $output->writeln("\nTéléchargement terminé");

        return Command::SUCCESS;
    }
}
