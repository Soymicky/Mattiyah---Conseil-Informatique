<?php

namespace App\Entity;

use App\Repository\ServicesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServicesRepository::class)]
class Services
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomService = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descriptionService = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeOffre = null;

    #[ORM\ManyToOne(inversedBy: 'servicesList')]
    #[ORM\JoinColumn(nullable: false)]
    private ?NiveauService $niveauService = null;

    #[ORM\ManyToMany(targetEntity: RendezVous::class, mappedBy: 'services')]
    private Collection $rendezVousList;

    public function __construct()
    {
        $this->rendezVousList = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomService(): ?string
    {
        return $this->nomService;
    }

    public function setNomService(?string $nomService): static
    {
        $this->nomService = $nomService;

        return $this;
    }

    public function getDescriptionService(): ?string
    {
        return $this->descriptionService;
    }

    public function setDescriptionService(?string $descriptionService): static
    {
        $this->descriptionService = $descriptionService;

        return $this;
    }

    public function getTypeOffre(): ?string
    {
        return $this->typeOffre;
    }

    public function setTypeOffre(?string $typeOffre): static
    {
        $this->typeOffre = $typeOffre;

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

    /**
     * @return Collection<int, RendezVous>
     */
    public function getRendezVousList(): Collection
    {
        return $this->rendezVousList;
    }

    public function addRendezVousList(RendezVous $rendezVousList): static
    {
        $this->rendezVousList->add($rendezVousList);
        $rendezVousList->addService($this);

        return $this;
    }

    public function removeRendezVousList(RendezVous $rendezVousList): static
    {
        $this->rendezVousList->removeElement($rendezVousList);
        $rendezVousList->removeService($this);

        return $this;
    }
}