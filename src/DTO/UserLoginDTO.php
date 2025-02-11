<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserLoginDTO
{
    private Assert\Collection $constraints;
    private ValidatorInterface $validator;
    private array $data;

    public function __construct(array $data, ValidatorInterface $validator)
    {
        $this->constraints = new Assert\Collection([
            'email' => new Assert\Required([
                new Assert\NotBlank(),
                new Assert\Email(),
            ]),
            'password' => new Assert\Required([
                new Assert\NotBlank(),
            ]),
        ]);

        $this->data = $data;
        $this->validator = $validator;

        return $this;
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
