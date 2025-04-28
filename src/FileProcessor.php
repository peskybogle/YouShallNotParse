<?php

namespace YouShallNotParse;

use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use YouShallNotParse\Visitor\ClassVisitor;
use YouShallNotParse\Visitor\FunctionVisitor;
use YouShallNotParse\Visitor\VariableVisitor;

class FileProcessor {
    private NameMapper $nameMapper;
    private \PhpParser\Parser $parser;
    private NodeTraverser $traverser;
    private ClassVisitor $classVisitor;
    private FunctionVisitor $functionVisitor;
    private VariableVisitor $variableVisitor;
    private array $classMappings = [];
    private array $methodMappings = [];
    private array $functionMappings = [];
    private array $variableMappings = [];

    public function __construct(NameMapper $nameMapper) {
        $this->nameMapper = $nameMapper;
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->traverser = new NodeTraverser();
        $this->classVisitor = new ClassVisitor($nameMapper);
        $this->functionVisitor = new FunctionVisitor($nameMapper);
        $this->variableVisitor = new VariableVisitor($nameMapper);
        $this->traverser->addVisitor($this->classVisitor);
        $this->traverser->addVisitor($this->functionVisitor);
        $this->traverser->addVisitor($this->variableVisitor);
    }

    public function processFile(string $filePath): void {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File not found: {$filePath}");
        }

        if ($this->nameMapper->shouldIgnoreFile($filePath)) {
            return;
        }

        $code = file_get_contents($filePath);
        if ($code === false) {
            throw new \RuntimeException("Could not read file: {$filePath}");
        }

        try {
            // Only process PHP code within PHP tags
            if (strpos($code, '<?php') !== false) {
                $ast = $this->parser->parse($code);
                if ($ast !== null) {
                    $this->traverser->traverse($ast);
                    
                    // Collect mappings after processing each file
                    $this->classMappings = array_merge($this->classMappings, $this->classVisitor->getClassMappings());
                    $this->methodMappings = array_merge($this->methodMappings, $this->classVisitor->getMethodMappings());
                    $this->functionMappings = array_merge($this->functionMappings, $this->functionVisitor->getFunctionMappings());
                    $this->variableMappings = array_merge($this->variableMappings, $this->variableVisitor->getVariableMappings());
                }
            }
        } catch (Error $error) {
            throw new \RuntimeException("Parse error in {$filePath}: " . $error->getMessage());
        }
    }

    public function getDiscoveredClasses(): array {
        return array_keys($this->classMappings);
    }

    public function getDiscoveredMethods(): array {
        return array_keys($this->methodMappings);
    }

    public function getDiscoveredFunctions(): array {
        return array_keys($this->functionMappings);
    }

    public function getDiscoveredVariables(): array {
        return array_keys($this->variableMappings);
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