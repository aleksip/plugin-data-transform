<?php

namespace aleksip\DataTransformPlugin\Twig;

use aleksip\DataTransformPlugin\DataTransformer;

class PatternDataNodeVisitor extends \Twig_BaseNodeVisitor
{
    protected $dt;

    public function __construct(DataTransformer $dt)
    {
        $this->dt = $dt;
    }

    protected function doEnterNode(\Twig_Node $node, \Twig_Environment $env)
    {
        return $node;
    }

    protected function doLeaveNode(\Twig_Node $node, \Twig_Environment $env)
    {
        if ($node instanceof \Twig_Node_Include) {
            if ($node->hasNode('expr') && $node->getNode('expr')->hasAttribute('value')) {
                $patternStoreKey = $node->getNode('expr')->getAttribute('value');
                $data = $this->dt->getProcessedPatternSpecificData($patternStoreKey);
                if ($node instanceof \Twig_Node_Embed) {
                    $dataNode = new PatternDataEmbedNode($node, $data);
                }
                else {
                    $dataNode = new PatternDataIncludeNode($node, $data);
                }

                return $dataNode;
            }
        }

        return $node;
    }

    public function getPriority()
    {
        return 0;
    }
}
