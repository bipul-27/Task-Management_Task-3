<?php

/**
 * Enable Query Log
 */
if (!function_exists('fluentplugin_eql')) {
    function fluentplugin_eql()
    {
        defined('SAVEQUERIES') || define('SAVEQUERIES', true);
    }
}

/**
 * Get Query Log
 */
if (!function_exists('fluentplugin_gql')) {
    function fluentplugin_gql()
    {
        $result = [];
        foreach ((array)$GLOBALS['wpdb']->queries as $key => $query) {
            $result[++$key] = array_combine([
                'query', 'execution_time'
            ], array_slice($query, 0, 2));
        }
        return $result;
    }
}

