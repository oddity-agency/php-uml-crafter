<?php

namespace WongDoody\PhpUmlCrafter\Commands;

use Illuminate\Console\Command;
use WongDoody\PhpUmlCrafter\Services\GeneratorService;

class PhpUmlCrafterGenerator extends Command
{
    protected $signature = 'uml-crafter:generate
        {--source-path= : The path to load the scanned data}
        {--save-path= : The path to save the uml}';

    protected $description = 'Generate a plantuml';

    public function handle(GeneratorService $generatorService)
    {
        $filePath = $this->option('source-path') ?? storage_path('app/uml_data.json');
        $data = json_decode(file_get_contents($filePath), true);
        $plantUmlSyntax = $generatorService->execute($data);

        $umlFilePath = $this->option('save-path') ?? storage_path('app/uml_diagram.puml');
        file_put_contents($umlFilePath, $plantUmlSyntax);

        $this->info('PlantUML diagram generated. Saved to ' . realpath($umlFilePath));
    }
}
