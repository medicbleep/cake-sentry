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
        if (!Configure::read('Sentry.production_only')) {
            Sentry\init([
                'dsn' => Configure::read('SENTRY_DSN'),
                'traces_sample_rate' => 1.0,
                'environment' => Configure::read('environment'),
            ]);
            if (class_exists('AuthComponent')) {
                $model = Configure::read('Sentry.User.model');
                if (empty($model)) {
                    $model = 'User';
                }
                $User = ClassRegistry::init($model);
                $mail = Configure::read('Sentry.User.email_field');
                if (empty($mail)) {
                    if ($User->hasField('email')) {
                        $mail = 'email';
                    } else {
                        if ($User->hasField('mail')) {
                            $mail = 'mail';
                        }
                    }
                }
                if (AuthComponent::user($User->primaryKey)) {
                    Sentry\configureScope(function (Sentry\State\Scope $scope) use ($mail, $User): void {
                        $scope->setUser([
                            'email' => AuthComponent::user($mail),
                            "id" => AuthComponent::user($User->primaryKey),
                            "username" => AuthComponent::user($User->displayField),
                        ]);
                    });
                }
            }
            Sentry\captureException($exception);
        }
    }

    public static function handleException($exception)
    {
        try {
            // Avoid bot scan errors
            if (Configure::read('Sentry.avoid_bot_scan_errors') && ($exception instanceof MissingControllerException || $exception instanceof MissingPluginException) && Configure::read('debug') == 0) {
                echo Configure::read('Sentry.avoid_bot_scan_errors');
                exit(0);
            }

            self::sentryLog($exception);

            parent::handleException($exception);
        } catch (Exception $e) {
            parent::handleException($e);
        }
    }
}
