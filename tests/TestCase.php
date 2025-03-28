<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function authenticate(?User $user = null)
    {
        $user ??= User::factory()->create();
        $this->actingAs($user);

        return $user;


    }
}
