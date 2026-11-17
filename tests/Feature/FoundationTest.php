<?php

declare(strict_types=1);

it('uses the production stack defaults from the plan', function (): void {
    expect(config('database.default'))->toBe('mysql')
        ->and(config('app.locale'))->toBe('ar')
        ->and(config('cache.default'))->toBe('file')
        ->and(config('session.driver'))->toBe('file')
        ->and(config('queue.default'))->toBe('sync');
});

it('serves the built-in health endpoint', function (): void {
    $this->get('/up')->assertOk();
});
