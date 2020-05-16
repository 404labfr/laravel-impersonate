<?php

namespace Lab404\Tests;

use Lab404\Tests\Stubs\Models\User;

class ScopeTest extends TestCase
{
    /** @test */
    public function get_all_impersonated()
    {
        $this->actingAs(User::find(1));

        $this->assertCount(2, User::getCanImpersonate());
    }
}