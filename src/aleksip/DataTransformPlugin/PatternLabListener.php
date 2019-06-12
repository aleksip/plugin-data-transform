<?php

namespace aleksip\DataTransformPlugin;

use aleksip\DataTransformPlugin\Twig\PatternDataNodeVisitor;
use PatternLab\Config;
use PatternLab\Console;
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
        $this->addListener('patternData.codeHelperStart', 'dataTransformer');
        $this->addListener('twigPatternLoader.customize', 'addNodeVisitor', -99);
        if ($this->isVerbose()) {
            Console::writeLine('[data transform plugin] listeners added...');
        }
    }

    public function dataTransformer()
    {
        if (!$this->isEnabled()) {
            return;
        }

        $this->dataTransformer = new DataTransformer($this->isVerbose());

        if (Config::getOption('patternExtension') !== 'twig') {
            $patternEngineBasePath = PatternEngine::getInstance()->getBasePath();
            $patternLoaderClass = $patternEngineBasePath.'\Loaders\PatternLoader';
            $patternLoader = new $patternLoaderClass(array());
            $this->dataTransformer->run(new Renderer($patternLoader));
        }
    }

    public function addNodeVisitor()
    {
        if (!$this->isEnabled()) {
            return;
        }

        $nodeVisitor = new PatternDataNodeVisitor($this->dataTransformer);

        $env = TwigUtil::getInstance();
        $env->addNodeVisitor($nodeVisitor);
        TwigUtil::setInstance($env);

        $this->dataTransformer->run(new Renderer($env));
    }

    protected function isEnabled()
    {
        $enabled = Config::getOption('plugins.dataTransform.enabled');
        $enabled = (is_null($enabled) || (bool)$enabled);

        if ($this->isVerbose() && !$enabled) {
            Console::writeLine('[data transform plugin] plugin is disabled...');
        }

        return $enabled;
    }

    protected function isVerbose()
    {
        $verbose = Config::getOption('plugins.dataTransform.verbose');

        return (!is_null($verbose) && (bool)$verbose);
    }
}
