<?php

namespace App\Validator;

use App\Entity\User;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PasswordConstraintsValidator extends ConstraintValidator
{
    public function validate($password, Constraint $constraint)
    {
        if (!$constraint instanceof PasswordConstraints) {
            throw new UnexpectedTypeException($constraint, PasswordConstraints::class);
        }

        if (null === $password || '' === $password) {
            return;
        }

        if (mb_strlen($password) < User::PASSWORD_LENGTH) {
            $this->context->buildViolation('The password {{ value }} is too short')
                          ->setParameter('{{ value }}', $password)
                          ->addViolation();
        }

        if (!preg_match('/^[a-zA-Z0-9]+$/', $password, $matches)) {
            $this->context->buildViolation($constraint->message)
                          ->setParameter('{{ value }}', $password)
                          ->addViolation();
        }
    }
}