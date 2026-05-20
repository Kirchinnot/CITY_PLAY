/** Labels et points alignés sur App\Support\Difficulty (PHP). */

export const DIFFICULTY_LABELS = {
    enfant: 'Enfant 👶',
    facile: 'Facile 🏹',
    moyen: 'Moyen 🦁',
    difficile: 'Difficile 👑',
};

export function getDifficultyLabel(level) {
    return DIFFICULTY_LABELS[level] ?? level;
}

export const SESSION_DIFFICULTIES = [
    { id: 'facile', label: 'Facile 🏹' },
    { id: 'moyen', label: 'Moyen 🦁' },
    { id: 'difficile', label: 'Difficile 👑' },
];
