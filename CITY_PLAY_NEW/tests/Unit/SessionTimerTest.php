<?php

namespace Tests\Unit;

use App\Models\GameSession;
use App\Support\SessionTimer;
use Carbon\Carbon;
use Tests\TestCase;

class SessionTimerTest extends TestCase
{
    public function test_remaining_seconds_decreases_with_elapsed_time(): void
    {
        $session = new GameSession([
            'available_minutes'   => 60,
            'started_at'          => now()->subMinutes(10),
            'total_pause_seconds' => 0,
            'status'              => 'active',
        ]);

        $remaining = SessionTimer::remainingSeconds($session);

        $this->assertGreaterThan(0, $remaining);
        $this->assertLessThanOrEqual(60 * 60, $remaining);
        $this->assertLessThan(60 * 60 - 9 * 60, $remaining);
    }

    public function test_pause_freezes_elapsed_time(): void
    {
        $session = new GameSession([
            'available_minutes'   => 30,
            'started_at'          => Carbon::parse('2026-01-01 10:00:00'),
            'paused_at'           => Carbon::parse('2026-01-01 10:15:00'),
            'total_pause_seconds' => 0,
            'status'              => 'paused',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-01-01 11:00:00'));

        $this->assertSame(15 * 60, SessionTimer::elapsedActiveSeconds($session));
        $this->assertSame(15 * 60, SessionTimer::remainingSeconds($session));

        Carbon::setTestNow();
    }

    public function test_is_expired_when_limit_reached(): void
    {
        $session = new GameSession([
            'available_minutes'   => 5,
            'started_at'          => now()->subMinutes(6),
            'total_pause_seconds' => 0,
            'status'              => 'active',
        ]);

        $this->assertTrue(SessionTimer::isExpired($session));
    }
}
