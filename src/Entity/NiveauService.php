<?php

namespace App\Entity;

use App\Repository\NiveauServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NiveauServiceRepository::class)]
class NiveauService
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomNiveau= null; // <-- PROPRIÉTÉ RENOMMÉE CORRECTEMENT

    /**
     * @var Collection<int, AvisClient>
     */
    #[ORM\OneToMany(mappedBy: 'niveauService', targetEntity: AvisClient::class)]
    private Collection $avisClients;

    /**
     * @var Collection<int, RendezVousService>
     */
    #[ORM\OneToMany(targetEntity: RendezVousService::class, mappedBy: 'niveauService')]
    private Collection $rendezVousServices;

    public function __construct()
    {
        $this->avisClients = new ArrayCollection();
        $this->rendezVousServices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    // --- MISE À JOUR ICI ---
    public function getNomNiveau(): ?string // <-- GETTER RENOMMÉ
    {
        return $this->nomNiveau; // <-- FAIT RÉFÉRENCE À LA NOUVELLE PROPRIÉTÉ
    }

    public function setNomNiveau(?string $nomNiveau): static // <-- SETTER RENOMMÉ ET PARAMÈTRE MIS À JOUR
    {
        $this->nomNiveau = $nomNiveau; // <-- MODIFIE LA NOUVELLE PROPRIÉTÉ
        return $this;
    }
    // --- FIN DE MISE À JOUR ---

    /**
     * @return Collection<int, AvisClient>
     */
    public function getAvisClients(): Collection
    {
        return $this->avisClients;
    }

    public function addAvisClient(AvisClient $avisClient): static
    {
        if (!$this->avisClients->contains($avisClient)) {
            $this->avisClients->add($avisClient);
            $avisClient->setNiveauService($this);
        }

        return $this;
    }

    public function removeAvisClient(AvisClient $avisClient): static
    {
        if ($this->avisClients->removeElement($avisClient)) {
            // set the owning side to null (unless already changed)
            if ($avisClient->getNiveauService() === $this) {
                $avisClient->setNiveauService(null);
            }
        }

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
            $rendezVousService->setNiveauService($this);
        }

        return $this;
    }

    public function removeRendezVousService(RendezVousService $rendezVousService): static
    {
        if ($this->rendezVousServices->removeElement($rendezVousService)) {
            // set the owning side to null (unless already changed)
            if ($rendezVousService->getNiveauService() === $this) {
                $rendezVousService->setNiveauService(null);
            }
        }

        return $this;
    }
}