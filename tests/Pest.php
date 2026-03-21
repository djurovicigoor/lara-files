<?php

use Djurovicigoor\LaraFiles\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

uses(TestCase::class)->in(__DIR__);

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

function something()
{
    //
}
