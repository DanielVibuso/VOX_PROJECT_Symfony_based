<?php

namespace App\Controller;

use App\DTO\CompanyDTO;
use App\Service\CompanyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api/company', name: 'company_')]
class CompanyController extends AbstractController
{
    public function __construct(protected CompanyService $companyService)
    {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $companies = $this->companyService->findAll($page, $limit);

        $totalCompanies = $this->companyService->countAll();

        return $this->json([
            'data' => $companies,
            'page' => $page,
            'limit' => $limit,
            'total' => $totalCompanies,
        ], Response::HTTP_OK, [], [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['partners', 'createdAt', 'updatedAt'],
        ]);
    }

    #[Route('/{id}', name: 'find', methods: ['GET'])]
    public function findById(int $id): Response
    {
        $companies = $this->companyService->findById($id);

        return $this->json(['data' => $companies], Response::HTTP_OK, [], [ObjectNormalizer::IGNORED_ATTRIBUTES => ['partners', 'createdAt', 'updatedAt']]);
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(Request $request, ValidatorInterface $validator): Response
    {
        $data = json_decode($request->getContent(), true);

        $errors = (new CompanyDTO($data, $validator))->validate();

        if ($errors) {
            return $this->json(
                data: [
                    'message' => 'Validation error',
                    'errors' => $errors,
                ],
                status: Response::HTTP_BAD_REQUEST
            );
        }

        $company = $this->companyService->store($data);

        return $this->json(['data' => $company], Response::HTTP_CREATED, [], [ObjectNormalizer::IGNORED_ATTRIBUTES => ['partners', 'createdAt', 'updatedAt']]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $company = $this->companyService->update($id, $data);

        return $this->json(['data' => 'Company updated: '.$company->getName()], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->companyService->delete($id);

        return $this->json([], Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/partner', name: 'add-partner', methods: ['POST'])]
    public function addPartner(int $id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $company = $this->companyService->addPartner($id, $data['partnerId']);

        return $this->json(['data' => 'Partner added to company: '.$company->getName()], Response::HTTP_CREATED);
    }

    #[Route('/{idCompany}/remove-partner/{idPartner}', name: 'remove-partner', methods: ['DELETE'])]
    public function removePartner(int $idCompany, int $idPartner): Response
    {
        $company = $this->companyService->removePartner($idCompany, $idPartner);

        return $this->json(['data' => 'Partner removed from company: '.$company->getName()], Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/partners', name: 'get-partners', methods: ['GET'])]
    public function getPartner(int $id): Response
    {
        $companyPartners = $this->companyService->getPartners($id);

        return $this->json([
            'data' => $companyPartners,
        ], Response::HTTP_OK, [], [
            ObjectNormalizer::IGNORED_ATTRIBUTES => ['companies', 'createdAt', 'updatedAt'],
        ]);
    }
}
