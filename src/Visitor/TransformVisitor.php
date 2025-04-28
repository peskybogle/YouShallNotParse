<?php

namespace YouShallNotParse\Visitor;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Param;
use PhpParser\NodeVisitorAbstract;

class TransformVisitor extends NodeVisitorAbstract {
    private array $classMappings;
    private array $methodMappings;
    private array $functionMappings;
    private array $variableMappings;
    private array $safeVariables = ['this'];

    public function __construct(
        array $classMappings,
        array $methodMappings,
        array $functionMappings,
        array $variableMappings
    ) {
        $this->classMappings = $classMappings;
        $this->methodMappings = $methodMappings;
        $this->functionMappings = $functionMappings;
        $this->variableMappings = $variableMappings;
    }

    private function shouldTransformVariable(string $name): bool {
        return !in_array(strtolower($name), $this->safeVariables);
    }

    public function enterNode(Node $node) {
        // Load safety list and skip lists
        $safetyList = json_decode(file_get_contents('ysnp.safety.json'), true);
        $config = json_decode(file_get_contents('ysnp.config.json'), true);
        $safeVariables = array_map('strtolower', $safetyList['variables'] ?? []);
        $skipClasses = array_map('strtolower', $config['skip_classes'] ?? []);
        $safeClasses = array_map('strtolower', $safetyList['classes'] ?? []);
        $safeFunctions = array_map('strtolower', $safetyList['functions'] ?? []);
        $skipMethods = array_map('strtolower', $config['skip_methods'] ?? []);

        if ($node instanceof Variable && is_string($node->name)) {
            if ($this->shouldTransformVariable($node->name) && isset($this->variableMappings[$node->name])) {
                $node->name = $this->variableMappings[$node->name];
            }
        } elseif ($node instanceof PropertyFetch) {
            if ($node->name instanceof Identifier && isset($this->variableMappings[$node->name->name])) {
                $node->name = new Identifier($this->variableMappings[$node->name->name]);
            }
        } elseif ($node instanceof Property) {
            foreach ($node->props as $prop) {
                if ($prop->name instanceof Identifier && isset($this->variableMappings[$prop->name->name])) {
                    $prop->name = new Identifier($this->variableMappings[$prop->name->name]);
                }
            }
        } elseif ($node instanceof Function_) {
            $name = (string)$node->name;
            $lowerName = strtolower($name);
            if (isset($this->functionMappings[$lowerName])) {
                $node->name = new Identifier($this->functionMappings[$lowerName]);
            }
        } elseif ($node instanceof FuncCall && $node->name instanceof Name) {
            $name = $node->name->toString();
            $lowerName = strtolower($name);
            if (isset($this->functionMappings[$lowerName])) {
                $node->name = new Name($this->functionMappings[$lowerName]);
            }
        } elseif ($node instanceof ClassMethod) {
            $name = (string)$node->name;
            $lowerName = strtolower($name);
            if (!in_array($lowerName, $safeFunctions) && !in_array($lowerName, $skipMethods) && isset($this->methodMappings[$lowerName])) {
                $node->name = new Identifier($this->methodMappings[$lowerName]);
            }
        } elseif ($node instanceof MethodCall) {
            if ($node->name instanceof Identifier && isset($this->methodMappings[strtolower($node->name->name)])) {
                $lowerName = strtolower($node->name->name);
                if (!in_array($lowerName, $safeFunctions) && !in_array($lowerName, $skipMethods)) {
                    $node->name = new Identifier($this->methodMappings[$lowerName]);
                }
            }
        } elseif ($node instanceof Class_) {
            if ($node->name !== null) {
                $name = (string)$node->name;
                $lowerName = strtolower($name);
                if (!in_array($lowerName, $skipClasses) && !in_array($lowerName, $safeClasses) && isset($this->classMappings[$lowerName])) {
                    $node->name = new Identifier($this->classMappings[$lowerName]);
                }
            }
        } elseif ($node instanceof New_) {
            if ($node->class instanceof Name) {
                $name = $node->class->toString();
                $lowerName = strtolower($name);
                if (!in_array($lowerName, $skipClasses) && !in_array($lowerName, $safeClasses) && isset($this->classMappings[$lowerName])) {
                    $node->class = new Name($this->classMappings[$lowerName]);
                }
            }
        } elseif ($node instanceof Param) {
            if ($node->type instanceof Name) {
                $name = $node->type->toString();
                $lowerName = strtolower($name);
                if (!in_array($lowerName, $skipClasses) && !in_array($lowerName, $safeClasses) && isset($this->classMappings[$lowerName])) {
                    $node->type = new Name($this->classMappings[$lowerName]);
                }
            }
        }

        return null;
    }
} 