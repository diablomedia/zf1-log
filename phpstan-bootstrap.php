<?php

if (!function_exists('zend_monitor_custom_event')) {
    /**
     * @link http://files.zend.com/help/Zend-Server-6.3/content/zendserverapi/zend_monitor-php_api.htm#function-zend_monitor_custom_event
     * @param string $class
     * @param string $text
     * @param mixed $user_data
     * @param int $severity
     * @return void
     */
    function zend_monitor_custom_event($class, $text, $user_data = null, $severity = null)
    {
    }
}
