<?php
/*
 * Copyright (c) 2024 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Models;

use Webmozart\Assert\Assert;

final class User implements \JsonSerializable
{
    private ?string $id;

    private ?string $email;

    private ?string $userName;

    private ?string $name;

    public function __construct(
        ?string $id = null,
        ?string $email = null,
        ?string $userName = null,
        ?string $name = null
    ) {
        if ($id === null && $email === null) {
            throw new \InvalidArgumentException('You must specify an id or email');
        }

        Assert::nullOrNotEmpty($id);
        Assert::nullOrNotEmpty($email);

        if ($userName !== null) {
            Assert::stringNotEmpty($userName);
        }
        if ($name !== null) {
            Assert::stringNotEmpty($name);
        }

        $this->id = $id;
        $this->email = $email;
        $this->userName = $userName;
        $this->name = $name;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function withUserId(string $userId): self
    {
        $cloned = clone $this;
        $cloned->id = $userId;

        return $cloned;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function withEmail(string $email): self
    {
        $cloned = clone $this;
        $cloned->email = $email;

        return $cloned;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function withUserName(string $userName): self
    {
        $cloned = clone $this;
        $cloned->userName = $userName;

        return $cloned;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function withName(string $name): self
    {
        $cloned = clone $this;
        $cloned->name = $name;

        return $cloned;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'userName' => $this->userName,
            'name' => $this->name,
        ];
    }
}
