<?php
/*
 * Copyright (c) 2024 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Requests;

final class RunReportRequest implements PaginableRequestInterface
{
    private string $reportId;

    private string $tableId;

    private ?int $skip = null;

    private ?int $top = null;

    public function __construct($reportId, $tableId)
    {
        $this->reportId = $reportId;
        $this->tableId = $tableId;
    }

    public function getReportId(): string
    {
        return $this->reportId;
    }

    public function withReportId(string $reportId): self
    {
        $clone = clone $this;
        $clone->reportId = $reportId;

        return $clone;
    }

    public function getTableId(): string
    {
        return $this->tableId;
    }

    public function withTableId(string $tableId): self
    {
        $clone = clone $this;
        $clone->tableId = $tableId;

        return $clone;
    }

    public function getSkip(): ?int
    {
        return $this->skip;
    }

    public function withSkip(int $count): self
    {
        $clone = clone $this;
        $clone->skip = $count;

        return $clone;
    }

    public function getTop(): ?int
    {
        return $this->top;
    }

    public function withTop(int $top): self
    {
        $clone = clone $this;
        $clone->top = $top;

        return $clone;
    }

    public function getPaginationType(): string
    {
        return self::PAGINATION_IN_QUERY;
    }
}
