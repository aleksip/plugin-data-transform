<?php

namespace aleksip\DataTransformPlugin\Twig;

use aleksip\DataTransformPlugin\DataTransformer;

class PatternDataNodeVisitor extends \Twig_BaseNodeVisitor
{
    /**
     * @var DataTransformer
     */
    protected $dataTransformer;

    public function __construct(DataTransformer $dataTransformer)
    {
        $this->dataTransformer = $dataTransformer;
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

                // Handle Twig namespace includes
                if ($patternStoreKey[0] == '@') {
                    $patternStoreKey = ltrim($patternStoreKey, '@');
                    $lineageParts = explode('/', $patternStoreKey);
                    $length = count($lineageParts);
                    $patternType = $lineageParts[0];

                    $patternName = $lineageParts[$length - 1];
                    $patternName = ltrim($patternName, '_');
                    $patternName = preg_replace('/^[0-9\-]+/', '', $patternName);

                    $patternNameStripped = explode('.twig', $patternName);

                    if (count($patternNameStripped) > 1) {
                        $patternName = $patternNameStripped[0];
                    }
                    $patternName = str_replace('.', '-', $patternName);
                    $patternStoreKey = $patternType . "-" . $patternName;
                }

                $data = $this->dataTransformer->getProcessedPatternSpecificData($patternStoreKey);
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
