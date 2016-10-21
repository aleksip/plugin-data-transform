<?php

namespace aleksip\DataTransformPlugin;

use aleksip\DataTransformPlugin\Twig\PatternDataNodeVisitor;
use PatternLab\Listener;
use PatternLab\PatternEngine\Twig\TwigUtil;

class PatternLabListener extends Listener
{
    protected $dt;
    protected $nv;

    public function __construct()
    {
        $this->addListener(
            'twigPatternLoader.customize',
            'twigPatternLoaderCustomize'
        );
    }

    public function twigPatternLoaderCustomize()
    {
        $this->dt ?: $this->dt = new DataTransformer();
        $this->nv ?: $this->nv = new PatternDataNodeVisitor($this->dt);

        $env = TwigUtil::getInstance();
        $env->addNodeVisitor($this->nv);
        TwigUtil::setInstance($env);
        $this->dt->run($env);
    }
}
