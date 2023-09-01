<?php

namespace App\Util;

class GraphUtil
{

    const DEFAULT_DAYS = 30;

    public static function graphDates($days = self::DEFAULT_DAYS) : array
    {
        $result = [];
        $now = new \DateTime();
        $result[] = $now->format('Y-m-d');

        for ($i = 1; $i <= $days; $i++) {
            $back = $now->modify('-1 day');
            $result[] = $back->format('Y-m-d');
        }
        return $result;
    }

    public static function fixGraphData(array $result) : array {
        $now = new \DateTime();
        if (!array_key_exists($now->format('Y-m-d'), $result)) {
            $result[$now->format('Y-m-d')] = 0;
        }

        for ($i = 1; $i <= GraphUtil::DEFAULT_DAYS; $i++) {
            $date = $now->modify('-1 day');
            $format = $date->format('Y-m-d');
            if (!array_key_exists($format, $result)) {
                $result[$format] = 0;
            }
        }

        ksort($result);

        return $result;
    }

}