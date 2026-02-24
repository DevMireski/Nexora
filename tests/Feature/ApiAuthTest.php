<?php
namespace Tests\Feature;

use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    public function test_users_endpoint_requires_auth()
    {
        $this->json('GET', '/api/v1/users')
            ->assertStatus(401);
    }
}
