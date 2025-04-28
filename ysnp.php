<?php

require_once __DIR__ . '/vendor/autoload.php';

use YouShallNotParse\NameMapper;
use YouShallNotParse\FileProcessor;
use YouShallNotParse\CodeTransformer;
use YouShallNotParse\ProgressSpinner;

if (!file_exists('ysnp.config.json')) {
    die("Error: ysnp.config.json not found\n");
}

if (!file_exists('ysnp.safety.json')) {
    die("Error: ysnp.safety.json not found\n");
}

$mapper = new NameMapper('ysnp.config.json', 'ysnp.safety.json');
$processor = new FileProcessor($mapper);
$config = json_decode(file_get_contents('ysnp.config.json'), true);
$spinner = new ProgressSpinner();

if (empty($config['source_directory'])) {
    die("Error: source_directory not specified in config\n");
}

if (!is_dir($config['source_directory'])) {
    die("Error: source_directory '{$config['source_directory']}' not found\n");
}

if (empty($config['destination_directory'])) {
    die("Error: destination_directory not specified in config\n");
}

if (file_exists($config['destination_directory'])) {
    echo "Warning: destination_directory '{$config['destination_directory']}' already exists.\n";
    echo "This will overwrite any existing files in this directory.\n";
    echo "Do you want to continue? [y/N]: ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (strtolower(trim($line)) !== 'y') {
        echo "Operation cancelled.\n";
        exit(1);
    }
    fclose($handle);
}

echo "Running. This may take a few minutes.\n\n";

// First pass - discover classes, methods, functions and variables
$spinner->start('Mapping classes');
processDirectory($config['source_directory'], $processor, $spinner);
$spinner->stop('Classes mapped');

$spinner->start('Mapping methods');
$spinner->stop('Methods mapped');

$spinner->start('Mapping functions');
$spinner->stop('Functions mapped');

$spinner->start('Mapping variables');
$spinner->stop('Variables mapped');

// Save the mappings
$spinner->start('Saving mappings');
$mapper->saveNameMaps();
$spinner->stop('Mappings saved');

// Second pass - transform the code using the mappings
$transformer = new CodeTransformer(
    $mapper->getClassMappings(),
    $mapper->getMethodMappings(),
    $mapper->getFunctionMappings(),
    $mapper->getVariableMappings()
);

$spinner->start('Obfuscating files');
transformDirectory($config['source_directory'], $config['destination_directory'], $transformer, $spinner);
$spinner->stop();

echo "\nComplete. Your obfuscated files are in {$config['destination_directory']}\n";

function processDirectory(string $directory, FileProcessor $processor, ProgressSpinner $spinner): void {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $processor->processFile($file->getPathname());
            $spinner->update();
        }
    }
}

function transformDirectory(string $sourceDir, string $destDir, CodeTransformer $transformer, ProgressSpinner $spinner): void {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $relativePath = str_replace($sourceDir, '', $file->getPathname());
            $destPath = $destDir . $relativePath;
            $transformer->transformFile($file->getPathname(), $destPath);
            $spinner->update();
        }
    }
}