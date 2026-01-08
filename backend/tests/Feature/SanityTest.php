<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SanityTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_works(): void
    {
        $this->assertTrue(true);
    }
}