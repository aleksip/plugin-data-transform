<?php

namespace aleksip\DataTransformPlugin\Twig;

class PatternDataEmbedNode extends \Twig_Node_Embed
{
    use PatternDataNodeTrait;

    public function __construct(\Twig_Node_Embed $originalNode, $data)
    {
        $variables = $originalNode->hasNode('variables')
            ? $originalNode->getNode('variables')
            : null
        ;
        parent::__construct(
          $originalNode->getAttribute('filename'),
          $originalNode->getAttribute('index'),
          $variables,
          $originalNode->getAttribute('only'),
          $originalNode->getAttribute('ignore_missing'),
          $originalNode->getLine(),
          $originalNode->getNodeTag()
        );

        $this->setData($data);
    }
}
