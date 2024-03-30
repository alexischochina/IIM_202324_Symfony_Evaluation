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
use Symfony\Contracts\Translation\TranslatorInterface;

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
    public function new(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
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
                    $this->addFlash('danger', $translator->trans('product.file', [], 'message'));
                    return $this->redirectToRoute('app_produit_index');
                }
                $produit->setPhoto($newFilename);
            }
            $entityManager->persist($produit);
            $entityManager->flush();
            $this->addFlash('success', $translator->trans('product.new', [], 'message'));

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
    public function add(Produit $produit, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $contenuPanier = new ContenuPanier();

        $contenuPanier->addProduit($produit);
        $contenuPanier->setQquantite(1);
        $contenuPanier->setDate(new \DateTime());

        /** @var User $user */
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
        $this->addFlash('success', $translator->trans('product.add', [], 'message'));

        return $this->redirectToRoute('app_contenu_panier_index');
    }

    /**
     * @throws Exception
     */
    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', $translator->trans('product.edit', [], 'message'));

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        if($this->isCsrfTokenValid('delete' . $produit->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
            $this->addFlash('success', $translator->trans('product.delete', [], 'message'));
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }
}
