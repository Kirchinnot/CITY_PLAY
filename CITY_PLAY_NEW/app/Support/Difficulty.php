<?php

namespace App\Support;

/**
 * Niveaux de difficulté unifiés pour tout le projet.
 *
 * Énigmes (DB riddles) : enfant | facile | moyen | difficile
 * Sessions / invitations : facile | moyen | difficile
 */
class Difficulty
{
    public const ENFANT = 'enfant';
    public const FACILE = 'facile';
    public const MOYEN = 'moyen';
    public const DIFFICILE = 'difficile';

    /** Niveaux autorisés sur les énigmes */
    public const RIDDLE_LEVELS = [self::ENFANT, self::FACILE, self::MOYEN, self::DIFFICILE];

    /** Niveaux autorisés sur les sessions et invitations */
    public const SESSION_LEVELS = [self::FACILE, self::MOYEN, self::DIFFICILE];

    public static function label(string $level): string
    {
        return match ($level) {
            self::ENFANT    => 'Enfant',
            self::FACILE    => 'Facile',
            self::MOYEN     => 'Moyen',
            self::DIFFICILE => 'Difficile',
            default         => ucfirst($level),
        };
    }

    public static function shortLabel(string $level): string
    {
        return match ($level) {
            self::ENFANT    => 'Enfant 👶',
            self::FACILE    => 'Facile 🏹',
            self::MOYEN     => 'Moyen 🦁',
            self::DIFFICILE => 'Difficile 👑',
            default         => self::label($level),
        };
    }

    /** Points de base pour le scoring (GameplayLogic) */
    public static function scorePoints(string $level): int
    {
        return match ($level) {
            self::ENFANT    => 50,
            self::FACILE    => 100,
            self::MOYEN     => 150,
            self::DIFFICILE => 200,
            default         => 50,
        };
    }

    public static function isValidForRiddle(string $level): bool
    {
        return in_array($level, self::RIDDLE_LEVELS, true);
    }

    public static function isValidForSession(string $level): bool
    {
        return in_array($level, self::SESSION_LEVELS, true);
    }
}
