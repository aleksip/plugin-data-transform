<?php

namespace aleksip\DataTransformPlugin;

trait ErrorHandlerTrait
{
    protected static $levels = [
        E_WARNING => 'E_WARNING',
        E_NOTICE => 'E_NOTICE',
        E_DEPRECATED => 'E_DEPRECATED',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED',
    ];

    protected $errno;
    protected $errstr;

    protected function errorHandler($errno, $errstr)
    {
        $this->errno = $errno;
        $this->errstr = $errstr;

        return TRUE;
    }

    protected function setErrorHandler()
    {
        $this->errno = null;
        $this->errstr = null;
        set_error_handler([$this, 'errorHandler']);
    }

    protected function restoreErrorHandler($errorMessage = null)
    {
        restore_error_handler();
        if (isset($this->errno)) {
            $level = isset(self::$levels[$this->errno]) ? self::$levels[$this->errno] : $this->errno;
            if (isset($errorMessage)) {
                DataTransformPlugin::writeWarning($errorMessage);
            }
            DataTransformPlugin::writeWarning($this->errstr . ' (' . $level . ')', isset($errorMessage));
        }
    }
}
