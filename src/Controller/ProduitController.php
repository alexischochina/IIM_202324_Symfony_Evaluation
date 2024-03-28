<?php

namespace App\Controller;

use App\Entity\ContenuPanier;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\User;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/produit')]
class ProduitController extends AbstractController
{
    #[Route('/', name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('photo')->getData();

            if($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch(FileException $e) {
                    $this->addFlash('danger', "Impossible d'uploader le fichier");
                    return $this->redirectToRoute('app_produit_index');
                }
                $produit->setPhoto($newFilename);
            }
            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }
    #[Route('/add/{id}', name: 'app_produit_add', methods: ['GET'])]
    public function add(Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $contenuPanier = new ContenuPanier();

        $contenuPanier->addProduit($produit);
        $contenuPanier->setQquantite(1);
        $contenuPanier->setDate(new \DateTime());
        //$contenuPanier->setPanier($this->getUser()->getUserPanier());

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $panierValide = $entityManager->getRepository(Panier::class)->findPanierActif($user);

        if($panierValide == null){
            $panierValide = new Panier();
            $panierValide->setUserPanier($user);
            $panierValide->setEtat(false);
            $entityManager->persist($panierValide);
        }

        $panierValide->addContenuPanier($contenuPanier);
        $entityManager->persist($contenuPanier);
        $entityManager->flush();

        return $this->redirectToRoute('app_contenu_panier_index');
    }

    /**
     * @throws Exception
     */
    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        $user = $this->getUser();
        if(!$user) {
            throw $this->createNotFoundException('Vous devez être connecté pour accéder à votre panier.');
        }

        if($user instanceof User) {
            $paniers = $user->getPaniers();
        } else {
            throw new Exception('L\'objet fourni n\'est pas une instance de la classe User.');
        }

        $paniersNonValides = array_filter($paniers->toArray(), function($panier) {
            return !$panier->getEtat();
        });

        $contenuPanier = new ContenuPanier();
        $contenuPanier->addProduit($produit);
        $paniersNonValides[0]->addContenuPanier($contenuPanier);


        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if($this->isCsrfTokenValid('delete' . $produit->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }
}
