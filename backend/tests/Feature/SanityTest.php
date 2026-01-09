<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SanityTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_works(): void
    {
        $this->assertTrue(true);
    }
}
