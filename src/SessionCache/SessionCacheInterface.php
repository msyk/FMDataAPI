<?php

declare(strict_types=1);

namespace INTERMediator\FileMakerServer\RESTAPI\SessionCache;

/**
 * Interface for session cache implementations.
 *
 * Implementations of this interface are used internally by the library to cache
 * FileMaker Data API session tokens. These tokens are sensitive credentials that
 * grant access to the FileMaker Data API on behalf of the authenticated user.
 * Implementors must ensure that cached values are stored securely and are not
 * accessible to unauthorized parties.
 *
 * This interface should not be implemented directly. Instead, extend
 * {@see AbstractSessionCache}, which provides the cache key and TTL management
 * required by this interface, and implement the three cache methods.
 *
 * @see ApcuSessionCache for an example implementation using APCu.
 * @see AbstractSessionCache for an easier way to implement session caching, but
 * this interface is still useful for custom implementations.
 */
interface SessionCacheInterface
{
    /**
     * Retrieves the cached FileMaker Data API session token for the current session.
     *
     * The cache key is managed internally by the library and set via
     * {@see setKey()} prior to this method being called.
     *
     * @return string|null The cached session token, or null if no token exists
     *                     for the current key.
     */
    public function get(): ?string;

    /**
     * Persists a FileMaker Data API session token in the cache.
     *
     * The value being stored is a sensitive FileMaker Data API session token.
     * Implementors must ensure this value is stored securely and protected from
     * unauthorized access, as it grants access to the FileMaker Data API on behalf
     * of the authenticated user.
     *
     * The cache key and TTL are managed internally by the library and set via
     * {@see setKey()} and {@see setTtl()}
     * prior to this method being called.
     *
     * @param string $value The FileMaker Data API session token to store.
     *                      This is a sensitive credential and must be treated as such.
     * @return bool True on success, false on failure.
     */
    public function set(string $value): bool;

    /**
     * Deletes the cached FileMaker Data API session token for the current session.
     *
     * The cache key is managed internally by the library and set via
     * {@see setKey()} prior to this method being called.
     *
     * @return bool True on success, false if the key did not exist or deletion failed.
     */
    public function delete(): bool;

    /**
     * Sets the cache key for the current session.
     *
     * This method is called internally by the library and should not be called
     * manually. The key will not change during a single PHP request.
     *
     * @param string $key The cache key to use for subsequent cache operations.
     */
    public function setKey(string $key): void;


    /**
     * Sets the time-to-live for cached session tokens.
     *
     * This method is called internally by the library and should not be called
     * manually. The TTL will not change during a single PHP request.
     *
     * @param int $ttl Time-to-live in seconds for the cached session token.
     */
    public function setTtl(int $ttl): void;
}
