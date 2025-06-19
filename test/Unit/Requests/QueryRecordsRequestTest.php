<?php
/*
 * Copyright (c) 2025 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Test\Unit\Requests;

use Gtlogistics\QuickbaseClient\Requests\QueryRecordsRequest;
use PHPUnit\Framework\TestCase;

class QueryRecordsRequestTest extends TestCase
{
    public function testEmptyQuery(): void
    {
        $request = new  QueryRecordsRequest('abcdefghi');
        $request->withWhere('');

        $this->assertNotNull($request);
    }
}
