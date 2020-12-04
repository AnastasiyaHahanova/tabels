<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class UserConstraints extends Constraint
{
    public $message = 'Invalid user parameters';

    public function validatedBy()
    {
        return UserConstraintsValidator::class;
    }

}