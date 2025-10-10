<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Code ISO alpha-2 (ex: FR, US)
    #[ORM\Column(length: 2)]
    private ?string $iso = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $nicename = null;

    // Code ISO alpha-3 (ex: FRA, USA)
    #[ORM\Column(length: 3)]
    private ?string $iso3 = null;

    // Numéro ISO (ex: 250 pour la France)
    #[ORM\Column(nullable: true)]
    private ?int $numcode = null;

    // Indicatif téléphonique (ex: 33 pour la France)
    #[ORM\Column]
    private ?int $phonecode = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'country')]
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIso(): ?string
    {
        return $this->iso;
    }

    public function setIso(string $iso): static
    {
        $this->iso = $iso;
        return $this;
    }

    public function getIso3(): ?string
    {
        return $this->iso3;
    }

    public function setIso3(string $iso3): static
    {
        $this->iso3 = $iso3;
        return $this;
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

    public function getNicename(): ?string
    {
        return $this->nicename;
    }

    public function setNicename(string $nicename): static
    {
        $this->nicename = $nicename;
        return $this;
    }

    public function getNumcode(): ?int
    {
        return $this->numcode;
    }

    public function setNumcode(?int $numcode): static
    {
        $this->numcode = $numcode;
        return $this;
    }

    public function getPhonecode(): ?int
    {
        return $this->phonecode;
    }

    public function setPhonecode(int $phonecode): static
    {
        $this->phonecode = $phonecode;
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setCountry($this);
        }
        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            if ($user->getCountry() === $this) {
                $user->setCountry(null);
            }
        }
        return $this;
    }
}
