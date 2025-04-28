<?php

namespace YouShallNotParse\Visitor;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitorAbstract;
use YouShallNotParse\NameMapper;

class VariableVisitor extends NodeVisitorAbstract {
    private NameMapper $nameMapper;
    private array $variableMappings = [];

    public function __construct(NameMapper $nameMapper) {
        $this->nameMapper = $nameMapper;
    }

    public function enterNode(Node $node) {
        if ($node instanceof Variable && is_string($node->name)) {
            // Handle regular variables
            $this->variableMappings[$node->name] = $this->nameMapper->mapVariable($node->name);
        } elseif ($node instanceof Property) {
            // Handle class properties
            foreach ($node->props as $prop) {
                if (is_string($prop->name->name)) {
                    $this->variableMappings[$prop->name->name] = $this->nameMapper->mapVariable($prop->name->name);
                }
            }
        }
    }

    public function getVariableMappings(): array {
        return $this->variableMappings;
    }
} 