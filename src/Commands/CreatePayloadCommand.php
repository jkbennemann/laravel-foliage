<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreatePayloadCommand extends Command
{
    public $signature = 'validation:create-payload {name}';

    public $description = 'Creates a new payload class for your application';

    public function handle(): int
    {
        $className = $this->argument('name');
        if (! Str::endsWith($className, 'Payload')) {
            $className .= 'Payload';
        }

        $namespace = config('validate-business-requirements.payload_namespace');

        $content = File::get(base_path('vendor/jkbennemann/laravel-validate-business-requirements/stubs/Payload.stub'));
        $content = Str::replace('{class}', $className, $content);
        $content = Str::replace('{namespace}', $namespace, $content);

        $filepath = Str::startsWith($namespace, 'App')
            ? Str::replace('App', 'app', $namespace)
            : $namespace;
        $filepath = Str::replace('\\', '/', $filepath);
        $filepath = base_path($filepath);
        $filename = $filepath.DIRECTORY_SEPARATOR.$className.'.php';

        File::ensureDirectoryExists($filepath);

        if (File::exists($filename)) {
            $this->info('Payload already exists');

            return self::FAILURE;
        }

        File::put($filename, $content);

        $this->info('Payload created');

        return self::SUCCESS;
    }
}
