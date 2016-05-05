<?php

namespace aleksip\DataTransformPlugin\Twig;

use Drupal\Core\Template\Attribute;

trait PatternDataNodeTrait
{
    protected $data;

    public function setData($data)
    {
        if (is_int($data) || is_float($data)) {
            if (false !== $locale = setlocale(LC_NUMERIC, 0)) {
                setlocale(LC_NUMERIC, 'C');
            }

            $this->data .= $data;

            if (false !== $locale) {
                setlocale(LC_NUMERIC, $locale);
            }
        } elseif (null === $data) {
            $this->data .= 'null';
        } elseif (is_bool($data)) {
            $this->data .= ($data ? 'true' : 'false');
        } elseif (is_array($data)) {
            $this->data .= 'array(';
            $first = true;
            foreach ($data as $key => $v) {
                if (!$first) {
                    $this->data .= ', ';
                }
                $first = false;
                $this->setData($key);
                $this->data .= ' => ';
                $this->setData($v);
            }
            $this->data .= ')';
        } elseif ($data instanceof Attribute) {
            $this->data .= 'new \Drupal\Core\Template\Attribute(';
            $this->setData($data->toArray());
            $this->data .= ')';
        } else {
            $this->data .= sprintf('"%s"', addcslashes($data, "\0\t\"\$\\"));
        }
    }

    protected function addTemplateArguments(\Twig_Compiler $compiler)
    {
        if (null === $this->getNode('variables')) {
            if (false === $this->getAttribute('only')) {
                $compiler
                    ->raw('array_merge($context, ')
                    ->raw($this->data)
                    ->raw(')')
                ;
            }
            else {
                $compiler->raw('array()');
            }
        } elseif (false === $this->getAttribute('only')) {
            $compiler
                ->raw('array_merge($context, ')
                ->raw($this->data)
                ->raw(', ')
                ->subcompile($this->getNode('variables'))
                ->raw(')')
            ;
        } else {
            $compiler->subcompile($this->getNode('variables'));
        }
    }
}
