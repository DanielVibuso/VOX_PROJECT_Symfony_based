<?php

namespace App\Controller;

use App\DTO\PartnerDTO;
use App\Service\PartnerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api/partner', name: 'partner_')]
class PartnerController extends AbstractController
{
    public function __construct(protected PartnerService $partnerService)
    {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $partners = $this->partnerService->findAll($page, $limit);

        $totalPartners = $this->partnerService->countAll();

        return $this->json([
            'data' => $partners,
            'page' => $page,
            'limit' => $limit,
            'total' => $totalPartners,
        ], Response::HTTP_OK, [], [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['companies', 'createdAt', 'updatedAt'],
        ]);
    }

    #[Route('/{id}', name: 'find', methods: ['GET'])]
    public function findById(int $id): Response
    {
        $partner = $this->partnerService->findById($id);

        return $this->json([
            'data' => $partner,
        ], Response::HTTP_OK, [], [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['createdAt', 'updatedAt'],
        ]);
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(Request $request, ValidatorInterface $validator): Response
    {
        $data = json_decode($request->getContent(), true);

        $errors = (new PartnerDTO($data, $validator))->validate();

        if ($errors) {
            return $this->json(
                data: [
                    'message' => 'Validation error',
                    'errors' => $errors,
                ],
                status: Response::HTTP_BAD_REQUEST
            );
        }

        $partner = $this->partnerService->store($data);

        return $this->json(['data' => $partner], Response::HTTP_CREATED, [], [ObjectNormalizer::IGNORED_ATTRIBUTES => ['companies', 'createdAt', 'updatedAt']]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $partner = $this->partnerService->update($id, $data);

        return new JsonResponse(['data' => 'Partner updated: '.$partner->getName()], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->partnerService->delete($id);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    #[Route('/{idPartner}/remove-company/{idCompany}', name: 'remove-company', methods: ['DELETE'])]
    public function removePartner(int $idPartner, int $idCompany): Response
    {
        $this->partnerService->removeCompany($idPartner, $idCompany);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/company', name: 'add-company', methods: ['POST'])]
    public function addCompany(int $id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $this->partnerService->addCompany($id, $data['companyId']);

        return new Response('Partner added to company', Response::HTTP_CREATED);
    }

    #[Route('/{id}/companies', name: 'get-companies', methods: ['GET'])]
    public function getPartner(int $id, SerializerInterface $serializer): Response
    {
        $partnerCompanies = $this->partnerService->getCompanies($id);

        $jsonContent = $serializer->serialize($partnerCompanies, 'json', ['groups' => ['partner_companies_group']]);

        return new JsonResponse($jsonContent, JsonResponse::HTTP_OK, [], true);
    }
}
