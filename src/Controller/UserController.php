<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Book;
use App\Form\UserType;
use App\Form\AddBookType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users')]
final class UserController extends AbstractController
{
    #[Route('', name: 'user_list', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/create', name: 'user_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($user);

            $entityManager->flush();

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'user_delete', methods: ['GET'])]
    public function delete(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable');
        }

        $entityManager->remove($user);

        $entityManager->flush();

        return $this->redirectToRoute('user_list');
    }
    #[Route('/{id}/library', name: 'user_library', methods: ['GET'])]
public function library(
    User $user,
    EntityManagerInterface $entityManager
): Response
{
    $bookRepository = $entityManager->getRepository(Book::class);

    $books = $bookRepository->findAll();

    return $this->render('user/library.html.twig', [
        'user' => $user,
        'allBooks' => $books,
    ]);
}
    #[Route('/{id}/add_book', name: 'user_add_book', methods: ['GET', 'POST'])]
public function addBook(
    User $user,
    Request $request,
    EntityManagerInterface $entityManager
): Response
{
    $form = $this->createForm(AddBookType::class);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $books = $form->get('books')->getData();
        if ($user->getBooks()->contains($books)) {
            $this->addFlash('notice', "Livre déjà présent dans la bibliothèque");
        } else {
            $this->addFlash('success', "Livre ajouté la bibliothèque");
        }
        $entityManager->flush();

        return $this->redirectToRoute('user_library', [
            'id' => $user->getId(),
        ]);
    }

    return $this->render('user/add_book.html.twig', [
        'user' => $user,
        'form' => $form->createView(),
    ]);
}
#[Route('/{user}/remove_book/{book}', name: 'user_remove_book', methods: ['GET'])]
public function removeBook(
    User $user,
    Book $book,
    EntityManagerInterface $entityManager
): Response
{
    $user->removeBook($book);

    $entityManager->flush();

    return $this->redirectToRoute('user_library', [
        'id' => $user->getId(),
    ]);
}
}