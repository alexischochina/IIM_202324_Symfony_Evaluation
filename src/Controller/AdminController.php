<?php

namespace App\Controller;

use App\Entity\ContenuPanier;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
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
        $user = $entityManager->getRepository(User::class)->findUserByPanierId($id);

        $contenu = [];
        foreach ($contenu_panier as $index=>$item) {
            $quantite = $item->getQquantite();
            $contenu[] = [
                'produit' => $produits[$index],
                'quantite' => $quantite,
            ];
        }

        return $this->render('admin/users_panier/show.html.twig', [
            'user' => $user,
            'contenu_panier' => $contenu_panier,
            'contenu' => $contenu,
        ]);
    }

    #[Route('/recent-users', name: 'app_admin_recent_users')]
    public function recentUsers(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findRecentUsers();

        return $this->render('admin/recent_users.html.twig', [
            'users' => $users,
        ]);
    }
}
