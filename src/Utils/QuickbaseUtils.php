<?php
/*
 * Copyright (c) 2024 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Utils;

use Gtlogistics\QuickbaseClient\Models\Date;
use Gtlogistics\QuickbaseClient\Models\Time;
use Gtlogistics\QuickbaseClient\Models\User;

use function Safe\sprintf;

/**
 * @internal
 */
final class QuickbaseUtils
{
    /**
     * @param mixed $value
     * @param 'text'|'numeric'|'timestamp'|'date'|'timeofday'|'duration' $type
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
        if ($type === 'duration') {
            return new \DateInterval(sprintf('PT%dS', round($value / 1000)));
        }
        if ($type === 'user') {
            return self::parseUser($value);
        }
        if ($type === 'multiuser') {
            return array_map([self::class, 'parseUser'], $value);
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
        if ($value instanceof \DateInterval) {
            return self::serializeDateInterval($value);
        }

        return $value;
    }

    private static function parseUser(array $value): User
    {
        return new User(
            $value['id'] ?? null,
            $value['email'] ?? null,
            $value['userName'] ?? null,
            $value['name'] ?? null,
        );
    }

    private static function serializeDateInterval(\DateInterval $value): string
    {
        $seconds = $value->s;
        $seconds += $value->i * 60;
        $seconds += $value->h * 60 * 60;
        $seconds += $value->d * 24 * 60 * 60;
        $seconds += $value->m * 30 * 24 * 60 * 60;
        $seconds += $value->y * 365 * 24 * 60 * 60;

        return $seconds;
    }
}
