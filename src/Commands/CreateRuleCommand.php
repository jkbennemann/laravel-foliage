<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateRuleCommand extends Command
{
    public $signature = 'validation:create-rule {name}';

    public $description = 'Creates a new validation rule for your application';

    public function handle(): int
    {
        $className = $this->argument('name');
        if (! Str::endsWith($className, 'Rule')) {
            $className .= 'Rule';
        }
        $ruleKey = Str::slug(
            Str::remove('Rule', $className, false)
        );

        $namespace = config('validate-business-requirements.rules_namespace');

        $content = File::get(base_path('vendor/jkbennemann/laravel-validate-business-requirements/stubs/Rule.stub'));
        $content = Str::replace('{class}', $className, $content);
        $content = Str::replace('{namespace}', $namespace, $content);
        $content = Str::replace('{key}', $ruleKey, $content);

        $filepath = Str::startsWith($namespace, 'App')
            ? Str::replace('App', 'app', $namespace)
            : $namespace;
        $filepath = Str::replace('\\', '/', $filepath);
        $filepath = base_path($filepath);
        $filename = $filepath.DIRECTORY_SEPARATOR.$className.'.php';

        File::ensureDirectoryExists($filepath);

        if (File::exists($filename)) {
            $this->info('Rule already exists');

            return self::FAILURE;
        }

        File::put($filename, $content);

        $this->info('Rule created');

        return self::SUCCESS;
    }
}
