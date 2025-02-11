<?php

namespace App\Controller;

use App\DTO\UserDTO;
use App\DTO\UserLoginDTO;
use App\Service\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api', name: 'user')]
class UserController extends AbstractController
{
    public function __construct(private UserService $service)
    {
    }

    #[Route('/login', name: 'user_login', methods: ['POST'], format: 'json')]
    public function login(Request $request, ValidatorInterface $validator, JWTTokenManagerInterface $JWTManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $dto = new UserLoginDTO($data, $validator);

        $errors = $dto->validate();

        if ($errors) {
            return $this->json(
                data: [
                    'message' => 'Validation error',
                    'errors' => $errors,
                ],
                status: Response::HTTP_BAD_REQUEST
            );
        }

        $serviceResponse = $this->service->login($data, $JWTManager);

        return $this->json(
            data: [
                'message' => 'User logged in!',
                'data' => $serviceResponse,
            ],
            status: Response::HTTP_OK,
            headers: [],
            context: [AbstractNormalizer::IGNORED_ATTRIBUTES => ['password']]
        );
    }

    #[Route('/user/register', name: 'user_store', methods: ['POST'], format: 'json')]
    public function store(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $dto = new UserDTO($data, $validator);

        $errors = $dto->validate();

        if ($errors) {
            return $this->json(
                data: [
                    'message' => 'Validation error',
                    'errors' => $errors,
                ],
                status: Response::HTTP_BAD_REQUEST
            );
        }

        $user = $this->service->store($data);

        return $this->json(
            data: ['message' => 'User created!', 'data' => $user],
            status: Response::HTTP_CREATED,
            headers: [],
            context: [AbstractNormalizer::IGNORED_ATTRIBUTES => ['password', 'userIdentifier']]
        );
    }
}
