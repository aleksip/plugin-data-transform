<?php

namespace aleksip\DataTransformPlugin;

use PatternLab\Listener;
use PatternLab\PatternData\Event;

class PatternLabListener extends Listener
{
    public function __construct()
    {
        $this->addListener('patternData.codeHelperStart', 'runHelper');
    }

    public function runHelper(Event $event)
    {
        $options = $event->getOptions();
        $helper = new Helper($options);
        $helper->run();
    }
}
