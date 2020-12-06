<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NameConstraintsValidator extends ConstraintValidator
{
    public function validate($name, Constraint $constraint)
    {
        if (!$constraint instanceof NameConstraints) {
            throw new UnexpectedTypeException($constraint, NameConstraints::class);
        }

        if (null === $name || '' === $name) {
            return;
        }

        if (!preg_match('/^[a-zA-Z]+[0-9]*$/', $name, $matches)) {
            $this->context->buildViolation($constraint->message)
                          ->setParameter('{{ value }}', $name)
                          ->addViolation();
        }
    }
}