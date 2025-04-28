<?php

namespace YouShallNotParse\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Comment;

class CommentStripVisitor extends NodeVisitorAbstract {
    public function enterNode(Node $node) {
        $node->setAttribute('comments', []);
        return $node;
    }
} 