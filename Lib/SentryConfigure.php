<?php

App::uses('SentryErrorHandler', 'Sentry.Lib');

/**
 * Class SentryConfigure
 *
 * Configuration wrapper for Sentry. Designed to be used in bootstrap.php
 *
 *    // Setup Sentry configuration
 *    App::uses('SentryConfigure', 'Sentry.Lib');
 *    new SentryConfigure();
 *
 */
class SentryConfigure {
     private $defaults = ['traces_sample_rate' => 1.0];
     private $clientConfig = [];

     public function __construct($exceptionIgnoreList = []) {
         $userConfig = Configure::read('Sentry.init');
         if (is_array($userConfig)) {
             $this->clientConfig = array_merge_recursive($this->defaults, $userConfig);
         }

         $this->addExceptionFilter($exceptionIgnoreList);
         Sentry\init($this->clientConfig);
     }

    /**
     * Adds an exception filter function to the SentryErrorHandler
     * @param array $exceptionIgnoreList
     * @returns void
     */
     private function addExceptionFilter($exceptionIgnoreList = []) {
         SentryErrorHandler::$exceptionFilterFunc = function(Exception $exception) use (&$exceptionIgnoreList): bool {
             return in_array(get_class($exception), $exceptionIgnoreList);
         };
     }

}