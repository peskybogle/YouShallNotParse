<?php

namespace YouShallNotParse;

use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use YouShallNotParse\Visitor\TransformVisitor;

class CodeTransformer {
    private \PhpParser\Parser $parser;
    private NodeTraverser $traverser;
    private Standard $printer;
    private TransformVisitor $transformVisitor;
    private array $classMappings;
    private array $methodMappings;
    private array $functionMappings;
    private array $variableMappings;

    public function __construct(
        array $classMappings,
        array $methodMappings,
        array $functionMappings,
        array $variableMappings
    ) {
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->traverser = new NodeTraverser();
        $this->printer = new Standard();
        $this->transformVisitor = new TransformVisitor(
            $classMappings,
            $methodMappings,
            $functionMappings,
            $variableMappings
        );
        $this->traverser->addVisitor($this->transformVisitor);
        $this->classMappings = $classMappings;
        $this->methodMappings = $methodMappings;
        $this->functionMappings = $functionMappings;
        $this->variableMappings = $variableMappings;
    }

    public function transformCode(string $code): string {
        try {
            // Only transform PHP code within PHP tags
            if (strpos($code, '<?php') === false) {
                return $code;
            }

            $ast = $this->parser->parse($code);
            if ($ast === null) {
                return $code;
            }

            $ast = $this->traverser->traverse($ast);
            return $this->printer->prettyPrintFile($ast);
        } catch (Error $error) {
            throw new \RuntimeException("Parse error: " . $error->getMessage());
        }
    }

    public function transformFile(string $sourceFile, string $destinationFile): void {
        // Create the destination directory if it doesn't exist
        $destDir = dirname($destinationFile);
        if (!is_dir($destDir)) {
            mkdir($destDir, 0777, true);
        }

        $code = file_get_contents($sourceFile);
        
        // Load safety list and skip lists
        $safetyList = json_decode(file_get_contents('ysnp.safety.json'), true);
        $config = json_decode(file_get_contents('ysnp.config.json'), true);
        $safeVariables = array_map('strtolower', $safetyList['variables'] ?? []);
        $skipClasses = array_map('strtolower', $config['skip_classes'] ?? []);
        $safeClasses = array_map('strtolower', $safetyList['classes'] ?? []);
        $safeFunctions = array_map('strtolower', $safetyList['functions'] ?? []);
        $skipMethods = array_map('strtolower', $config['skip_methods'] ?? []);
        
        // Case-insensitive variable name replacements
        foreach ($this->variableMappings as $original => $obfuscated) {
            $lowerOriginal = strtolower($original);
            // Skip if variable is in safety list
            if (!in_array($lowerOriginal, $safeVariables)) {
                // Replace standard variables ($varname)
                $code = preg_replace('/\$' . preg_quote($original, '/') . '\b/i', '$' . $obfuscated, $code);
                
                // Replace property references ($this->varname and $obj->varname)
                $code = preg_replace('/->\s*' . preg_quote($original, '/') . '\b/i', '->' . $obfuscated, $code);
                
                // Replace property declarations (public $varname)
                $code = preg_replace('/public\s+\$' . preg_quote($original, '/') . '\b/i', 'public $' . $obfuscated, $code);
                
                // Replace property assignments ($this->varname =)
                $code = preg_replace('/\$this->' . preg_quote($original, '/') . '\b/i', '$this->' . $obfuscated, $code);
            }
        }

        // Case-insensitive function name replacements
        foreach ($this->functionMappings as $original => $obfuscated) {
            $lowerOriginal = strtolower($original);
            // Match "function name" pattern
            $code = preg_replace('/function\s+' . preg_quote($lowerOriginal, '/') . '\b/i', 'function ' . $obfuscated, $code);
            // Match "name(" pattern for function calls
            $code = preg_replace('/\b' . preg_quote($lowerOriginal, '/') . '\s*\(/i', $obfuscated . '(', $code);
        }

        // Case-insensitive method name replacements
        foreach ($this->methodMappings as $original => $obfuscated) {
            $lowerOriginal = strtolower($original);
            // Skip if method is in safety list or skip list
            if (!in_array($lowerOriginal, $safeFunctions) && !in_array($lowerOriginal, $skipMethods)) {
                // Match "function name" pattern in class
                $code = preg_replace('/function\s+' . preg_quote($lowerOriginal, '/') . '\b/i', 'function ' . $obfuscated, $code);
                // Match "->name(" pattern for method calls
                $code = preg_replace('/->\s*' . preg_quote($lowerOriginal, '/') . '\s*\(/i', '->' . $obfuscated . '(', $code);
            }
        }

        // Case-insensitive class name replacements
        foreach ($this->classMappings as $original => $obfuscated) {
            $lowerOriginal = strtolower($original);
            // Skip if class is in safety list or skip list
            if (!in_array($lowerOriginal, $skipClasses) && !in_array($lowerOriginal, $safeClasses)) {
                // Match "class Name" pattern
                $code = preg_replace('/class\s+' . preg_quote($lowerOriginal, '/') . '\b/i', 'class ' . $obfuscated, $code);
                // Match "new Name" pattern
                $code = preg_replace('/new\s+' . preg_quote($lowerOriginal, '/') . '\b/i', 'new ' . $obfuscated, $code);
                // Match type hints and extends/implements
                $code = preg_replace('/:\s*' . preg_quote($lowerOriginal, '/') . '\b/i', ': ' . $obfuscated, $code);
                $code = preg_replace('/extends\s+' . preg_quote($lowerOriginal, '/') . '\b/i', 'extends ' . $obfuscated, $code);
                $code = preg_replace('/implements\s+' . preg_quote($lowerOriginal, '/') . '\b/i', 'implements ' . $obfuscated, $code);
            }
        }

        file_put_contents($destinationFile, $code);
    }
} 