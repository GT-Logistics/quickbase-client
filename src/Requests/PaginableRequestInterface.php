<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Requests;

interface PaginableRequestInterface
{
    public const PAGINATION_IN_QUERY = 'query';

    public const PAGINATION_IN_PAYLOAD = 'payload';

    /**
     * @param non-negative-int $count
     *
     * @return static
     */
    public function withSkip(int $count): PaginableRequestInterface;

    /**
     * @return self::PAGINATION_IN_*
     */
    public function getPaginationType(): string;
}
