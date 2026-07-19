<?php

/**
 * Get the application name from environment variable
 * 
 * @return string The application name
 */
if (!function_exists('app_name')) {
    function app_name(): string
    {
        // Using a static variable to cache the result for the duration of the request
        static $cachedAppName = null;
        if ($cachedAppName !== null) {
            return $cachedAppName;
        }

        // Check if it's already set in Config\App (which now has its own caching)
        try {
            // Attempt to get from Config\App if it exists and has been initialized
            // Note: In some contexts, config('App') might cause recursion if called from within App constructor,
            // but we're usually calling this from views.
            $appConfig = config('App');
            // If the App config has a name property, use it. 
            // Our refactored App constructor doesn't set a public $name property by default
            // but we can check the dbSettings logic if we wanted to.
            // For now, let's keep it simple and check DB if not in env.
        } catch (\Throwable $e) {}

        if (($envName = env('app.name')) !== null) {
            $cachedAppName = $envName;
            return $cachedAppName;
        }

        // During setup, the database might not be ready or tables might be missing.
        // We check if the table exists before attempting to query it.
        try {
            $db = \Config\Database::connect();
            if ($db->tableExists('configuration')) {
                $configModel = new \App\Models\ConfigurationModel();
                $cachedAppName = $configModel->getParam('app.name', 'Media Organizer');
                return $cachedAppName;
            }
        } catch (\Throwable $e) {
            // Fallback to default
        }

        $cachedAppName = 'Media Organizer';
        return $cachedAppName;
    }
}

/**
 * Determine whether every IP address a hostname resolves to is a public,
 * routable address (not loopback, private, link-local, or otherwise
 * reserved). A literal IP is validated directly.
 *
 * Used to block SSRF via user-supplied poster URLs that could otherwise
 * reach internal services (e.g. 127.0.0.1, 169.254.169.254, RFC1918).
 *
 * @param string $host
 * @return bool
 */
if (!function_exists('is_public_host')) {
    function is_public_host(string $host): bool
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
        }

        // Cache per-request: the same host is often re-validated across
        // redirect hops and across multiple poster candidates in one request.
        static $cache = [];
        if (array_key_exists($host, $cache)) {
            return $cache[$host];
        }

        // gethostbynamel() (A records only) goes through the system
        // resolver/cache and is fast in practice. Querying AAAA up front via
        // dns_get_record($host, DNS_A + DNS_AAAA) is dramatically slower in
        // many environments -- the AAAA half can take several seconds to
        // time out when there's no IPv6 route -- so only fall back to an
        // explicit AAAA lookup when the (quick) A lookup finds nothing.
        $ips = @gethostbynamel($host);

        if ($ips === false || empty($ips)) {
            $records = @dns_get_record($host, DNS_AAAA);
            $ips = array_filter(array_map(function ($record) {
                return $record['ipv6'] ?? null;
            }, $records ?: []));
        }

        if (empty($ips)) {
            // Fail closed: if we can't resolve it, we can't prove it's safe.
            return $cache[$host] = false;
        }

        foreach ($ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $cache[$host] = false;
            }
        }

        return $cache[$host] = true;
    }
}

/**
 * True if a URL is an absolute http(s) URL whose host resolves only to
 * public addresses. Rejects non-http(s) schemes, missing hosts, and any
 * host resolving to a private/loopback/reserved address.
 *
 * @param string $url
 * @return bool
 */
if (!function_exists('is_safe_remote_image_url')) {
    function is_safe_remote_image_url(string $url): bool
    {
        $parts = parse_url($url);
        if ($parts === false || empty($parts['host'])) {
            return false;
        }

        $scheme = strtolower($parts['scheme'] ?? '');
        if (!in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        return is_public_host($parts['host']);
    }
}

/**
 * Safely fetch a remote image, validating the host (public/routable only)
 * before every request, including redirect targets, so a malicious server
 * can't bypass the check by 30x-redirecting to an internal address.
 *
 * @param string $url
 * @param string $userAgent
 * @param int $maxRedirects
 * @return string|null Raw response body, or null on failure/blocked URL
 */
if (!function_exists('fetch_remote_image')) {
    function fetch_remote_image(string $url, string $userAgent = 'MediaOrganizer/1.0', int $maxRedirects = 5): ?string
    {
        $current = $url;

        for ($hop = 0; $hop <= $maxRedirects; $hop++) {
            if (!is_safe_remote_image_url($current)) {
                log_message('error', 'fetch_remote_image: blocked unsafe or invalid URL: ' . $current);
                return null;
            }

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $current,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_USERAGENT => $userAgent,
                CURLOPT_HTTPHEADER => ['Accept: image/*'],
            ]);

            $body = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($body === false) {
                log_message('error', 'fetch_remote_image: request failed for ' . $current . ': ' . $error);
                return null;
            }

            if (in_array($httpCode, [301, 302, 303, 307, 308], true)) {
                $location = curl_getinfo($ch, CURLINFO_REDIRECT_URL) ?: null;
                if (empty($location)) {
                    log_message('error', 'fetch_remote_image: redirect with no Location for ' . $current);
                    return null;
                }
                $current = $location;
                continue;
            }

            if ($httpCode === 200) {
                return $body;
            }

            log_message('error', 'fetch_remote_image: unexpected status ' . $httpCode . ' for ' . $current);
            return null;
        }

        log_message('error', 'fetch_remote_image: too many redirects for ' . $url);
        return null;
    }
}

/**
 * Add TMDB, IMDB, IGDB, and/or MusicBrainz IDs to notes field using tag format
 * Tags: <!tmdb>xxxx, <!imdb>xxxx, <!tvdb>xxxx, <!igdb>xxxx, <!mbid>xxxx, <!source>xxxx
 * 
 * @param string|null $existingNotes The current notes content
 * @param array $externalIds Associative array of IDs (tmdb, imdb, tvdb, igdb, mbid, source)
 * @return string The updated notes with IDs embedded
 */
if (!function_exists('add_external_ids_to_notes')) {
    function add_external_ids_to_notes(?string $existingNotes, array $externalIds = []): string
    {
        $notes = $existingNotes ?? '';
        
        // Remove any existing tags to avoid duplicates
        $tagsToClear = ['tmdb', 'imdb', 'tvdb', 'igdb', 'mbid', 'source'];
        foreach ($tagsToClear as $tag) {
            $notes = preg_replace('/<!' . $tag . '>[^\n]*\n?/', '', $notes);
        }
        
        // Trim the remaining notes
        $notes = trim($notes);
        
        // Add new tags at the beginning, each followed by a newline
        $tagsContent = '';
        foreach ($externalIds as $tag => $id) {
            if ($id !== null && $id !== '') {
                $tagsContent .= '<!' . strtolower($tag) . '>' . $id . "\n";
            }
        }
        
        return $tagsContent . $notes;
    }
}

if (!function_exists('get_external_id_from_notes')) {
    function get_external_id_from_notes(?string $notes, string $tag): ?string
    {
        if ($notes === null || $notes === '') {
            return null;
        }
        
        if (preg_match('/<!' . preg_quote($tag, '/') . '>(\S+)/', $notes, $matches)) {
            return $matches[1];
        }
        
        return null;
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
        return get_external_id_from_notes($notes, 'tmdb');
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
        return get_external_id_from_notes($notes, 'imdb');
    }
}

/**
 * Extract TVDB ID from notes field
 */
if (!function_exists('get_tvdb_id_from_notes')) {
    function get_tvdb_id_from_notes(?string $notes): ?string
    {
        return get_external_id_from_notes($notes, 'tvdb');
    }
}

/**
 * Extract IGDB ID from notes field
 */
if (!function_exists('get_igdb_id_from_notes')) {
    function get_igdb_id_from_notes(?string $notes): ?string
    {
        return get_external_id_from_notes($notes, 'igdb');
    }
}

/**
 * Extract MusicBrainz ID from notes field
 */
if (!function_exists('get_mbid_from_notes')) {
    function get_mbid_from_notes(?string $notes): ?string
    {
        return get_external_id_from_notes($notes, 'mbid');
    }
}

/**
 * Extract Lookup Source from notes field
 */
if (!function_exists('get_source_from_notes')) {
    function get_source_from_notes(?string $notes): ?string
    {
        return get_external_id_from_notes($notes, 'source');
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
        
        // Remove all supported external ID tags
        $tags = ['tmdb', 'imdb', 'tvdb', 'igdb', 'mbid', 'source'];
        $clean = $notes;
        foreach ($tags as $tag) {
            $clean = preg_replace('/<!' . $tag . '>\S+\s*/', '', $clean);
        }
        
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
if (!function_exists('get_image_dimensions')) {
    /**
     * Get dimensions of an image from a URL
     * 
     * @param string $url The image URL
     * @return array [width, height] or [null, null] on failure
     */
    function get_image_dimensions(string $url): array
    {
        try {
            // Use getimagesize on the URL
            // Note: This might be slow and depends on allow_url_fopen
            $dimensions = @getimagesize($url);
            if ($dimensions) {
                return [(int)$dimensions[0], (int)$dimensions[1]];
            }
        } catch (\Throwable $e) {
            log_message('debug', 'Could not get dimensions for: ' . $url . ' - ' . $e->getMessage());
        }
        
        return [null, null];
    }
}

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
        try {
            $db = \Config\Database::connect();
            if ($db->tableExists('tags')) {
                $mediaModel = new \App\Models\MediaModel();
                return $mediaModel->getTagByName('wishlist');
            }
        } catch (\Throwable $e) {
            // Silently fail during setup or if database is not ready
        }
        return null;
    }
}
