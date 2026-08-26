<?php
/**
 * TOTP Handler Class
 *
 * @package   StudipAttendance\Helpers
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\Helpers;

use Config;

class TOTPHandler
{
    /**
     * @var int default time window.
     */
    const DEFAULT_TIMEWINDOW = 30;

    /**
     * @var int number of token digits.
     */
    const DEFAULT_TOKEN_DIGITS = 6;

    /**
     * Generates a Time-based One-Time Password (TOTP).
     *
     * @param string    $secret     The shared secret (qr_seed).
     * @param int|null  $time       Specific timestamp (defaults to current server time).
     * @param int|null  $timeWindow Time window in seconds (default to config (30s)).
     * @param int|null  $digits     Length of the output code (default 6).
     * @return string               The padded TOTP code.
     */
    public static function generateTOTP(
        string $secret,
        ?int $time = null,
        ?int $timeWindow = null,
        ?int $digits = null,
    ): string {
        $time = $time ?? time();
        $timeWindow = $timeWindow ?? self::getTimeWindow();
        $digits = $digits ?? self::DEFAULT_TOKEN_DIGITS;

        // 1. Calculate the time counter (8-byte binary integer)
        $counter = (int) floor($time / $timeWindow);
        $binaryCounter = pack('N*', 0) . pack('N*', $counter); // 64-bit big-endian integer

        // 2. Generate HMAC-SHA1 hash using the seed and binary counter
        $hash = hash_hmac('sha1', $binaryCounter, $secret, true);

        // 3. Dynamic Truncation
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $dbc = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        );

        // 4. Compute mod 10^digits and format with leading zeros
        $otp = $dbc % (10 ** $digits);
        return str_pad((string) $otp, $digits, '0', STR_PAD_LEFT);
    }

    /**
     * Verifies an incoming token against the expected TOTP.
     *
     * @param string    $token      The short code / scanned token provided by the user.
     * @param string    $secret     he session's qr_seed.
     * @param int       $window     Discrepancy tolerance (1 means ±1 time step allowed).
     * @param int|null  $timeWindow Time window in seconds (default to config (30s)).
     * @return bool
     */
    public static function verifyTOTP(
        string $token,
        string $secret,
        int $window = 1,
        ?int $timeWindow = null
    ): bool {
        $currentTime = time();
        $timeWindow = $timeWindow ?? self::getTimeWindow();

        // Check current interval, previous intervals, and future intervals
        for ($i = -$window; $i <= $window; $i++) {
            $evalTime = $currentTime + ($i * $timeWindow);
            $checkingToken = self::generateTOTP($secret, $timeWindow, strlen($token), $evalTime);
            if (hash_equals($checkingToken, $token)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the TOTP Time Window in Seconds.
     * @return int
     */
    public static function getTimeWindow(): int
    {
        return (int) (Config::get()->ATTENDANCE_TOTP_TIMEWINDOW ?? self::DEFAULT_TIMEWINDOW);
    }
}
