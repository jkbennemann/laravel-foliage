<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Core\Payload;

trait CanHandleUpdate
{
    private bool $isUpdate = false;

    public function setUpdate(bool $isUpdate): void
    {
        $this->isUpdate = $isUpdate;
    }

    public function isUpdate(): bool
    {
        return $this->isUpdate;
    }
}
