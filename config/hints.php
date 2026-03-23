<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Hint Data Key to Method Mapping
    |--------------------------------------------------------------------------
    |
    | Maps data keys (used in hints.json) to their corresponding method names
    | in the HintDataService class.
    |
    */
    'key_to_method' => [
        'first_letter' => 'getFirstLetterOfGame',
        'developer' => 'getDeveloperOfGame',
        'publisher' => 'getPublisherOfGame',
        'tags' => 'getTagsOfGame',
        'playtime' => 'getPlaytimeOfGame',
        'release_date' => 'getReleaseDateOfGame',
        'banner_url' => 'getBannerUrlOfGame',
        'review_ratio' => 'getReviewRatioOfGame',
        'total_reviews' => 'getTotalReviewsOfGame',
        'required_disk_space' => 'getRequiredDiskSpaceOfGame',
        'total_owners' => 'getTotalOwnersOfGame',
        'current_players' => 'getCurrentPlayersOfGame',
    ],

    /*
    |--------------------------------------------------------------------------
    | Disk Space Regex Patterns
    |--------------------------------------------------------------------------
    |
    | Patterns used to extract required disk space from the "minimum"
    | PC requirements HTML returned by the Steam store. These are configurable
    | so they can be adjusted without editing the service code.
    |
    */
    'disk_space_patterns' => [
        '/Storage:\s*<\/strong>\s*([^<]+)/i',
        '/Storage:\s*([^<]+)/i',
        '/Hard Drive:\s*<\/strong>\s*([^<]+)/i',
        '/Hard Drive:\s*([^<]+)/i',
        '/Disk Space:\s*<\/strong>\s*([^<]+)/i',
        '/Disk Space:\s*([^<]+)/i',
        '/HDD:\s*<\/strong>\s*([^<]+)/i',
        '/HDD:\s*([^<]+)/i',
    ],
];