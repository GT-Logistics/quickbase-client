<?php
/*
 * Copyright (c) 2024 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Test\Unit\Utils;

use Gtlogistics\QuickbaseClient\Models\User;
use Gtlogistics\QuickbaseClient\Utils\QuickbaseUtils;
use PHPUnit\Framework\TestCase;

class QuickbaseUtilsTest extends TestCase
{
    /**
     * @testWith [1296000, 1296000000]
     *           [1296000, 1296000250]
     *           [1296001, 1296000500]
     *           [1296001, 1296000750]
     */
    public function testParseDurationField(int $expected, int $value): void
    {
        $interval = QuickbaseUtils::parseField($value, 'duration');

        $this->assertInstanceOf(\DateInterval::class, $interval);
        $this->assertEquals($expected, $interval->s);
    }

    /**
     * @testWith ["123456.ab1s", "jsmith@quickbase.com", "jsmith", "Juliet Smith", {"id": "123456.ab1s", "email": "jsmith@quickbase.com", "userName": "jsmith", "name": "Juliet Smith"}]
     *           [null, "jsmith@quickbase.com", null, null, {"email": "jsmith@quickbase.com"}]
     *           ["123456.ab1s", null, null, null, {"id": "123456.ab1s"}]
     */
    public function testParseUserField(
        ?string $expectedId,
        ?string $expectedEmail,
        ?string $expectedUserName,
        ?string $expectedName,
        array $value
    ): void {
        $user = QuickbaseUtils::parseField($value, 'user');

        $this->assertUser($expectedId, $expectedEmail, $expectedUserName, $expectedName, $user);
    }

    public function testParseMultiuserField(): void
    {
        $users = QuickbaseUtils::parseField(
            [
                ['id' => '123456.ab1s', 'email' => 'user1@quickbase.com', 'name' => 'user 1'],
                ['id' => '254789.mkgp', 'email' => 'user2@quickbase.com', 'name' => 'user 2'],
                ['id' => '789654.vc2s', 'email' => 'user3@quickbase.com', 'name' => 'user 3'],
            ],
            'multiuser',
        );

        $this->assertIsArray($users);
        $this->assertCount(3, $users);
        $this->assertUser('123456.ab1s', 'user1@quickbase.com', null, 'user 1', $users[0]);
        $this->assertUser('254789.mkgp', 'user2@quickbase.com', null, 'user 2', $users[1]);
        $this->assertUser('789654.vc2s', 'user3@quickbase.com', null, 'user 3', $users[2]);
    }

    private function assertUser(
        ?string $expectedId,
        ?string $expectedEmail,
        ?string $expectedUserName,
        ?string $expectedName,
        $value
    ): void {
        $this->assertInstanceOf(User::class, $value);
        $this->assertSame($expectedId, $value->getId());
        $this->assertSame($expectedEmail, $value->getEmail());
        $this->assertSame($expectedUserName, $value->getUserName());
        $this->assertSame($expectedName, $value->getName());
    }
}
