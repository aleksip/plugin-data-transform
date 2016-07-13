<?php

namespace aleksip\DataTransformPlugin;

use aleksip\DataTransformPlugin\Twig\PatternDataNodeVisitor;
use PatternLab\Listener;
use PatternLab\PatternEngine\Twig\TwigUtil;

class PatternLabListener extends Listener
{
    public function __construct()
    {
        $this->addListener(
            'twigPatternLoader.customize',
            'twigPatternLoaderCustomize'
        );
    }

    public function twigPatternLoaderCustomize()
    {
        $env = TwigUtil::getInstance();
        $dt = new DataTransformer($env);
        $env->addNodeVisitor(new PatternDataNodeVisitor($dt));
        TwigUtil::setInstance($env);
        $dt->run();
    }
}
