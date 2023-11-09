<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Requests;

interface PaginableRequestInterface
{
    /**
     * @return static
     */
    public function withSkip(int $count);
}
