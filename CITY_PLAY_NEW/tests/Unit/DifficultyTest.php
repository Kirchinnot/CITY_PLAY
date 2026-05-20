<?php

namespace Tests\Unit;

use App\Support\Difficulty;
use PHPUnit\Framework\TestCase;

class DifficultyTest extends TestCase
{
    public function test_score_points_for_all_riddle_levels(): void
    {
        $this->assertSame(50, Difficulty::scorePoints(Difficulty::ENFANT));
        $this->assertSame(100, Difficulty::scorePoints(Difficulty::FACILE));
        $this->assertSame(150, Difficulty::scorePoints(Difficulty::MOYEN));
        $this->assertSame(200, Difficulty::scorePoints(Difficulty::DIFFICILE));
    }

    public function test_session_levels_are_subset_of_riddle_levels_except_enfant_only_on_riddles(): void
    {
        foreach (Difficulty::SESSION_LEVELS as $level) {
            $this->assertContains($level, Difficulty::RIDDLE_LEVELS);
        }
    }
}
