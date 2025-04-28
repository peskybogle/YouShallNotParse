<?php

namespace YouShallNotParse;

use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use YouShallNotParse\Visitor\TransformVisitor;
use YouShallNotParse\Visitor\CommentStripVisitor;
use YouShallNotParse\Visitor\IncludeVisitor;
use YouShallNotParse\NameMapper;

class CodeTransformer {
    private \PhpParser\Parser $parser;
    private NodeTraverser $traverser;
    private Standard $printer;
    private TransformVisitor $transformVisitor;
    private ?CommentStripVisitor $commentStripVisitor;
    private ?IncludeVisitor $includeVisitor;
    private array $classMappings;
    private array $methodMappings;
    private array $functionMappings;
    private array $variableMappings;
    private bool $stripComments;
    private bool $stripWhitespace;
    private bool $stripLinebreaks;
    private NameMapper $nameMapper;

    public function __construct(
        array $classMappings,
        array $methodMappings,
        array $functionMappings,
        array $variableMappings,
        NameMapper $nameMapper
    ) {
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->traverser = new NodeTraverser();
        $this->nameMapper = $nameMapper;

        // Load config to check if we should strip comments, whitespace and linebreaks
        $config = json_decode(file_get_contents('ysnp.config.json'), true);
        $this->stripComments = $config['strip_comments'] ?? false;
        $this->stripWhitespace = $config['strip_whitespace'] ?? false;
        $this->stripLinebreaks = $config['strip_linebreaks'] ?? false;
        
        // Configure printer options based on stripping settings
        $printerOptions = [
            'shortArraySyntax' => true
        ];
        
        if ($this->stripWhitespace || $this->stripLinebreaks) {
            $printerOptions['indent'] = '';
        }
        
        if ($this->stripLinebreaks) {
            $printerOptions['newline'] = '';
        }
        
        $this->printer = new Standard($printerOptions);

        $this->transformVisitor = new TransformVisitor(
            $classMappings,
            $methodMappings,
            $functionMappings,
            $variableMappings
        );
        $this->traverser->addVisitor($this->transformVisitor);
        
        if ($this->stripComments) {
            $this->commentStripVisitor = new CommentStripVisitor();
            $this->traverser->addVisitor($this->commentStripVisitor);
        }

        $this->includeVisitor = new IncludeVisitor($nameMapper);
        $this->traverser->addVisitor($this->includeVisitor);

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
            $transformedCode = $this->printer->prettyPrintFile($ast);

            // Additional cleanup based on stripping settings
            if ($this->stripWhitespace) {
                // Remove extra spaces
                $transformedCode = preg_replace('/\s+/', ' ', $transformedCode);
                // Fix spacing around operators and punctuation
                $transformedCode = preg_replace('/\s*([\{\}\[\]\(\)\=\<\>\+\-\*\/\,\;])\s*/', '$1', $transformedCode);
                // Add space after comma in function calls
                $transformedCode = preg_replace('/,(\S)/', ', $1', $transformedCode);
                // Remove whitespace between PHP closing and opening tags
                $transformedCode = preg_replace('/\?>\s+<\?php/', "?><?php", $transformedCode);
            }

            if ($this->stripLinebreaks) {
                // Remove all linebreaks
                $transformedCode = str_replace(["\r\n", "\r", "\n"], '', $transformedCode);
                // Ensure single space after semicolons for readability in case of errors
                $transformedCode = preg_replace('/;(\S)/', '; $1', $transformedCode);
            }

            return $transformedCode;
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
        if ($code === false) {
            throw new \RuntimeException("Could not read file: {$sourceFile}");
        }

        // Set current file for include visitor
        $this->includeVisitor->setCurrentFile($sourceFile);

        // Transform the code
        $transformedCode = $this->transformCode($code);

        // Get the renamed destination file path
        $renamedDestFile = $this->nameMapper->mapFile($destinationFile);
        
        // Write the transformed code to the renamed file
        file_put_contents($renamedDestFile, $transformedCode);
    }

    public function shouldIgnoreFile(string $path): bool {
        return $this->nameMapper->shouldIgnoreFile($path);
    }
} 