<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Requests;

interface PaginableRequestInterface
{
    /**
     * @param non-negative-int $count
     *
     * @return static
     */
    public function withSkip(int $count): PaginableRequestInterface;
}
