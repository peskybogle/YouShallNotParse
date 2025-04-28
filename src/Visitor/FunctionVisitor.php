<?php

namespace YouShallNotParse\Visitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeVisitorAbstract;
use YouShallNotParse\NameMapper;

class FunctionVisitor extends NodeVisitorAbstract {
    private NameMapper $nameMapper;
    private array $functionMappings = [];

    public function __construct(NameMapper $nameMapper) {
        $this->nameMapper = $nameMapper;
    }

    public function enterNode(Node $node) {
        if ($node instanceof Function_ && $node->name !== null) {
            $functionName = $node->name->toString();
            $lowerName = strtolower($functionName);
            if (!isset($this->functionMappings[$lowerName])) {
                $this->functionMappings[$lowerName] = $this->nameMapper->mapFunction($functionName);
            }
        }
    }

    public function getFunctionMappings(): array {
        return $this->functionMappings;
    }
} 