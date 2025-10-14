<?php

namespace App\Entity;

use App\Repository\TimeZoneRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TimeZoneRepository::class)]
class TimeZone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 10)]
    private ?string $offset_utc = null;

    #[ORM\Column]
    private ?int $offset_minutes = null;

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

    public function getOffsetUtc(): ?string
    {
        return $this->offset_utc;
    }

    public function setOffsetUtc(string $offset_utc): static
    {
        $this->offset_utc = $offset_utc;

        return $this;
    }

    public function getOffsetMinutes(): ?int
    {
        return $this->offset_minutes;
    }

    public function setOffsetMinutes(int $offset_minutes): static
    {
        $this->offset_minutes = $offset_minutes;

        return $this;
    }
}
