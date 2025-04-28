<?php

namespace YouShallNotParse\Visitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;
use YouShallNotParse\NameMapper;

class ClassVisitor extends NodeVisitorAbstract {
    private NameMapper $nameMapper;
    private array $classMappings = [];
    private array $methodMappings = [];

    public function __construct(NameMapper $nameMapper) {
        $this->nameMapper = $nameMapper;
    }

    public function enterNode(Node $node) {
        if ($node instanceof Class_ && $node->name !== null) {
            $className = $node->name->toString();
            $this->classMappings[$className] = $this->nameMapper->mapClass($className);
        } elseif ($node instanceof ClassMethod && $node->name !== null) {
            $methodName = $node->name->toString();
            $this->methodMappings[$methodName] = $this->nameMapper->mapMethod($methodName);
        }
    }

    public function getClassMappings(): array {
        return $this->classMappings;
    }

    public function getMethodMappings(): array {
        return $this->methodMappings;
    }
} 