<?php

namespace aleksip\DataTransformPlugin;

use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use PatternLab\Data;
use PatternLab\PatternData;

/**
 * @author Aleksi Peebles <aleksi@iki.fi>
 */
class DataTransformer
{
    use ErrorHandlerTrait;

    protected static $processed = array();

    protected $reservedKeys;
    protected $patternDataStore;
    protected $renderer;
    protected $hasRun;
    protected $currentPattern;

    public function __construct()
    {
        // TODO: Add an accessor function for $reservedKeys to the Data class?
        $this->reservedKeys = array("cacheBuster","link","patternSpecific","patternLabHead","patternLabFoot");
        $this->patternDataStore = PatternData::get();
    }

    public function run(Renderer $renderer)
    {
        if ($this->hasRun) {
            return;
        }
        $this->renderer = $renderer;
        // Process global data.
        DataTransformPlugin::writeInfo('processing global data');
        $dataStore = $this->processData(Data::get());
        Data::replaceStore($dataStore);
        // Process pattern specific data.
        DataTransformPlugin::writeInfo('processing pattern specific data');
        foreach (array_keys($this->patternDataStore) as $pattern) {
            $this->currentPattern = $pattern;
            $this->processPattern($pattern);
        }
        $this->hasRun = true;
        DataTransformPlugin::writeInfo('processing done');
    }

    protected function isProcessed($pattern)
    {
        return isset(self::$processed[$pattern]);
    }

    protected function setProcessed($pattern)
    {
        self::$processed[$pattern] = true;
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
        $this->setProcessed($pattern);
        DataTransformPlugin::writeInfo("processing pattern '$pattern'");
        $patternSpecificData =
          $this->processData(Data::getPatternSpecificData($pattern));


        $dataStore = Data::get();
        foreach (array_keys($patternSpecificData) as $key) {
          if (!isset($dataStore['patternSpecific'][$pattern]['data'][$key])) {
            // Value is default global data.
            if (isset($dataStore[$key]) && is_object($dataStore[$key])) {
              $patternSpecificData[$key] = clone $dataStore[$key];
            }
          }
        }
        Data::initPattern($pattern);
        Data::setPatternData($pattern, $patternSpecificData);
    }

    protected function processData($data)
    {
        foreach (array_keys($data) as $key) {
            if (!in_array($key, $this->reservedKeys)) {
                $this->setErrorHandler();
                $data = $this->processKey($data, $key);
                $this->restoreErrorHandler("error processing key '$key'");
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
                DataTransformPlugin::writeInfo('created Attribute object', true);
            }
            elseif (isset($value['Url()']['url'])) {
              $options = isset($value['Url()']['options']) && is_array($value['Url()']['options']) ? $value['Url()']['options'] : [];
              $data[$key] = Url::fromUri($value['Url()']['url'], $options);
                DataTransformPlugin::writeInfo('created Url object', true);
            }
            elseif (isset($value['include()']) && is_array($value['include()']) && isset($value['include()']['pattern'])) {
                $pattern = $value['include()']['pattern'];
                if (is_string($pattern)) {
                    if (isset($this->patternDataStore[$pattern])) {
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
                        DataTransformPlugin::writeInfo("included pattern '$pattern'", true);
                    }
                    else {
                        DataTransformPlugin::writeWarning("could not find '$pattern' to include", DataTransformPlugin::isVerbose());
                    }
                }
                else {
                    DataTransformPlugin::writeWarning('include() pattern key value was not a string', DataTransformPlugin::isVerbose());
                }
            }
            elseif (isset($value['join()']) && is_array($value['join()'])) {
                $data[$key] = join($value['join()']);
                DataTransformPlugin::writeInfo("joined data under key '$key'", true);
            }
            else {
                $data[$key] = $value;
            }
        }
        elseif (is_string($value) && isset($this->patternDataStore[$value]) && $key !== 'pattern') {
            $data[$key] = $this->renderPattern($value, $this->getProcessedPatternSpecificData($value));
            DataTransformPlugin::writeInfo("included pattern '$value'", true);
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
        $rendered = '';
        if (isset($this->patternDataStore[$pattern]['patternRaw'])) {
            foreach (array_keys($data) as $key) {
                $data = $this->cloneObjects($data, $key);
            }
            $rendered = $this->renderer->render(
                $this->patternDataStore[$pattern]['patternRaw'],
                $data
            );
        }

        return $rendered;
    }

    protected function cloneObjects($data, $key)
    {
        $value = $data[$key];
        if (is_array($value)) {
            foreach (array_keys($value) as $subKey) {
                $value = $this->cloneObjects($value, $subKey);
            }
            $data[$key] = $value;
        }
        elseif (is_object($value)) {
            $data[$key] = clone $value;
        }

        return $data;
    }
}
