<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class TaskDateTimeRange extends Constraint
{
    public string $startField;
    public string $endField;

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
