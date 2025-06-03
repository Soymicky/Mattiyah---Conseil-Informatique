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

    /**
     * @var Collection<int, RendezVous>
     */
    #[ORM\ManyToMany(targetEntity: RendezVous::class, mappedBy: 'services')]
    private Collection $rendezVousList;

    /**
     * @var Collection<int, RendezVousService>
     */
    #[ORM\OneToMany(targetEntity: RendezVousService::class, mappedBy: 'services')]
    private Collection $rendezVousServices;

    public function __construct()
    {
        $this->rendezVousList = new ArrayCollection();
        $this->rendezVousServices = new ArrayCollection();
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

    /**
     * @return Collection<int, RendezVous>
     */
    public function getRendezVousList(): Collection
    {
        return $this->rendezVousList;
    }

    public function addRendezVousList(RendezVous $rendezVousList): static
    {
        if (!$this->rendezVousList->contains($rendezVousList)) {
            $this->rendezVousList->add($rendezVousList);
        }

        return $this;
    }

    public function removeRendezVousList(RendezVous $rendezVousList): static
    {
        $this->rendezVousList->removeElement($rendezVousList);

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
            $rendezVousService->setServices($this);
        }

        return $this;
    }

    public function removeRendezVousService(RendezVousService $rendezVousService): static
    {
        if ($this->rendezVousServices->removeElement($rendezVousService)) {
            // set the owning side to null (unless already changed)
            if ($rendezVousService->getServices() === $this) {
                $rendezVousService->setServices(null);
            }
        }

        return $this;
    }
}