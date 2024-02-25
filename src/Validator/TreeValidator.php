<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Validator;

use Jkbennemann\Foliage\Core\Node;
use Jkbennemann\Foliage\Exceptions\RuleValidation;
use Jkbennemann\Foliage\Validator\Contracts\BaseValidator;

class TreeValidator extends BaseValidator
{
    /**
     * @throws RuleValidation
     */
    public function evaluate(Node $rootNode, array $payload): bool
    {
        if ($rootNode->isEmpty()) {
            return true;
        }

        return $this->strategy->evaluateNode($rootNode, $payload, null);
    }
}
