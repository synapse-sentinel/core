<?php

declare(strict_types=1);

describe('Application', function () {
    it('returns a successful response for the homepage', function () {
        $response = $this->get('/');

        $response->assertOk();
    });

    it('has a health check endpoint', function () {
        $response = $this->get('/up');

        $response->assertOk();
    });
});
