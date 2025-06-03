<?php
namespace App\Entity;

use App\Repository\RendezVousRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RendezVousRepository::class)]
class RendezVous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateRDV = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $statut = null;

    #[ORM\OneToOne(inversedBy: 'rendezVous', cascade: ['persist', 'remove'])]
    private ?Utilisateur $utilisateur = null;

    // #[ORM\ManyToOne(inversedBy: 'rendezVousList')]
    // private ?Services $service = null;

    #[ORM\OneToOne(mappedBy: 'rendezVous', cascade: ['persist', 'remove'])]
    private ?AvisClient $avisClient = null;

    /**
     * @var Collection<int, Services>
     */
    #[ORM\ManyToMany(targetEntity: Services::class)]
    private Collection $services;

    public function __construct()
    {
        $this->services = new ArrayCollection();
    }

    // SUPPRIMER CES LIGNES :
    // #[ORM\Column(length: 100, nullable: true)]
    // private ?string $typeService = null;

    // public function getTypeService(): ?string
    // {
    //     return $this->typeService;
    // }

    // public function setTypeService(?string $typeService): static
    // {
    //     $this->typeService = $typeService;

    //     return $this;
    // }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateRDV(): ?\DateTimeInterface
    {
        return $this->dateRDV;
    }

    public function setDateRDV(?\DateTimeInterface $dateRDV): static
    {
        $this->dateRDV = $dateRDV;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    // public function getService(): ?Services
    // {
    //     return $this->service;
    // }

    // public function setService(?Services $service): static
    // {
    //     $this->service = $service;

    //     return $this;
    // }

    public function getAvisClient(): ?AvisClient
    {
        return $this->avisClient;
    }

    public function setAvisClient(?AvisClient $avisClient): static
    {
        // unset the owning side of the relation if necessary
        if ($avisClient === null && $this->avisClient !== null) {
            $this->avisClient->setRendezVous(null);
        }

        // set the owning side of the relation if necessary
        if ($avisClient !== null && $avisClient->getRendezVous() !== $this) {
            $avisClient->setRendezVous($this);
        }

        $this->avisClient = $avisClient;

        return $this;
    }

    /**
     * @return Collection<int, Services>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Services $service): static
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
        }

        return $this;
    }

    public function removeService(Services $service): static
    {
        $this->services->removeElement($service);

        return $this;
    }
}