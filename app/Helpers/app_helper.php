<?php

/**
 * Get the application name from environment variable
 * 
 * @return string The application name
 */
if (!function_exists('app_name')) {
    function app_name(): string
    {
        return env('app.name', 'Media Organizer');
    }
}

/**
 * Add TMDB and/or IMDB IDs to notes field using tag format
 * Tags: <!tmdb>xxxx and <!imdb>xxxx
 * 
 * @param string|null $existingNotes The current notes content
 * @param string|null $tmdbId The TMDB ID to store
 * @param string|null $imdbId The IMDB ID to store
 * @return string The updated notes with IDs embedded
 */
if (!function_exists('add_external_ids_to_notes')) {
    function add_external_ids_to_notes(?string $existingNotes, ?string $tmdbId, ?string $imdbId): string
    {
        $notes = $existingNotes ?? '';
        
        // Remove any existing TMDB/IMDB tags to avoid duplicates (match only tag and value on same line)
        $notes = preg_replace('/<!tmdb>[^\n]*\n?/', '', $notes);
        $notes = preg_replace('/<!imdb>[^\n]*\n?/', '', $notes);
        
        // Trim the remaining notes
        $notes = trim($notes);
        
        // Add new tags at the beginning, each followed by a newline
        $tags = '';
        if ($tmdbId !== null && $tmdbId !== '') {
            $tags .= '<!tmdb>' . $tmdbId . "\n";
        }
        if ($imdbId !== null && $imdbId !== '') {
            $tags .= '<!imdb>' . $imdbId . "\n";
        }
        
        // If we have tags and existing notes, add ONE newline separator before existing notes
        if ($tags !== '' && $notes !== '') {
            $result = $tags . $notes;
        } else {
            $result = $tags . $notes;
        }
        
        return $result;
    }
}

/**
 * Extract TMDB ID from notes field
 * 
 * @param string|null $notes The notes content
 * @return string|null The TMDB ID if found, null otherwise
 */
if (!function_exists('get_tmdb_id_from_notes')) {
    function get_tmdb_id_from_notes(?string $notes): ?string
    {
        if ($notes === null || $notes === '') {
            return null;
        }
        
        if (preg_match('/<!tmdb>(\S+)/', $notes, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}

/**
 * Extract IMDB ID from notes field
 * 
 * @param string|null $notes The notes content
 * @return string|null The IMDB ID if found, null otherwise
 */
if (!function_exists('get_imdb_id_from_notes')) {
    function get_imdb_id_from_notes(?string $notes): ?string
    {
        if ($notes === null || $notes === '') {
            return null;
        }
        
        if (preg_match('/<!imdb>(\S+)/', $notes, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}

/**
 * Remove TMDB/IMDB tags from notes for display purposes
 * 
 * @param string|null $notes The notes content with tags
 * @return string The notes without TMDB/IMDB tags
 */
if (!function_exists('strip_external_ids_from_notes')) {
    function strip_external_ids_from_notes(?string $notes): string
    {
        if ($notes === null || $notes === '') {
            return '';
        }
        
        // Remove TMDB and IMDB tags
        $clean = preg_replace('/<!tmdb>\S+\s*/', '', $notes);
        $clean = preg_replace('/<!imdb>\S+\s*/', '', $clean);
        
        return trim($clean);
    }
}

/**
 * Add multiple medium IDs to notes field using tag format
 * Tag format: <!medium_id>xxx,yyy,zzz
 * 
 * @param string|null $existingNotes The current notes content
 * @param array $mediumIds Array of medium_id integers
 * @return string The updated notes with medium IDs embedded
 */
if (!function_exists('add_medium_ids_to_notes')) {
    function add_medium_ids_to_notes(?string $existingNotes, array $mediumIds): string
    {
        $notes = $existingNotes ?? '';
        
        // Remove any existing medium_id tag to avoid duplicates (match only tag and value on same line)
        $notes = preg_replace('/<!medium_id>[^\n]*\n?/', '', $notes);
        
        // Trim the remaining notes
        $notes = trim($notes);
        
        // Filter out empty/null values and ensure integers
        $mediumIds = array_filter($mediumIds, function($id) {
            return $id !== null && $id !== '' && $id !== 0 && $id !== '0';
        });
        
        // Convert to integers and remove duplicates
        $mediumIds = array_unique(array_map('intval', $mediumIds));
        
        // Sort for consistency (highest first as per requirement)
        rsort($mediumIds);
        
        // Add new tag at the beginning if we have IDs, followed by a newline
        $tags = '';
        if (!empty($mediumIds)) {
            $tags .= '<!medium_id>' . implode(',', $mediumIds) . "\n";
        }
        
        // If we have tags and existing notes, add ONE newline separator before existing notes
        if ($tags !== '' && $notes !== '') {
            $result = $tags . $notes;
        } else {
            $result = $tags . $notes;
        }
        
        return $result;
    }
}

/**
 * Extract medium IDs from notes field
 * 
 * @param string|null $notes The notes content
 * @return array Array of medium_id integers (empty if none found)
 */
if (!function_exists('get_medium_ids_from_notes')) {
    function get_medium_ids_from_notes(?string $notes): array
    {
        if ($notes === null || $notes === '') {
            return [];
        }
        
        if (preg_match('/<!medium_id>([^<\s]+)/', $notes, $matches)) {
            // Split by comma and convert to integers
            $ids = explode(',', $matches[1]);
            $ids = array_map('intval', $ids);
            // Filter out zeros
            $ids = array_filter($ids, function($id) {
                return $id > 0;
            });
            return array_values($ids);
        }
        
        return [];
    }
}

/**
 * Get the highest medium ID from notes (for storing in medium_id field)
 * 
 * @param string|null $notes The notes content
 * @return int|null The highest medium_id if found, null otherwise
 */
if (!function_exists('get_highest_medium_id_from_notes')) {
    function get_highest_medium_id_from_notes(?string $notes): ?int
    {
        $mediumIds = get_medium_ids_from_notes($notes);
        
        if (empty($mediumIds)) {
            return null;
        }
        
        return max($mediumIds);
    }
}

/**
 * Normalize a title for fuzzy searching
 * Handles special characters, punctuation, "the" placement, and common variations
 *
 * Examples:
 * - "Tron: Legacy" and "Tron Legacy" both normalize to "tron legacy"
 * - "The Matrix" and "Matrix, The" both normalize to "matrix"
 * - "Star Wars: Episode IV - A New Hope" normalizes to "star wars episode iv a new hope"
 * - "Marvel's Spider-Man" normalizes to "marvels spider man"
 *
 * @param string|null $title The title to normalize
 * @return string The normalized title for comparison
 */
if (!function_exists('normalize_title_for_search')) {
    function normalize_title_for_search(?string $title): string
    {
        if ($title === null || $title === '') {
            return '';
        }

        // Convert to lowercase
        $normalized = mb_strtolower($title, 'UTF-8');

        // Replace common word separators with spaces
        // & (ampersand) -> "and"
        $normalized = preg_replace('/\s*&\s*/', ' and ', $normalized);

        // Remove possessives ('s) - handles both straight and curly apostrophes
        $normalized = preg_replace("/['\u{2019}]s\b/u", '', $normalized);

        // Remove all punctuation and special characters except spaces
        // This handles: colons, semicolons, hyphens, quotes, apostrophes, etc.
        $normalized = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $normalized);

        // Collapse multiple spaces into one
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        // Trim
        $normalized = trim($normalized);

        // Handle "The" at the beginning or end
        // Remove "the" from the beginning
        $normalized = preg_replace('/^the\s+/', '', $normalized);

        // Remove "the" from the end (handles "Matrix, The" -> "Matrix")
        $normalized = preg_replace('/\s+the$/', '', $normalized);

        // Remove "a" and "an" from the beginning
        $normalized = preg_replace('/^an?\s+/', '', $normalized);

        // Final trim and space collapse
        $normalized = trim($normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return $normalized;
    }
}

/**
 * Check if two titles match using fuzzy/normalized comparison
 *
 * @param string|null $title1 First title
 * @param string|null $title2 Second title
 * @return bool True if titles match when normalized
 */
if (!function_exists('titles_match')) {
    function titles_match(?string $title1, ?string $title2): bool
    {
        $norm1 = normalize_title_for_search($title1);
        $norm2 = normalize_title_for_search($title2);

        if ($norm1 === '' || $norm2 === '') {
            return false;
        }

        return $norm1 === $norm2;
    }
}

/**
 * Get the wishlist tag if it exists
 * 
 * @return array|null The wishlist tag data or null if not found
 */
if (!function_exists('get_wishlist_tag')) {
    function get_wishlist_tag(): ?array
    {
        $mediaModel = new \App\Models\MediaModel();
        return $mediaModel->getTagByName('wishlist');
    }
}
