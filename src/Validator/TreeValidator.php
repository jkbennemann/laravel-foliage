<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Validator;

use Jkbennemann\BusinessRequirements\Core\Node;
use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;
use Jkbennemann\BusinessRequirements\Validator\Contracts\BaseValidator;

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

        return $this->strategy->evaluateNode($rootNode, $payload);
    }
}
