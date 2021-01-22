<?php


/**
 * Description of SentryErrorHandler
 *
 * @author Sandreu
 */
class SentryErrorHandler extends ErrorHandler
{

    public static $exceptionFilterFunc = null;

    public static function handleError($code, $description, $file = null, $line = null, $context = null)
    {
        try {
            $e = new ErrorException($description, 0, $code, $file, $line);
            self::sentryLog($e);

            return parent::handleError($code, $description, $file, $line, $context);
        } catch (Exception $e) {
            self::handleException($e);
        }
    }

    protected static function sentryLog($exception)
    {
        Sentry\captureException($exception);
    }

    public static function handleException($exception)
    {

        if (is_callable(self::$exceptionFilterFunc) || !self::$exceptionFilterFunc.($exception)) {
            parent::handleException($exception);
	    return;
        }

        try {
            self::sentryLog($exception);
        } finally {
            parent::handleException($exception);
        }
    }
}
