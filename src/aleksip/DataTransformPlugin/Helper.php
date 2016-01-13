<?php

namespace aleksip\DataTransformPlugin;

use Drupal\Core\Template\Attribute;
use PatternLab\Data;
use PatternLab\PatternData;
use PatternLab\PatternData\Helper as PatternDataHelper;
use PatternLab\PatternEngine;

class Helper extends PatternDataHelper
{
    protected $patternLoader;
    protected $store;
    protected $reservedKeys;
    protected $processed;

    public function __construct($options = array())
    {
        parent::__construct($options);
        $patternEngineBasePath = PatternEngine::getInstance()->getBasePath();
        $patternLoaderClass = $patternEngineBasePath . '\Loaders\PatternLoader';
        $this->patternLoader = new $patternLoaderClass($options);
        $this->store = PatternData::get();
        // TODO: Add an accessor function for $reservedKeys to the Data class?
        $this->reservedKeys = array("listItems","cacheBuster","patternLink","patternSpecific","patternLabHead","patternLabFoot");
        $this->processed = array();
    }

    public function run()
    {
        foreach (array_keys($this->store) as $patternStoreKey) {
            $this->processPattern($patternStoreKey);
        }
    }

    protected function isProcessed($patternStoreKey)
    {
        return isset($this->processed[$patternStoreKey]);
    }

    protected function setProcessed($patternStoreKey)
    {
        $this->processed[$patternStoreKey] = true;
    }

    protected function processPattern($patternStoreKey)
    {
        $patternStoreData = $this->store[$patternStoreKey];
        if ($patternStoreData["category"] == "pattern" && !$this->isProcessed($patternStoreKey)) {
            $data = Data::getPatternSpecificData($patternStoreKey);
            foreach (array_keys($data) as $key) {
                if (!in_array($key, $this->reservedKeys)) {
                    $data = $this->processKey($data, $key);
                }
            }
            Data::setPatternData($patternStoreKey, $data);
            $this->setProcessed($patternStoreKey);
        }
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
                if (is_string($pattern) && isset($this->store[$pattern])) {
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
        elseif (is_string($value) && isset($this->store[$value]) && $key !== 'pattern') {
            $data[$key] = $this->renderPattern($value, $this->getProcessedPatternSpecificData($value));
        }

        return $data;
    }

    protected function getProcessedPatternSpecificData($pattern, $extraData = array())
    {
        $this->processPattern($pattern);

        return Data::getPatternSpecificData($pattern, $extraData);
    }

    protected function renderPattern($pattern, $data)
    {
        if (isset($this->store[$pattern]['patternRaw'])) {
            $pattern = $this->patternLoader->render([
                'pattern' => $this->store[$pattern]['patternRaw'],
                'data' => $data
            ]);
        }

        return $pattern;
    }
}
