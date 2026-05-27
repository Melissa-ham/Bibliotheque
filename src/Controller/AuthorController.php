<?php

namespace App\Controller;

use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/authors')]  // c'est notre route principale
final class AuthorController extends AbstractController
{
    #[Route('', name: 'author_list', methods: ['GET'])]
    public function index(AuthorRepository $authorRepository): Response
    {
        $authors = $authorRepository->findAll();

        return $this->render('author/index.html.twig', [
            'authors' => $authors,
        ]);
    }

    #[Route('/create', name: 'author_create', methods: ['GET','POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $author = new Author();

        $form = $this->createForm(AuthorType::class, $author);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($author);

            $entityManager->flush();

            return $this->redirectToRoute('author_list');
        }

        return $this->render('author/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'author_delete', methods: ['GET'])]
    public function delete(
        int $id,
        AuthorRepository $authorRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $author = $authorRepository->find($id);

        if (!$author) {
            throw $this->createNotFoundException('Auteur introuvable');
        }

        if ($author->getBooks()->isEmpty()) {
            $entityManager->remove($author);
            $entityManager->flush();
            $this->addFlash('success', 'Auteur'. $author->getPrenom() .'supprimé');
            
        } else {
            $this->addFlash('notice', 'Veuillez supprimer les livres de l auteur avant de supprimer l auteur');
        }
            

        return $this->redirectToRoute('author_list');
    }
}