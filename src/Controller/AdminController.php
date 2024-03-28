<?php

namespace App\Controller;

use App\Entity\Panier;
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
    public function usersPanierShow(Panier $panier): Response
    {
        $contenu_panier = $panier->getContenuPanier();
        $test = $panier->getId();

        return $this->render('admin/users_panier/show.html.twig', [
            'test' => $test,
            'contenu_panier' => $contenu_panier,
        ]);
    }
}
