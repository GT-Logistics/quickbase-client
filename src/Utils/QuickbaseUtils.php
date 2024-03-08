<?php
/*
 * Copyright (c) 2024 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Utils;

use Gtlogistics\QuickbaseClient\Models\Date;
use Gtlogistics\QuickbaseClient\Models\Time;

/**
 * @internal
 */
final class QuickbaseUtils
{
    /**
     * @param mixed $value
     * @param 'text'|'numeric'|'timestamp'|'date'|'timeofday' $type
     *
     * @return mixed
     */
    public static function parseField($value, string $type)
    {
        if ($value === '' && in_array($type, ['timestamp', 'date', 'timeofday'])) {
            return null;
        }
        if ($type === 'timestamp') {
            return new \DateTimeImmutable($value);
        }
        if ($type === 'date') {
            return new Date($value);
        }
        if ($type === 'timeofday') {
            return new Time($value);
        }

        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public static function serializeField($value)
    {
        if ($value instanceof Date) {
            return $value->format('Y-m-d');
        }
        if ($value instanceof Time) {
            return $value->format('H:i:s');
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return $value;
    }
}
