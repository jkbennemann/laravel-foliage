<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Core;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Jkbennemann\Foliage\Facades\Rule as RuleAlias;
use ReflectionException;

trait HasValidationRules
{
    /**
     * @throws ReflectionException|BindingResolutionException|Exception
     */
    public function validationNode(): Node
    {
        $rules = $this->getAttribute($this->rulesFieldName());
        $rulesArray = is_string($rules) ? json_decode($rules, true) : $rules;
        $builder = app(TreeBuilder::class);

        if (empty($rulesArray)) {
            $rulesArray = [RuleAlias::empty()->node()->toArray()];
        }

        return $builder->build($rulesArray);
    }

    protected function rulesFieldName(): string
    {
        return 'validation_rules';
    }
}
