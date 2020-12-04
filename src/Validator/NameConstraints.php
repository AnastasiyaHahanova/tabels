<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class NameConstraints extends Constraint
{
    public $message = 'Invalid user parameters';

    public function validatedBy()
    {
        return NameConstraintsValidator::class;
    }

}