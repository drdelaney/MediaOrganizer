<?php

if (!function_exists('user_date')) {
    /**
     * Convert UTC datetime from database to user's configured timezone
     * 
     * @param string|null $datetime UTC datetime string
     * @param string $format Output format
     * @return string
     */
    function user_date($datetime, $format = 'M d, Y H:i')
    {
        if (empty($datetime)) {
            return '';
        }

        $configModel = new \App\Models\ConfigurationModel();
        $timezone = $configModel->getParam('timezone', 'UTC');

        try {
            $date = new \DateTime($datetime, new \DateTimeZone('UTC'));
            $date->setTimezone(new \DateTimeZone($timezone));
            return $date->format($format);
        } catch (\Exception $e) {
            return $datetime;
        }
    }
}

if (!function_exists('database_now')) {
    /**
     * Get current time in UTC for database storage
     * 
     * @param string $format
     * @return string
     */
    function database_now($format = 'Y-m-d H:i:s')
    {
        return gmdate($format);
    }
}
