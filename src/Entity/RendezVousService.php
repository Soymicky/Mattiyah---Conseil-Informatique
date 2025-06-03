<?php

namespace App\Entity;

use App\Repository\RendezVousServiceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RendezVousServiceRepository::class)]
class RendezVousService
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'rendezVousServices')]
    private ?RendezVous $rendezVous = null;

    #[ORM\ManyToOne(inversedBy: 'rendezVousServices')]
    private ?Services $services = null;

    #[ORM\ManyToOne(inversedBy: 'rendezVousServices')]
    private ?NiveauService $niveauService = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRendezVous(): ?RendezVous
    {
        return $this->rendezVous;
    }

    public function setRendezVous(?RendezVous $rendezVous): static
    {
        $this->rendezVous = $rendezVous;

        return $this;
    }

    public function getServices(): ?Services
    {
        return $this->services;
    }

    public function setServices(?Services $services): static
    {
        $this->services = $services;

        return $this;
    }

    public function getNiveauService(): ?NiveauService
    {
        return $this->niveauService;
    }

    public function setNiveauService(?NiveauService $niveauService): static
    {
        $this->niveauService = $niveauService;

        return $this;
    }
}
