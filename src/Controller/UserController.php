<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\UserService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



class UserController extends AbstractController
{
    private UserService $userService;
    private EntityManagerInterface $entityManager;

    public function __construct(UserService $userService, EntityManagerInterface $entityManager)
    {
        $this->userService = $userService;
        $this->entityManager = $entityManager;
    }
    #[Route('/user', name: 'user_list')]
    public function list(): Response //entity manager = DTO
    {
        $users = $this->userService->getAllUsers();
        return $this->render('pages/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #Route('/user/add', name: 'user_add',methods: ['GET','POST'])
    public function add(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('user_add', $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token');
            return $this->redirectToRoute('user_list');
        }

        $userData = $request->request->all();
        $this->userService->addUser($userData);

        $this->addFlash('success', 'User added successfully');
        return $this->redirectToRoute('user_add');
    }

    #Route('/user/edit/{id}', name: 'user_edit', methods: ['GET','POST'])
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {

        $user = $this->entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['status' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $this->userService->updateUser($user);

            return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
        }

        return $this->render('pages/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #Route('/user/delete/{id})', name: 'user_delete', methods: ['GET',DELETE'])
    public function delete(int $id, EntityManagerInterface $entityManager) : Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $this->userService->deleteUser($user);

        return $this->redirectToRoute('user_list');
    }
}
