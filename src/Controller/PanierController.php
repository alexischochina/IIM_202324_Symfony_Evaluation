<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PanierRepository;
use App\Entity\Panier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier', methods: ['GET'])]
    public function index(PanierRepository $panierRepository, EntityManagerInterface $entityManager): Response
    {
        
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('panier/index.html.twig', [
            'panier' => $panierRepository->findBy(['userPanier' => $user]),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/panier/{id}', name: 'app_panier_show', methods: ['GET'])]
    public function show(Panier $panier): Response
    {
        return $this->render('panier/show.html.twig', [
            'panier' => $panier,
        ]);
    }

    
}