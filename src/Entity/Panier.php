<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateAchat = null;

    #[ORM\Column]
    private ?bool $etat = null;

    #[ORM\ManyToOne(inversedBy: 'paniers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userPanier = null;

    #[ORM\OneToMany(targetEntity: ContenuPanier::class, mappedBy: 'panier', orphanRemoval: true)]
    private Collection $contenuPanier;

    public function __construct()
    {
        $this->contenuPanier = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateAchat(): ?\DateTimeInterface
    {
        return $this->dateAchat;
    }

    public function setDateAchat(?\DateTimeInterface $dateAchat): static
    {
        $this->dateAchat = $dateAchat;

        return $this;
    }

    public function isEtat(): ?bool
    {
        return $this->etat;
    }

    public function setEtat(bool $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getUserPanier(): ?User
    {
        return $this->userPanier;
    }

    public function setUserPanier(?User $userPanier): static
    {
        $this->userPanier = $userPanier;

        return $this;
    }

    /**
     * @return Collection<int, ContenuPanier>
     */
    public function getContenuPanier(): Collection
    {
        return $this->contenuPanier;
    }

    public function addContenuPanier(ContenuPanier $contenuPanier): static
    {
        if (!$this->contenuPanier->contains($contenuPanier)) {
            $this->contenuPanier->add($contenuPanier);
            $contenuPanier->setPanier($this);
        }

        return $this;
    }

    public function removeContenuPanier(ContenuPanier $contenuPanier): static
    {
        if ($this->contenuPanier->removeElement($contenuPanier)) {
            // set the owning side to null (unless already changed)
            if ($contenuPanier->getPanier() === $this) {
                $contenuPanier->setPanier(null);
            }
        }

        return $this;
    }
}
