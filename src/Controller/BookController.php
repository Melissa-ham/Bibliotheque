<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/books')]
final class BookController extends AbstractController
{
    #[Route('', name: 'book_list', methods: ['GET'])]
    public function index(BookRepository $bookRepository): Response
    {
        $books = $bookRepository->findAll();

        return $this->render('book/index.html.twig', [
            'books' => $books,
        ]);
    }

    #[Route('/create', name: 'book_create', methods: ['GET','POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $book = new Book();

        $form = $this->createForm(BookType::class, $book);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($book);

            $entityManager->flush();

            return $this->redirectToRoute('book_list');
        }

        return $this->render('book/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'book_delete', methods: ['POST'])]
    public function delete(
        Book $book,
        BookRepository $bookRepository,
        EntityManagerInterface $entityManager
    ): Response
    {

        $entityManager->remove($book);

        $entityManager->flush();

        return $this->redirectToRoute('book_list');
    }
}
