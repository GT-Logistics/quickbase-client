<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Responses;

use Gtlogistics\QuickbaseClient\Utils\QuickbaseUtils;

/**
 * @internal
 */
final class DeletedRecordsResponse extends AbstractResponse
{
    /**
     * @var array{
     *     numberDeleted: non-negative-int,
     * }
     */
    protected array $data;

    public function getNumberDeleted(): int
    {
        return $this->data['numberDeleted'];
    }
}
