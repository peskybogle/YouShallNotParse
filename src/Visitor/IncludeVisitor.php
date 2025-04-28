<?php

namespace YouShallNotParse\Visitor;

use PhpParser\Node;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeVisitorAbstract;
use YouShallNotParse\NameMapper;

class IncludeVisitor extends NodeVisitorAbstract {
    private NameMapper $nameMapper;
    private string $currentFile;

    public function __construct(NameMapper $nameMapper) {
        $this->nameMapper = $nameMapper;
    }

    public function setCurrentFile(string $file): void {
        $this->currentFile = $file;
    }

    public function enterNode(Node $node) {
        if ($node instanceof Include_) {
            if ($node->expr instanceof String_) {
                $originalPath = $node->expr->value;
                
                // Special handling for positions-function.php
                if (strpos($originalPath, 'positions-function.php') !== false) {
                    $mappings = $this->nameMapper->getFileMappings();
                    if (isset($mappings['positions-function.php'])) {
                        $newPath = str_replace(
                            'positions-function.php', 
                            $mappings['positions-function.php'], 
                            $originalPath
                        );
                        $node->expr = new String_($newPath);
                        return;
                    }
                }
                
                // Handle other files
                if (preg_match('/([^\/]+\.php)[\'"]?$/', $originalPath, $matches)) {
                    $filename = trim($matches[1], "'\"");
                    $mappings = $this->nameMapper->getFileMappings();
                    
                    if (isset($mappings[$filename])) {
                        $directory = dirname($originalPath);
                        $newPath = ($directory === '.' ? $mappings[$filename] : $directory . '/' . $mappings[$filename]);
                        
                        if (strpos($originalPath, '__DIR__') !== false) {
                            if (preg_match('/(.*\.php)\'?$/', $originalPath, $matches)) {
                                $prefix = substr($originalPath, 0, strlen($originalPath) - strlen($matches[1]));
                                $newPath = $prefix . $mappings[$filename];
                            }
                        }
                        
                        $node->expr = new String_($newPath);
                    }
                }
            } else if ($node->expr instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
                if ($node->expr->right instanceof String_) {
                    $rightStr = $node->expr->right->value;
                    if (strpos($rightStr, 'positions-function.php') !== false) {
                        $mappings = $this->nameMapper->getFileMappings();
                        if (isset($mappings['positions-function.php'])) {
                            $newRightStr = str_replace(
                                'positions-function.php',
                                $mappings['positions-function.php'],
                                $rightStr
                            );
                            $node->expr->right = new String_($newRightStr);
                        }
                    }
                }
            }
        }
    }
} 