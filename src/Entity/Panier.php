<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateAchat = null;

    #[ORM\Column]
    private ?bool $etat = null;

    #[ORM\OneToOne(mappedBy: 'panier', cascade: ['persist', 'remove'])]
    private ?ContenuPanier $contenuPanier = null;

    #[ORM\ManyToOne(inversedBy: 'paniers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userPanier = null;

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

    public function getContenuPanier(): ?ContenuPanier
    {
        return $this->contenuPanier;
    }

    public function setContenuPanier(?ContenuPanier $contenuPanier): static
    {
        // unset the owning side of the relation if necessary
        if ($contenuPanier === null && $this->contenuPanier !== null) {
            $this->contenuPanier->setPanier(null);
        }

        // set the owning side of the relation if necessary
        if ($contenuPanier !== null && $contenuPanier->getPanier() !== $this) {
            $contenuPanier->setPanier($this);
        }

        $this->contenuPanier = $contenuPanier;

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
}
