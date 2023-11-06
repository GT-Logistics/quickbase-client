<?php

namespace Gtlogistics\QuickbaseClient\Requests;

interface PaginableRequestInterface
{
    /**
     * @return static
     */
    public function withSkip(int $count);
}
