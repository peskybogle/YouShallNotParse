<?php

namespace YouShallNotParse\Visitor;

use PhpParser\Node;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeVisitorAbstract;
use YouShallNotParse\NameMapper;

class FileVisitor extends NodeVisitorAbstract {
    private NameMapper $nameMapper;
    private string $currentFile;

    public function __construct(NameMapper $nameMapper) {
        $this->nameMapper = $nameMapper;
    }

    public function setCurrentFile(string $file): void {
        $this->currentFile = $file;
        if ($this->nameMapper->shouldRenameFile($file)) {
            $this->nameMapper->addFileToMap($file);
        }
    }

    public function enterNode(Node $node) {
        if ($node instanceof Include_ && $node->expr instanceof String_) {
            $path = $node->expr->value;
            if ($this->nameMapper->shouldRenameFile($path)) {
                $this->nameMapper->addFileToMap($path);
            }
        }
    }
} 