<?php

namespace Gtlogistics\QuickbaseClient\Test\Unit;

use Gtlogistics\QuickbaseClient\Query;
use PHPUnit\Framework\TestCase;

final class QueryTest extends TestCase
{
    public function testWrongOperator(): void
    {
        $this->expectException(\RuntimeException::class);

        $query = new Query();
        $query->notError(1, 'test');
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

    public function testWrongFieldId(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $query = new Query();
        $query->contains(null, 'test');
    }

    public function testWrongValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $query = new Query();
        $query->contains(1, null);
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
