<?php

namespace App\Services\Validation;

class ValidationResponseService
{
    public const INVALID = 1;
    public const PRIVATE = 2;
    public const PUBLIC = 3;

    /**
     * Determine numeric response code for a user validation result.
     *
     * @param string|null $userSteamID
     * @param bool|null $isPublicProfile
     * @return int
     */
    public static function determine(?string $userSteamID = null, ?bool $isPublicProfile = false): int
    {
        if (empty($userSteamID)) {
            return self::INVALID;
        }

        if (!$isPublicProfile) {
            return self::PRIVATE;
        }

        return self::PUBLIC;
    }
}