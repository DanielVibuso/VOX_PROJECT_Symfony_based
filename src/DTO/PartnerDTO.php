<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PartnerDTO
{
    private Assert\Collection $constraints;

    public function __construct(private array $data, private ValidatorInterface $validator)
    {
        $this->constraints = new Assert\Collection([
            'name' => new Assert\Required([
                new Assert\NotBlank(),
                new Assert\Length(min: 1, max: 255),
            ]),
            'cpf' => new Assert\Required([
                new Assert\NotBlank(),
                new Assert\Length(min: 11, max: 11),
            ]),
        ]);

        $this->data = $data;
        $this->validator = $validator;
    }

    public function getConstraints(): Assert\Collection
    {
        return $this->constraints;
    }

    public function validate(): array|bool
    {
        $violations = $this->validator->validate($this->data, $this->getConstraints());

        if ($violations->count() > 0) {
            $err = [];
            foreach ($violations as $violation) {
                $err[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }

            return $err;
        }

        return false;
    }
}
