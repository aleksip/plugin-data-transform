<?php

namespace aleksip\DataTransformPlugin;

use aleksip\DataTransformPlugin\Twig\PatternDataNodeVisitor;
use aleksip\DataTransformPlugin\Twig\TwigEnvironmentDecorator;
use PatternLab\Config;
use PatternLab\Listener;
use PatternLab\PatternEngine;
use PatternLab\PatternEngine\Twig\TwigUtil;

/**
 * @author Aleksi Peebles <aleksi@iki.fi>
 */
class PatternLabListener extends Listener
{
    /**
     * @var DataTransformer
     */
    protected $dataTransformer;

    public function __construct()
    {
        if (DataTransformPlugin::isEnabled()) {
            $this->addListener('patternData.codeHelperStart', 'dataTransformer');
            $this->addListener('twigPatternLoader.customize', 'addNodeVisitor', -99);
            DataTransformPlugin::writeInfo('listeners added');
        }
    }

    public function dataTransformer()
    {
        $this->dataTransformer = new DataTransformer();

        if (Config::getOption('patternExtension') !== 'twig') {
            $patternEngineBasePath = PatternEngine::getInstance()->getBasePath();
            $patternLoaderClass = $patternEngineBasePath.'\Loaders\PatternLoader';
            $patternLoader = new $patternLoaderClass([]);
            $this->dataTransformer->run(new Renderer($patternLoader));
        }
    }

    public function addNodeVisitor()
    {
        $nodeVisitor = new PatternDataNodeVisitor($this->dataTransformer);

        $env = TwigUtil::getInstance();
        if (DataTransformPlugin::isVerbose()) {
            $env = new TwigEnvironmentDecorator($env);
        }
        $env->addNodeVisitor($nodeVisitor);
        TwigUtil::setInstance($env);

        $this->dataTransformer->run(new Renderer($env));
    }
}
