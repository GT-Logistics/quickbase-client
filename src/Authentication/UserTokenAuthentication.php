<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Authentication;

use Http\Message\Authentication;
use Psr\Http\Message\RequestInterface;

class UserTokenAuthentication implements Authentication
{
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function authenticate(RequestInterface $request)
    {
        return $request->withHeader('Authorization', "QB-USER-TOKEN $this->token");
    }
}
