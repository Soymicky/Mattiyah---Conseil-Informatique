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

    #[ORM\ManyToOne(inversedBy: 'rendezVousList')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    // CHANGEMENT ICI : OneToOne devient OneToMany pour les avis
    /**
     * @var Collection<int, AvisClient>
     */
    #[ORM\OneToMany(mappedBy: 'rendezVous', targetEntity: AvisClient::class, cascade: ['persist', 'remove'])]
    private Collection $avisClients; // Renommé de avisClient à avisClients pour indiquer une collection

    /**
     * @var Collection<int, Services>
     */
    #[ORM\ManyToMany(targetEntity: Services::class, inversedBy: 'rendezVousList')]
    private Collection $services;

    /**
     * @var Collection<int, RendezVousService>
     */
    #[ORM\OneToMany(targetEntity: RendezVousService::class, mappedBy: 'rendezVous')]
    private Collection $rendezVousServices;

    public function __construct()
    {
        $this->services = new ArrayCollection();
        $this->rendezVousServices = new ArrayCollection();
        $this->avisClients = new ArrayCollection(); // NOUVELLE INITIALISATION DE LA COLLECTION
    }

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

    // CHANGEMENT ICI : getAvisClient devient getAvisClients et retourne une Collection
    /**
     * @return Collection<int, AvisClient>
     */
    public function getAvisClients(): Collection
    {
        return $this->avisClients;
    }

    // AJOUT DE LA MÉTHODE addAvisClient
    public function addAvisClient(AvisClient $avisClient): static
    {
        if (!$this->avisClients->contains($avisClient)) {
            $this->avisClients->add($avisClient);
            $avisClient->setRendezVous($this);
        }
        return $this;
    }

    // AJOUT DE LA MÉTHODE removeAvisClient
    public function removeAvisClient(AvisClient $avisClient): static
    {
        if ($this->avisClients->removeElement($avisClient)) {
            // set the owning side to null (unless already changed)
            if ($avisClient->getRendezVous() === $this) {
                $avisClient->setRendezVous(null);
            }
        }
        return $this;
    }

    // Suppression de l'ancien setAvisClient (qui gérait une relation OneToOne)
    // public function setAvisClient(?AvisClient $avisClient): static
    // {
    //     if ($avisClient !== null && $avisClient->getRendezVous() !== $this) {
    //         $avisClient->setRendezVous($this);
    //     }
    //     $this->avisClient = $avisClient;
    //     return $this;
    // }

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

    /**
     * @return Collection<int, RendezVousService>
     */
    public function getRendezVousServices(): Collection
    {
        return $this->rendezVousServices;
    }

    public function addRendezVousService(RendezVousService $rendezVousService): static
    {
        if (!$this->rendezVousServices->contains($rendezVousService)) {
            $this->rendezVousServices->add($rendezVousService);
            $rendezVousService->setRendezVous($this);
        }
        return $this;
    }

    public function removeRendezVousService(RendezVousService $rendezVousService): static
    {
        if ($this->rendezVousServices->removeElement($rendezVousService)) {
            if ($rendezVousService->getRendezVous() === $this) {
                $rendezVousService->setRendezVous(null);
            }
        }
        return $this;
    }
}