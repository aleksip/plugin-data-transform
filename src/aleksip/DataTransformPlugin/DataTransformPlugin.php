<?php

namespace aleksip\DataTransformPlugin;

use PatternLab\Config;
use PatternLab\Console;

class DataTransformPlugin
{
    public static function isEnabled()
    {
        $enabled = Config::getOption('plugins.dataTransform.enabled');
        $enabled = (is_null($enabled) || (bool)$enabled);

        if (!$enabled) {
            self::writeInfo('plugin is disabled');
        }

        return $enabled;
    }

    public static function isVerbose()
    {
        $verbose = Config::getOption('plugins.dataTransform.verbose');

        return (!is_null($verbose) && (bool)$verbose);
    }

    public static function writeInfo($line, $indent = false)
    {
        if (self::isVerbose()) {
            if (!$indent) {
                $line = '[data transform plugin] ' . $line;
            }
            Console::writeInfo($line, $indent);
        }
    }

    public static function writeWarning($line, $indent = false)
    {
        if (!$indent) {
            $line = '[data transform plugin] ' . $line;
        }
        Console::writeWarning($line, $indent);
    }
}
