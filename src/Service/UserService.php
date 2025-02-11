<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function login(array $data, JWTTokenManagerInterface $JWTManager)
    {
        $user = $this->userRepository->findOneBy(['email' => $data['email']]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Invalid credentials');
        }

        $token = $JWTManager->create($user);

        return ['user' => $user, 'token' => $token];
    }

    public function store(array $data): User
    {
        $user = $this->userRepository->findOneBy(['email' => $data['email']]);
        if ($user) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Email already exists');
        }

        $plaintextPassword = $data['password'];
        $user = new User($data);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );

        $user->setPassword($hashedPassword);

        $this->userRepository->add($user, true);

        return $user;
    }
}
