<?php

namespace WongDoody\PhpUmlCrafter\Commands;

use Illuminate\Console\Command;
use ReflectionException;
use WongDoody\PhpUmlCrafter\Services\ScannerService;

class PhpUmlCrafterScanner extends Command
{
    protected $signature = 'uml-crafter:scan
        {--project-path= : The path of the project to scan}
        {--exclude-directories= : List of directories to exclude as comma-separated string}
        {--exclude-files= : List of files to exclude as comma-separated string}
        {--save-path= : The path to save the scanned data}
        {--include-traits= : add true to include traits and interfaces}';

    protected $description = 'Scan classes to retrieve methods and relations';

    /**
     * @throws ReflectionException
     */
    public function handle(ScannerService $scannerService)
    {
        $projectPath = $this->option('project-path') ?? base_path();
        $excludeFiles = $this->option('exclude-files') ?
            array_map('trim', explode(',', $this->option('exclude-files'))) :
            config('php-uml-crafter.excludeFiles', []);
        $excludeDirectories = $this->option('exclude-directories') ?
            array_map('trim', explode(',', $this->option('exclude-directories'))) :
            config('php-uml-crafter.excludeDirectories', []);

        $includeTraitsAndInterfaces = $this->option('include-traits') === 'true';

        $classes = $scannerService->execute($projectPath, $excludeFiles, $excludeDirectories, $includeTraitsAndInterfaces);

        $filePath = $this->option('save-path') ?? storage_path('app/uml_data.json');
        file_put_contents($filePath, json_encode($classes));

        $this->info('Class scan was saved to ' . realpath($filePath));
    }
}
