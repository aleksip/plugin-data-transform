<?php

namespace aleksip\DataTransformPlugin;

use Drupal\Core\Template\Attribute;
use PatternLab\Data;
use PatternLab\PatternData;
use PatternLab\PatternEngine;

class DataTransformer
{
    protected $env;
    protected $reservedKeys;
    protected $patternDataStore;
    protected $processed;

    public function __construct(\Twig_Environment $env)
    {
        $this->env = $env;
        // TODO: Add an accessor function for $reservedKeys to the Data class?
        $this->reservedKeys = array("cacheBuster","link","patternSpecific","patternLabHead","patternLabFoot");
        $this->patternDataStore = PatternData::get();
        $this->processed = array();
    }

    public function run()
    {
        // Process global data.
        $dataStore = $this->processData(Data::get());
        Data::replaceStore($dataStore);
        // Process pattern specific data.
        foreach (array_keys($this->patternDataStore) as $pattern) {
            $this->processPattern($pattern);
        }
    }

    protected function isProcessed($pattern)
    {
        return isset($this->processed[$pattern]);
    }

    protected function setProcessed($pattern)
    {
        $this->processed[$pattern] = true;
    }

    protected function processPattern($pattern)
    {
        if (
            $this->isProcessed($pattern)
            || !isset($this->patternDataStore[$pattern])
            || $this->patternDataStore[$pattern]['category'] != 'pattern'
        ) {
            return;
        }
        $patternSpecificData =
            $this->processData(Data::getPatternSpecificData($pattern))
        ;
        // Clone objects in possible default global data.
        $dataStore = Data::get();
        foreach (array_keys($patternSpecificData) as $key) {
            if (!isset($dataStore['patternSpecific'][$pattern]['data'][$key])) {
                // Value is default global data.
                // TODO: Array support.
                if (is_object($dataStore[$key])) {
                    $patternSpecificData[$key] = clone $dataStore[$key];
                }
            }
        }
        Data::initPattern($pattern);
        Data::setPatternData($pattern, $patternSpecificData);
        $this->setProcessed($pattern);
    }

    protected function processData($data)
    {
        foreach (array_keys($data) as $key) {
            if (!in_array($key, $this->reservedKeys)) {
                $data = $this->processKey($data, $key);
            }
        }

        return $data;
    }

    protected function processKey($data, $key)
    {
        $value = $data[$key];
        if (is_array($value)) {
            foreach (array_keys($value) as $subKey) {
                $value = $this->processKey($value, $subKey);
            }
            if (isset($value['Attribute()']) && is_array($value['Attribute()'])) {
                $data[$key] = new Attribute($value['Attribute()']);
            }
            elseif (isset($value['include()']) && is_array($value['include()']) && isset($value['include()']['pattern'])) {
                $pattern = $value['include()']['pattern'];
                if (is_string($pattern) && isset($this->patternDataStore[$pattern])) {
                    if (!isset($value['include()']['with']) || !is_array($value['include()']['with'])) {
                        if (!isset($value['include()']['only'])) {
                            $patternData = $this->getProcessedPatternSpecificData($pattern);
                        }
                        else {
                            $patternData = array();
                        }
                    }
                    elseif (!isset($value['include()']['only'])) {
                        $patternData = $this->getProcessedPatternSpecificData($pattern, $value['include()']['with']);
                    }
                    else {
                        $patternData = $value['include()']['with'];
                    }
                    $data[$key] = $this->renderPattern($pattern, $patternData);
                }
            }
            elseif (isset($value['join()']) && is_array($value['join()'])) {
                $data[$key] = join($value['join()']);
            }
            else {
                $data[$key] = $value;
            }
        }
        elseif (is_string($value) && isset($this->patternDataStore[$value]) && $key !== 'pattern') {
            $data[$key] = $this->renderPattern($value, $this->getProcessedPatternSpecificData($value));
        }

        return $data;
    }

    public function getProcessedPatternSpecificData($pattern, $extraData = array())
    {
        $this->processPattern($pattern);

        return Data::getPatternSpecificData($pattern, $extraData);
    }

    protected function renderPattern($pattern, $data)
    {
        if (isset($this->patternDataStore[$pattern]['patternRaw'])) {
            $pattern = $this->env->render(
                $this->patternDataStore[$pattern]['patternRaw'],
                $data
            );
        }

        return $pattern;
    }
}
