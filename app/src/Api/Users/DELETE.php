<?php

declare(strict_types=1);

namespace App\Api\Users;

use App\User;

class DELETE extends Resource
{
    protected static $description = 'Deletes an user.';

    // private $private = "Can't touch this!";
    public function __construct(User $user)
    {
    }

    public function __invoke()
    {
        // $GET = $this->invoke('@:GET', $user);
        // $this->source = 'deez';
        // // $that is "this"
        // $this->hookable('deleteUser', function ($that) use ($user) {
        //     $that->private .= ' - MC HAMMER';
        //     $that->source .= ' nuuuuts ';
        // });
    }
}
