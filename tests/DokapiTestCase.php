<?php

namespace AndyFraussen\Dokapi\Tests;

use Orchestra\Testbench\TestCase;
use AndyFraussen\Dokapi\DokapiServiceProvider;

class DokapiTestCase extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            DokapiServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Perform any environment setup here
    }
}
