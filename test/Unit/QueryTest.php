<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Test\Unit;

use Gtlogistics\QuickbaseClient\Query;
use PHPUnit\Framework\TestCase;

final class QueryTest extends TestCase
{
    public function testWrongOperator(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $query = new Query();
        $query->notError(1, 'test');
    }

    /**
     * @testWith ["CT", "{'1'.CT.'test'}"]
     *           ["XCT", "{'1'.XCT.'test'}"]
     *           ["HAS", "{'1'.HAS.'test'}"]
     *           ["XHAS", "{'1'.XHAS.'test'}"]
     *           ["EX", "{'1'.EX.'test'}"]
     *           ["XEX", "{'1'.XEX.'test'}"]
     *           ["TV", "{'1'.TV.'test'}"]
     *           ["XTV", "{'1'.XTV.'test'}"]
     *           ["SW", "{'1'.SW.'test'}"]
     *           ["XSW", "{'1'.XSW.'test'}"]
     *           ["BF", "{'1'.BF.'test'}"]
     *           ["OBF", "{'1'.OBF.'test'}"]
     *           ["AF", "{'1'.AF.'test'}"]
     *           ["OAF", "{'1'.OAF.'test'}"]
     *           ["IR", "{'1'.IR.'test'}"]
     *           ["XIR", "{'1'.XIR.'test'}"]
     *           ["LT", "{'1'.LT.'test'}"]
     *           ["LTE", "{'1'.LTE.'test'}"]
     *           ["GT", "{'1'.GT.'test'}"]
     *           ["GTE", "{'1'.GTE.'test'}"]
     */
    public function testWhere(string $operator, string $expected): void
    {
        $query = new Query();
        $query = $query->where(1, $operator, 'test');

        $this->assertSame($expected, (string) $query);
    }

    /**
     * @testWith ["contains", "{'1'.CT.'test'}"]
     *           ["notContains", "{'1'.XCT.'test'}"]
     *           ["has", "{'1'.HAS.'test'}"]
     *           ["notHas", "{'1'.XHAS.'test'}"]
     *           ["equals", "{'1'.EX.'test'}"]
     *           ["notEquals", "{'1'.XEX.'test'}"]
     *           ["trueValue", "{'1'.TV.'test'}"]
     *           ["notTrueValue", "{'1'.XTV.'test'}"]
     *           ["startsWith", "{'1'.SW.'test'}"]
     *           ["notStartsWith", "{'1'.XSW.'test'}"]
     *           ["before", "{'1'.BF.'test'}"]
     *           ["onOrBefore", "{'1'.OBF.'test'}"]
     *           ["after", "{'1'.AF.'test'}"]
     *           ["onOrAfter", "{'1'.OAF.'test'}"]
     *           ["range", "{'1'.IR.'test'}"]
     *           ["notRange", "{'1'.XIR.'test'}"]
     *           ["less", "{'1'.LT.'test'}"]
     *           ["lessOrEquals", "{'1'.LTE.'test'}"]
     *           ["greater", "{'1'.GT.'test'}"]
     *           ["greaterOrEquals", "{'1'.GTE.'test'}"]
     */
    public function testOperators(string $operator, string $expected): void
    {
        $query = new Query();
        $query = $query->{$operator}(1, 'test');

        $this->assertSame($expected, (string) $query);
    }

    /**
     * @testWith ["{'1'.EX.'2'}", 2]
     *           ["{'1'.EX.'2.5'}", 2.5]
     *           ["{'1'.EX.'0'}", false]
     *           ["{'1'.EX.''}", null]
     */
    public function testScalarValue(string $expected, $value): void
    {
        $query = new Query();
        $query = $query->equals(1, $value);

        $this->assertSame($expected, (string) $query);
    }

    public function testWrongFieldId(): void
    {
        $this->expectException(\TypeError::class);

        $query = new Query();
        $query->contains(null, 'test');
    }

    public function testWrongValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $object = new \stdClass();
        $query = new Query();
        $query->contains(1, $object);
    }

    public function testWrongBoolean(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $query = new Query();
        $query->contains(1, 'test', 'NULL');
    }

    public function testAndQuery(): void
    {
        $query = new Query();
        $query = $query->contains(5, 'Ragnar Lodbrok');
        $query = $query->contains(7, 'Acquisitions');

        $this->assertSame("{'5'.CT.'Ragnar Lodbrok'}AND{'7'.CT.'Acquisitions'}", (string) $query);
    }

    public function testOrQuery(): void
    {
        $query = new Query();
        $query = $query->contains(5, 'Ragnar Lodbrok');
        $query = $query->orContains(7, 'Acquisitions');

        $this->assertSame("{'5'.CT.'Ragnar Lodbrok'}OR{'7'.CT.'Acquisitions'}", (string) $query);
    }
}
