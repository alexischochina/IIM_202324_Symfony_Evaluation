<?php

namespace App\Controller;

use App\Entity\ContenuPanier;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/users-panier', name: 'app_admin_users_panier')]
    public function usersPanier(EntityManagerInterface $entityManager): Response
    {
        $paniersActifs = $entityManager->getRepository(Panier::class)->findAllPanierActif();

        return $this->render('admin/users_panier/index.html.twig', [
            'paniers' => $paniersActifs,
        ]);
    }

    #[Route('/users-panier/{id}', name: 'app_admin_users_panier_show')]
    public function usersPanierShow(Panier $panier, EntityManagerInterface $entityManager): Response
    {
        $id = $panier->getId();
        $contenu_panier = $entityManager->getRepository(ContenuPanier::class)->findContenuPanierByPanierId($id);
        $produits = $entityManager->getRepository(Produit::class)->findProduitByPanierId($id);

        $contenu = [];
        foreach ($contenu_panier as $index=>$item) {
            $produit = $item->getProduit();
            $quantite = $item->getQquantite();
            $contenu[] = [
                //'produit' => $produit,
                'produit' => $produits[$index],
                'quantite' => $quantite,
            ];
        }

        //$contenu = $entityManager->getRepository(Produit::class)->findProduitByPanierId($id);

        return $this->render('admin/users_panier/show.html.twig', [
            'contenu_panier' => $contenu_panier,
            'contenu' => $contenu,
        ]);
    }
}
