<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient;

use Webmozart\Assert\Assert;
use function _PHPStan_39fe102d2\RingCentral\Psr7\str;

/**
 * @method self contains(int $fieldId, string|int|float|bool|null $value, string $boolean='AND')
 * @method self orContains(int $fieldId, string|int|float|bool|null $value)
 * @method self notContains(int $fieldId, string|int|float|bool|null $value, string $boolean='AND')
 * @method self orNotContains(int $fieldId, string|int|float|bool|null $value)
 * @method self has(int $fieldId, string|int|float|bool|null $value, string $boolean='AND')
 * @method self orHas(int $fieldId, string|int|float|bool|null $value)
 * @method self notHas(int $fieldId, string|int|float|bool|null $value, string $boolean='AND')
 * @method self orNotHas(int $fieldId, string|int|float|bool|null $value)
 * @method self equals(int $fieldId, string|int|float|bool|null $value, string $boolean='AND')
 * @method self orEquals(int $fieldId, string|int|float|bool|null $value)
 * @method self notEquals(int $fieldId, string|int|float|bool|null $value, string $boolean='AND')
 * @method self orNotEquals(int $fieldId, string|int|float|bool|null $value)
 * @method self trueValue(int $fieldId, string|int|float|bool|null $value, string $boolean='AND')
 * @method self orTrueValue(int $fieldId, string|int|float|bool|null $value)
 * @method self notTrueValue(int $fieldId, string|int|float|bool|null $value, string $boolean='AND')
 * @method self orNotTrueValue(int $fieldId, string|int|float|bool|null $value)
 * @method self startsWith(int $fieldId, string|int|float|bool|null $value, string $boolean='AND')
 * @method self orStartsWith(int $fieldId, string|int|float|bool|null $value)
 * @method self notStartsWith(int $fieldId, string|int|float|bool|null $value, string $boolean='AND')
 * @method self orNotStartsWith(int $fieldId, string|int|float|bool|null $value)
 * @method self range(int $fieldId, string|int|float|bool|null $value, string $boolean='AND')
 * @method self orRange(int $fieldId, string|int|float|bool|null $value)
 * @method self notRange(int $fieldId, string|int|float|bool|null $value, string $boolean='AND')
 * @method self orNotRange(int $fieldId, string|int|float|bool|null $value)
 * @method self before(int $fieldId, string|int|float|bool|null $value, string $boolean='AND')
 * @method self orBefore(int $fieldId, string|int|float|bool|null $value)
 * @method self onOrBefore(int $fieldId, string|int|float|bool|null $value, string $boolean='AND')
 * @method self orOnOrBefore(int $fieldId, string|int|float|bool|null $value)
 * @method self after(int $fieldId, string|int|float|bool|null $value, string $boolean='AND')
 * @method self orAfter(int $fieldId, string|int|float|bool|null $value)
 * @method self onOrAfter(int $fieldId, string|int|float|bool|null $value, string $boolean='AND')
 * @method self orOnOrAfter(int $fieldId, string|int|float|bool|null $value)
 * @method self less(int $fieldId, string|int|float|bool|null $value, string $boolean='AND')
 * @method self orLess(int $fieldId, string|int|float|bool|null $value)
 * @method self lessOrEquals(int $fieldId, string|int|float|bool|null $value, string $boolean='AND')
 * @method self orLessOrEquals(int $fieldId, string|int|float|bool|null $value)
 * @method self greater(int $fieldId, string|int|float|bool|null $value, string $boolean='AND')
 * @method self orGreater(int $fieldId, string|int|float|bool|null $value)
 * @method self greaterOrEquals(int $fieldId, string|int|float|bool|null $value, string $boolean='AND')
 * @method self orGreaterOrEquals(int $fieldId, string|int|float|bool|null $value)
 */
final class Query
{
    private const COMPARISON_OPERATORS = [
        'contains' => 'CT',
        'has' => 'HAS',
        'equals' => 'EX',
        'trueValue' => 'TV',
        'startsWith' => 'SW',
        'range' => 'IR',
    ];

    private const RANGE_OPERATORS = [
        'before' => 'BF',
        'onOrBefore' => 'OBF',
        'after' => 'AF',
        'onOrAfter' => 'OAF',
        'less' => 'LT',
        'lessOrEquals' => 'LTE',
        'greater' => 'GT',
        'greaterOrEquals' => 'GTE',
    ];

    /**
     * @var array{fieldId: int, operator: string, value: string, boolean: string}[]
     */
    private array $conditions = [];

    /**
     * @return array<string, string>
     */
    private function getOperators(): array
    {
        $operators = array_merge(self::COMPARISON_OPERATORS, self::RANGE_OPERATORS);
        // Add negated operators
        foreach (self::COMPARISON_OPERATORS as $function => $boolean) {
            $operators['not' . ucfirst($function)] = 'X' . $boolean;
        }

        return $operators;
    }

    /**
     * @param mixed[] $arguments
     */
    public function __call(string $name, array $arguments): self
    {
        $boolean = $arguments[2] ?? 'AND';
        if (str_starts_with($name, 'or')) {
            $boolean = 'OR';
            $name = lcfirst(str_replace('or', '', $name));
        }
        Assert::inArray($boolean, ['AND', 'OR']);

        $operators = $this->getOperators();
        Assert::inArray($name, array_keys($operators));
        $operator = $operators[$name];

        $fieldId = $arguments[0] ?? null;
        Assert::positiveInteger($fieldId);

        $value = $arguments[1] ?? null;
        if ($value !== null) {
            Assert::scalar($value);
        }

        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        } elseif ($value === null) {
            $value = '';
        }

        $clone = clone $this;
        $clone->conditions[] = ['fieldId' => $fieldId, 'operator' => $operator, 'value' => $value, 'boolean' => $boolean];

        return $clone;
    }

    public function __toString(): string
    {
        $query = '';
        $first = true;
        foreach ($this->conditions as $condition) {
            if (!$first) {
                $query .= $condition['boolean'];
            }

            $query .= "{'{$condition['fieldId']}'.{$condition['operator']}.'{$condition['value']}'}";
            $first = false;
        }

        return $query;
    }
}
