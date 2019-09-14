<?php

namespace aleksip\DataTransformPlugin;

use aleksip\DataTransformPlugin\Twig\TwigEnvironmentDecorator;

/**
 * @author Aleksi Peebles <aleksi@iki.fi>
 */
class Renderer
{
    protected $renderer;

    public function __construct($renderer)
    {
        $this->renderer = $renderer;
    }

    public function render($pattern, $data = array())
    {
        if ($this->renderer instanceof \Twig_Environment || $this->renderer instanceof TwigEnvironmentDecorator) {
            return $this->renderer->render($pattern, $data);
        }
        else {
            return $this->renderer->render(array(
                'pattern' => $pattern,
                'data' => $data,
            ));
        }
    }
}
