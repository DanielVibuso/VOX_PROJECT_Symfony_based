<?php

namespace App\Service;

use App\Entity\Partner;
use App\Repository\CompanyRepository;
use App\Repository\PartnerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PartnerService
{
    public function __construct(private PartnerRepository $partnerRepository,
        private EntityManagerInterface $entityManager,
        private CompanyRepository $companyRepository
    ) {
    }

    public function findAll(int $page = 1, int $limit = 10)
    {
        $offset = ($page - 1) * $limit;

        return $this->partnerRepository->findBy([], null, $limit, $offset);
    }

    public function countAll()
    {
        return $this->partnerRepository->count();
    }

    public function findById(int $id): ?Partner
    {
        $partner = $this->partnerRepository->find($id);

        if (!$partner) {
            throw new \Exception('partner not found');
        }

        return $partner;
    }

    public function store($data)
    {
        $newPartner = new Partner();
        $newPartner->setName($data['name']);
        $newPartner->setCpf($data['cpf']);
        $newPartner->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        $newPartner->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));

        $this->partnerRepository->add($newPartner);

        return $newPartner;
    }

    public function update(int $id, array $data): Partner
    {
        $partner = $this->partnerRepository->find($id);

        if (!$partner) {
            throw new \Exception('partner not found');
        }

        $partner->setName($data['name'] ?? $partner->getName());
        $partner->setCpf($data['cpf'] ?? $partner->getCpf());
        $partner->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));

        $this->entityManager->flush();

        return $partner;
    }

    public function delete(int $id)
    {
        $partner = $this->partnerRepository->find($id);

        if (!$partner) {
            throw new \Exception('partner not found');
        }

        $this->entityManager->remove($partner);

        $this->entityManager->flush();
    }

    public function addCompany(int $partnerId, int $companyId): void
    {
        $partner = $this->partnerRepository->find($partnerId);
        if (!$partner) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Partner not found');
        }

        $company = $this->companyRepository->find($companyId);
        if (!$company) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Company not found');
        }

        $partner->addCompany($company);

        $this->entityManager->flush();
    }

    public function removeCompany(int $partnerId, int $companyId): Partner
    {
        $company = $this->companyRepository->find($companyId);
        if (!$company) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Company not found');
        }

        $partner = $this->partnerRepository->find($partnerId);
        if (!$partner) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Partner not found');
        }

        if (!$partner->getCompanies()->contains($company)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Partner does not belong to the company');
        }

        $partner->removeCompany($company);

        $this->entityManager->flush();

        return $partner;
    }

    public function getCompanies(int $partnerId)
    {
        $partner = $this->partnerRepository->find($partnerId);
        if (!$partner) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Partner not found');
        }

        return $partner->getCompanies();
    }
}
