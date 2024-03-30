<?php

namespace App\Controller;

use App\Entity\ContenuPanier;
use App\Entity\Panier;
use App\Entity\User;
use App\Form\ContenuPanierType;
use App\Repository\ContenuPanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/contenu/panier')]
class ContenuPanierController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/', name: 'app_contenu_panier_index', methods: ['GET'])]
    public function index(ContenuPanierRepository $contenuPanierRepository , EntityManagerInterface $entityManager): Response
    {
        
          /** @var User $user */
          $user = $this->getUser();

        $panierValide = $entityManager->getRepository(Panier::class)->findPanierActif($user);


        if($panierValide == null){
            $panierValide = new Panier();
            $panierValide->setUserPanier($user);
            $panierValide->setEtat(false);
            $entityManager->persist($panierValide);
        }

        return $this->render('contenu_panier/index.html.twig', [
            'contenu_paniers' => $contenuPanierRepository->findBy(['panier' => $panierValide]),
        ]);
    }

    #[Route('/new', name: 'app_contenu_panier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $contenuPanier = new ContenuPanier();
        $form = $this->createForm(ContenuPanierType::class, $contenuPanier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contenuPanier);
            $entityManager->flush();
            $this->addFlash('success', $translator->trans('cart_content.new', [], 'message'));

            return $this->redirectToRoute('app_contenu_panier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contenu_panier/new.html.twig', [
            'contenu_panier' => $contenuPanier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contenu_panier_show', methods: ['GET'])]
    public function show(ContenuPanier $contenuPanier): Response
    {
        return $this->render('contenu_panier/show.html.twig', [
            'contenu_panier' => $contenuPanier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contenu_panier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ContenuPanier $contenuPanier, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ContenuPanierType::class, $contenuPanier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', $translator->trans('cart_content.edit', [], 'message'));

            return $this->redirectToRoute('app_contenu_panier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contenu_panier/edit.html.twig', [
            'contenu_panier' => $contenuPanier,
            'form' => $form,
        ]);
    }
    #[IsGranted('ROLE_USER')]
    #[Route('/add/{id}', name: 'app_contenu_panier_add', methods: ['GET'])]
    public function add(EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {

        /** @var User $user */
        $user = $this->getUser();

        $panierValide = $entityManager->getRepository(Panier::class)->findPanierActif($user);

        if($panierValide != null){
            $panierValide->setEtat(true);
            $panierValide->setDateAchat(new \DateTime());
            $entityManager->persist($panierValide);
            $entityManager->flush();
            $this->addFlash('success', $translator->trans('cart_content.add', [], 'message'));
        }

        return $this->redirectToRoute('app_contenu_panier_index');
    }
    #[Route('/{id}', name: 'app_contenu_panier_delete', methods: ['POST'])]
    public function delete(Request $request, ContenuPanier $contenuPanier, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contenuPanier->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($contenuPanier);
            $entityManager->flush();
            $this->addFlash('success', $translator->trans('cart_content.delete', [], 'message'));
        }

        return $this->redirectToRoute('app_contenu_panier_index', [], Response::HTTP_SEE_OTHER);
    }
}
