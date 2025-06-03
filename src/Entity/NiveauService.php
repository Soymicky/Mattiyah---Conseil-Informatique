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
    private ?string $nomNiveau = null;

    /**
     * @var Collection<int, Services>
     */
    #[ORM\OneToMany(mappedBy: 'niveauService', targetEntity: Services::class)]
    private Collection $servicesList;

    /**
     * @var Collection<int, RendezVousService>
     */
    #[ORM\OneToMany(targetEntity: RendezVousService::class, mappedBy: 'niveauService')]
    private Collection $rendezVousServices;

    public function __construct()
    {
        $this->servicesList = new ArrayCollection();
        // La ligne $this->rendezVousList = new ArrayCollection(); a été supprimée
        $this->rendezVousServices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomNiveau(): ?string
    {
        return $this->nomNiveau;
    }

    public function setNomNiveau(?string $nomNiveau): static
    {
        $this->nomNiveau = $nomNiveau;

        return $this;
    }

    /**
     * @return Collection<int, Services>
     */
    public function getServicesList(): Collection
    {
        return $this->servicesList;
    }

    public function addServicesList(Services $servicesList): static
    {
        if (!$this->servicesList->contains($servicesList)) {
            $this->servicesList->add($servicesList);
            $servicesList->setNiveauService($this);
        }

        return $this;
    }

    public function removeServicesList(Services $servicesList): static
    {
        if ($this->servicesList->removeElement($servicesList)) {
            // set the owning side to null (unless already changed)
            if ($servicesList->getNiveauService() === $this) {
                $servicesList->setNiveauService(null);
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