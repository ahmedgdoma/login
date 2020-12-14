<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class loginTest extends TestCase
{

    public function testFailLogin()
    {
        $response = $this->call('POST', '/admin/login', ['email' => 'admin@careers.dev', 'password'=> 'client']);
        $this->assertEquals(401, $response->status());

    }
    public function testSuccessLogin()
    {
        $response = $this->call('POST', '/admin/login', ['email' => 'admin@careers.dev', 'password'=> 'admin']);
        $this->assertEquals(200, $response->status());
        return $response['access_token'];

    }

    public function testFailLogout()
    {
        $this->json('POST', '/admin/logout');
        $this->assertEquals(401, $this->response->status());

    }

    /**
     * @depends testSuccessLogin
     */
    public function testSuccessLogout($token)
    {
        $this->json('POST', '/admin/logout', [], ['Authorization' => 'bearer ' . $token]);
        $this->assertEquals(200, $this->response->status());

    }
}
