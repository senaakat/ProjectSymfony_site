<?php

namespace App\Service;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    public function getAllUsers(): array
    {
        return $this->entityManager->getRepository(User::class)->findAll();
    }

    public function addUser(array $userData): User
    {
        $user = new User();
        $user->setUsername($userData['username']);
        $user->setSurname($userData['surname']);
        $user->setEmail($userData['user_email']);

        // Set roles
        $role = $userData['user_role'] === '0' ? ['ROLE_ADMIN'] : ['ROLE_USER'];
        $user->setRoles($role);

        // Hash the password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password']);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function updateUser(User $user): void
    {
        $this->entityManager->flush();
    }

    public function deleteUser(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}