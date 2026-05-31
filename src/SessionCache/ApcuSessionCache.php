<?php

declare(strict_types=1);

namespace INTERMediator\FileMakerServer\RESTAPI\SessionCache;

use RuntimeException;

/**
 * APCu-based session cache implementation.
 *
 * Caches FileMaker Data API session tokens using APCu, which stores data in
 * shared memory on the server. Note that depending on your setup APCu cache
 * may be shared across PHP processes on the same server, so cache keys must be
 * sufficiently unique to avoid collisions between different users and applications.
 *
 * Requires that the APCu extension is installed and enabled. See the
 * documentation here for more information: https://www.php.net/apcu
 *
 * As this cache stores sensitive FileMaker Data API session tokens, APCu is
 * only appropriate in environments where server memory access is properly
 * restricted.
 *
 * Note that cache operations in this implementation are not atomic. While care
 * has been taken to minimize the risk of race conditions, concurrent requests
 * sharing the same cache key may occasionally result in redundant
 * re-authentication against the FileMaker Server. This is considered an
 * acceptable trade-off given the constraints of the current implementation.
 *
 * @package INTER-Mediator\FileMakerServer\RESTAPI\SessionCache
 * @link https://github.com/msyk/FMDataAPI GitHub Repository
 * @version 37
 */
class ApcuSessionCache extends AbstractSessionCache
{
    /**
     * ApcuSessionCache constructor.
     * @throws RuntimeException If APCu is not available.
     */
    public function __construct()
    {
        parent::__construct();
        if (!function_exists('apcu_enabled') || !apcu_enabled()) {
            throw new RuntimeException("APCu is required to use ApcuSessionCache.");
        }
    }

    /**
     * Retrieves the cached FileMaker Data API session token for the current session.
     *
     * @return string|null The cached session token, or null if no token exists
     *                     for the current key.
     */
    public function get(): string|null
    {
        $value = apcu_fetch($this->key, $success);
        return $success && is_string($value) ? $value : null;
    }

    /**
     * Persists a FileMaker Data API session token in APCu.
     *
     * @param string $value The FileMaker Data API session token to store.
     *                      This is a sensitive credential and must be treated as such.
     * @return bool True on success, false on failure.
     */
    public function set(string $value): bool
    {
        return apcu_store($this->key, $value, $this->ttl);
    }

    /**
     * Deletes the cached FileMaker Data API session token.
     *
     * Returns false both when the key does not exist and when deletion fails.
     *
     * @return bool True on success, false if the key did not exist or deletion failed.
     */
    public function delete(): bool
    {
        return apcu_delete($this->key);
    }
}
