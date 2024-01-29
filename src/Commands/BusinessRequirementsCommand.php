<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Commands;

use Illuminate\Console\Command;

class BusinessRequirementsCommand extends Command
{
    public $signature = 'laravel-validate-business-requirements';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
