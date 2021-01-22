<?php

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

     public function __construct() {
         $userConfig = Configure::read('Sentry.init');
         if (is_array($userConfig)) {
             $this->clientConfig = array_merge_recursive($this->defaults, $userConfig);
         }

         Sentry\init($this->clientConfig);
     }
}