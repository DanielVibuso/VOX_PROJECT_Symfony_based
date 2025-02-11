<?php

namespace App\Service;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Repository\PartnerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CompanyService
{
    public function __construct(private CompanyRepository $companyRepository,
        private EntityManagerInterface $entityManager,
        private PartnerRepository $partnerRepository)
    {
    }

    public function findAll(int $page = 1, int $limit = 10)
    {
        $offset = ($page - 1) * $limit;

        return $this->companyRepository->findBy([], null, $limit, $offset);
    }

    public function countAll()
    {
        return $this->companyRepository->count();
    }

    public function findById(int $id): ?Company
    {
        $company = $this->companyRepository->find($id);

        if (!$company) {
            throw new \Exception('Company not found');
        }

        return $company;
    }

    public function store($data)
    {
        $newCompany = new Company();
        $newCompany->setName($data['name']);
        $newCompany->setCnpj($data['cnpj']);
        $newCompany->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        $newCompany->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));

        $this->companyRepository->add($newCompany);

        return $newCompany;
    }

    public function update(int $id, array $data)
    {
        $company = $this->companyRepository->find($id);

        if (!$company) {
            throw new \Exception('Company not found');
        }

        $company->setName($data['name'] ?? $company->getName());
        $company->setCnpj($data['cnpj'] ?? $company->getCnpj());
        $company->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));

        $this->entityManager->flush();

        return $company;
    }

    public function delete(int $id)
    {
        $company = $this->companyRepository->find($id);

        if (!$company) {
            throw new \Exception('Company not found');
        }

        $this->entityManager->remove($company);

        $this->entityManager->flush();
    }

    public function addPartner(int $companyId, int $partnerId)
    {
        $company = $this->companyRepository->find($companyId);
        if (!$company) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Company not found');
        }

        $partner = $this->partnerRepository->find($partnerId);
        if (!$partner) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Partner not found');
        }

        $company->addPartner($partner);

        $this->entityManager->flush();

        return $company;
    }

    public function removePartner(int $companyId, int $partnerId): Company
    {
        $company = $this->companyRepository->find($companyId);
        if (!$company) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Company not found');
        }

        // Verificar se o parceiro existe
        $partner = $this->partnerRepository->find($partnerId);
        if (!$partner) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Partner not found');
        }

        if (!$company->getPartners()->contains($partner)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Partner does not belong to the company');
        }

        $company->removePartner($partner);

        $this->entityManager->flush();

        return $company;
    }

    public function getPartners(int $companyId)
    {
        $company = $this->companyRepository->find($companyId);
        if (!$company) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Company not found');
        }

        return $company->getPartners();
    }
}
