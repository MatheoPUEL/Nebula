<?php

namespace App\Entity;

use App\Repository\ApodRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApodRepository::class)]
class Apod
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date_apod = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $explanation = null;

    #[ORM\Column(length: 4096, nullable: true)]
    private ?string $path = null;

    #[ORM\Column]
    private ?int $media_type = null;

    #[ORM\Column(length: 4096, nullable: true)]
    private ?string $hdpath = null;

    #[ORM\Column(length: 4096, nullable: true)]
    private ?string $copyright = null;

    #[ORM\Column(nullable: true)]
    private ?string $url = null;

    #[ORM\Column(nullable: true)]
    private ?string $hdurl = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateApod(): ?\DateTimeImmutable
    {
        return $this->date_apod;
    }

    public function setDateApod(\DateTimeImmutable $date_apod): static
    {
        $this->date_apod = $date_apod;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getExplanation(): ?string
    {
        return $this->explanation;
    }

    public function setExplanation(?string $explanation): static
    {
        $this->explanation = $explanation;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getMediaType(): ?int
    {
        return $this->media_type;
    }

    public function setMediaType(int $media_type): static
    {
        $this->media_type = $media_type;

        return $this;
    }

    public function getHdpath(): ?string
    {
        return $this->hdpath;
    }

    public function setHdpath(?string $hdpath): static
    {
        $this->hdpath = $hdpath;

        return $this;
    }

    public function getCopyright(): ?string
    {
        return $this->copyright;
    }

    public function setCopyright(?string $copyright): static
    {
        $this->copyright = $copyright;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getHdurl(): ?string
    {
        return $this->hdurl;
    }

    public function setHdurl(?string $hdurl): static
    {
        $this->hdurl = $hdurl;

        return $this;
    }
}
