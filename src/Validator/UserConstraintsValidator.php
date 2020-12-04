<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UserConstraintsValidator extends ConstraintValidator
{
    public function validate($name, Constraint $constraint)
    {
        if (!$constraint instanceof UserConstraints) {
            throw new UnexpectedTypeException($constraint, UserConstraints::class);
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