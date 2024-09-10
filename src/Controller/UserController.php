<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpCsFixer\Console\Report\FixReport\JsonReporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;


class UserController extends AbstractController
{
    #[Route('/user', name: 'user_list')]
    public function list(EntityManagerInterface $entityManager): Response //entity manager = DTO
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        return $this->render('pages/user/index.html.twig', [
            'users' => $users, //twig dosyasına gönderilecek değişkeni belirtir.
        ]);
    }

    #Route('/user/add', name: 'user_add',methods: ['GET','POST'])
    public function add(Request $request,EntityManagerInterface $entityManager,
                        UserPasswordHasherInterface $passwordHasher,
                        CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        if (!$this->isCsrfTokenValid('user_add', $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token');
            return $this->redirectToRoute('user_list');
        }

        // Create a new User entity
        $user = new User();
        $user->setUsername($request->request->get('username'));
        $user->setPassword($request->request->get('password'));
        $user->setEmail($request->request->get('user_email'));

        // Set roles based on the selected role
        $role = $request->request->get('user_role') == '0' ? ['ROLE_ADMIN'] : ['ROLE_USER'];
        $user->setRoles($role);

        // Generate a random password (you may want to send this to the user via email)
        $plainPassword = "sena123";
        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

//        // Handle avatar upload
//        $avatarFile = $request->files->get('avatar');
//        if ($avatarFile) {
//            $newFilename = uniqid().'.'.$avatarFile->guessExtension();
//            $avatarFile->move(
//                $this->getParameter('avatar_directory'),
//                $newFilename
//            );
//            $user->setAvatarFilename($newFilename);
//        }

        // Persist the new user
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'User added successfully');
        return $this->redirectToRoute('user_add');
    }

    #Route('/user/edit/{id}', name: 'user_edit', methods: ['GET','POST'])
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['status' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

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
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Remove the user
        $entityManager->remove($user);
        $entityManager->flush();

        // Redirect to the user list page
        return $this->redirectToRoute('user_list');
    }
}
