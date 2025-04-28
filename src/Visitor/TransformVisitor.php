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
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\Assign;
use PhpParser\NodeVisitorAbstract;

class TransformVisitor extends NodeVisitorAbstract {
    private array $variableMappings;
    private array $safeVariables = ['this'];

    public function __construct(
        array $classMappings,
        array $methodMappings,
        array $functionMappings,
        array $variableMappings
    ) {
        $this->variableMappings = $variableMappings;
    }

    private function shouldTransformVariable(string $name): bool {
        return !in_array(strtolower($name), $this->safeVariables);
    }

    public function enterNode(Node $node) {
        if ($node instanceof Variable && is_string($node->name)) {
            if ($this->shouldTransformVariable($node->name) && isset($this->variableMappings[$node->name])) {
                $node->name = $this->variableMappings[$node->name];
            }
        }
        return null;
    }

    public function leaveNode(Node $node) {
        if ($node instanceof Variable && is_string($node->name)) {
            if ($this->shouldTransformVariable($node->name) && isset($this->variableMappings[$node->name])) {
                $node->name = $this->variableMappings[$node->name];
            }
        }
        return $node;
    }
} 