<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Core;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use ReflectionException;

trait HasValidationRules
{
    protected string $rulesField = 'validation_rules';

    /**
     * @throws ReflectionException|BindingResolutionException|Exception
     */
    public function ruleTree(): Node
    {
        $rules = $this->getAttribute($this->rulesField);
        $rulesArray = is_string($rules) ? json_decode($rules, true) : $rules;
        $builder = new TreeBuilder();

        if (empty($rulesArray)) {
            $rulesArray = [Rule::empty()->node()->toArray()];
        }

        return $builder->build($rulesArray);
    }
}
