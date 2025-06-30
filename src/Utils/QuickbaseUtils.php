<?php
/*
 * Copyright (c) 2024 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Utils;

use Gtlogistics\QuickbaseClient\Models\Date;
use Gtlogistics\QuickbaseClient\Models\Time;
use Gtlogistics\QuickbaseClient\Models\User;

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
            return \DateTimeImmutable::createFromFormat(DATE_ATOM, $value)
                // Because Quickbase return the Z (Zulu) timezone, and that timezone
                // is not equivalent to the UTC timezone in the Intl ICU data, we
                // assigned manually the UTC to the date returned.
                ->setTimezone(new \DateTimeZone('UTC'));
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
            return $value->format(DATE_ATOM);
        }
        if ($value instanceof \DateInterval) {
            return self::serializeDateInterval($value);
        }

        return $value;
    }

    private static function parseUser(?array $value): ?User
    {
        if ($value === null) {
            return null;
        }

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
