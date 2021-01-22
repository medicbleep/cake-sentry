<?php


/**
 * Description of SentryErrorHandler
 *
 * @author Sandreu
 */
class SentryErrorHandler extends ErrorHandler
{

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
        $defaultParams = [
            'traces_sample_rate' => 1.0
        ];

        $config = Configure::read('Sentry.init');
        $sentryParams = [];
        if (is_array($config)) {
            $sentryParams = array_merge_recursive($defaultParams, $config);
        }

        Sentry\init($sentryParams);
        Sentry\captureException($exception);
    }

    public static function handleException($exception)
    {
        try {
            self::sentryLog($exception);

            parent::handleException($exception);
        } catch (Exception $e) {
            parent::handleException($e);
        }
    }
}
