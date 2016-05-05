<?php

namespace aleksip\DataTransformPlugin;

use aleksip\DataTransformPlugin\Twig\PatternDataNodeVisitor;
use PatternLab\Listener;
use PatternLab\PatternData\Event;
use PatternLab\PatternEngine\Twig\TwigUtil;

class PatternLabListener extends Listener
{
    public function __construct()
    {
        $this->addListener('patternData.codeHelperStart', 'runHelper');
        $this->addListener('twigPatternLoader.customize', 'addNodeVisitor');
    }

    public function runHelper(Event $event)
    {
        $options = $event->getOptions();
        $helper = new Helper($options);
        $helper->run();
    }

    public function addNodeVisitor()
    {
        $instance = TwigUtil::getInstance();
        $instance->addNodeVisitor(new PatternDataNodeVisitor());
        TwigUtil::setInstance($instance);
    }
}
