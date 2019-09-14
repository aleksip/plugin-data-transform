<?php

namespace aleksip\DataTransformPlugin\Twig;

use aleksip\DataTransformPlugin\ErrorHandlerTrait;

class TwigEnvironmentDecorator
{
    use ErrorHandlerTrait;

    protected $environment;
    protected $errno;
    protected $errstr;

    public function __construct(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    public function addGlobal($name, $value)
    {
        $this->environment->addGlobal($name, $value);
    }

    public function render($name, array $context = [])
    {
        $this->setErrorHandler();
        $this->environment->render($name, $context);
        $this->restoreErrorHandler();
    }

    public function __call($method, $args)
    {
        if ($method == 'errorHandler') {
            return $this->errorHandler(...$args);
        }
        else {
            return call_user_func_array([$this->environment, $method], $args);
        }
    }
}
