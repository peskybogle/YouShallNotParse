<?php

namespace YouShallNotParse;

use RuntimeException;

class NameMapper {
    private array $config;
    private array $safetyList;
    private array $variableMappings = [];
    private array $functionMappings = [];
    private array $methodMappings = [];
    private array $classMappings = [];
    private array $fileMappings = [];
    private array $usedNames = [];

    public function __construct(string $configPath, string $safetyPath) {
        if (!file_exists($configPath)) {
            throw new RuntimeException("Config file not found: {$configPath}");
        }
        if (!file_exists($safetyPath)) {
            throw new RuntimeException("Safety file not found: {$safetyPath}");
        }

        $this->config = json_decode(file_get_contents($configPath), true);
        $this->safetyList = json_decode(file_get_contents($safetyPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Invalid JSON in configuration files");
        }

        $this->validateConfig();
    }

    private function validateConfig(): void {
        $requiredFields = ['source_directory', 'destination_directory'];
        foreach ($requiredFields as $field) {
            if (!isset($this->config[$field])) {
                throw new RuntimeException("Missing required config field: {$field}");
            }
        }

        // Convert all skip/ignore lists to lowercase for case-insensitive matching
        $this->config['skip_variables'] = array_map('strtolower', $this->config['skip_variables'] ?? []);
        $this->config['skip_functions'] = array_map('strtolower', $this->config['skip_functions'] ?? []);
        $this->config['skip_methods'] = array_map('strtolower', $this->config['skip_methods'] ?? []);
        $this->config['skip_classes'] = array_map('strtolower', $this->config['skip_classes'] ?? []);
        $this->config['skip_rename_files'] = array_map('strtolower', $this->config['skip_rename_files'] ?? []);
        $this->config['ignore_files'] = array_map('strtolower', $this->config['ignore_files'] ?? []);
        $this->config['ignore_directories'] = array_map('strtolower', $this->config['ignore_directories'] ?? []);
    }

    public function shouldIgnoreFile(string $path): bool {
        $path = strtolower($path);
        
        // Check if file is in ignored directory
        foreach ($this->config['ignore_directories'] ?? [] as $dir) {
            if (strpos($path, strtolower($dir) . DIRECTORY_SEPARATOR) !== false) {
                return true;
            }
        }

        // Check if file is explicitly ignored
        return in_array(basename($path), $this->config['ignore_files'] ?? []);
    }

    public function shouldRenameFile(string $path): bool {
        if (!($this->config['rename_files'] ?? false)) {
            return false;
        }

        $path = strtolower($path);
        return !in_array(basename($path), $this->config['skip_rename_files'] ?? []);
    }

    private function generateUniqueName(string $prefix): string {
        do {
            $name = $prefix . '_' . bin2hex(random_bytes(8));
        } while (isset($this->usedNames[$name]));

        $this->usedNames[$name] = true;
        return $name;
    }

    private function isInSafetyList(string $name, string $type): bool {
        $name = strtolower($name);
        return in_array($name, array_map('strtolower', $this->safetyList[$type] ?? []));
    }

    private function shouldSkip(string $name, string $type): bool {
        $name = strtolower($name);
        $skipList = array_map('strtolower', $this->config["skip_{$type}s"] ?? []);
        return $this->isInSafetyList($name, $type) || 
               in_array($name, $skipList);
    }

    public function mapVariable(string $name): string {
        $lowerName = strtolower($name);
        
        if ($this->shouldSkip($name, 'variable')) {
            return $name;
        }

        if (!isset($this->variableMappings[$lowerName])) {
            $this->variableMappings[$lowerName] = $this->generateUniqueName('v');
        }

        return $this->variableMappings[$lowerName];
    }

    public function mapFunction(string $name): string {
        $lowerName = strtolower($name);
        
        if ($this->shouldSkip($name, 'function')) {
            return $name;
        }

        if (!isset($this->functionMappings[$lowerName])) {
            $this->functionMappings[$lowerName] = $this->generateUniqueName('fn');
        }

        return $this->functionMappings[$lowerName];
    }

    public function mapMethod(string $name): string {
        $lowerName = strtolower($name);
        
        if ($this->shouldSkip($name, 'method')) {
            return $name;
        }

        if (!isset($this->methodMappings[$lowerName])) {
            $this->methodMappings[$lowerName] = $this->generateUniqueName('m');
        }

        return $this->methodMappings[$lowerName];
    }

    public function mapClass(string $name): string {
        $lowerName = strtolower($name);
        
        if ($this->shouldSkip($name, 'class')) {
            return $name;
        }

        if (!isset($this->classMappings[$lowerName])) {
            $this->classMappings[$lowerName] = $this->generateUniqueName('c');
        }

        return $this->classMappings[$lowerName];
    }

    public function mapFile(string $path): string {
        if (!$this->shouldRenameFile($path)) {
            return $path;
        }

        $directory = dirname($path);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $baseName = strtolower(basename($path, ".$extension"));

        if (!isset($this->fileMappings[$baseName])) {
            $this->fileMappings[$baseName] = $this->generateUniqueName('file');
        }

        return $directory . DIRECTORY_SEPARATOR . $this->fileMappings[$baseName] . ".$extension";
    }

    public function saveNameMaps(): void {
        $maps = [
            'variable_name_map.json' => $this->variableMappings,
            'function_name_map.json' => $this->functionMappings,
            'method_name_map.json' => $this->methodMappings,
            'class_name_map.json' => $this->classMappings,
            'file_name_map.json' => $this->fileMappings
        ];

        foreach ($maps as $file => $map) {
            if (!empty($map)) {
                file_put_contents($file, json_encode($map, JSON_PRETTY_PRINT));
            }
        }
    }

    public function getClassMappings(): array {
        return $this->classMappings;
    }

    public function getMethodMappings(): array {
        return $this->methodMappings;
    }

    public function getFunctionMappings(): array {
        return $this->functionMappings;
    }

    public function getVariableMappings(): array {
        return $this->variableMappings;
    }
} 