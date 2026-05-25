<?php

declare(strict_types=1);

namespace INTERMediator\FileMakerServer\RESTAPI\SessionCache;

/**
 * Base class for session cache implementations.
 *
 * Provides the cache key and TTL to concrete implementations, both of which
 * are managed internally by the library. The cache key and TTL will not change
 * during a single PHP request.
 *
 * As this cache stores FileMaker Data API session tokens, which are sensitive
 * credentials granting full API access on behalf of the authenticated user,
 * implementors must ensure that the underlying cache storage is secure and
 * not accessible to unauthorized parties.
 *
 * To provide a custom cache backend, extend this class and implement
 * {@see SessionCacheInterface::get()}, {@see SessionCacheInterface::set()},
 * and {@see SessionCacheInterface::delete()}, using {@see self::$key}
 * and {@see self::$ttl} in your implementations.
 *
 * @see ApcuSessionCache for an example implementation using APCu.
 * @see SessionCacheInterface for an alternative way to implement session caching without
 * extending this class.
 */
abstract class AbstractSessionCache implements SessionCacheInterface
{
    /**
     * The cache key for the current session.
     *
     * Always set by the library via {@see SessionCacheInterface::setKey()} before any cache
     * operation is performed. Will not change during a single PHP request.
     * Implementing classes should use this property directly in their
     * {@see SessionCacheInterface::get()}, {@see SessionCacheInterface::set()},
     * and {@see SessionCacheInterface::delete()} implementations.
     */
    protected string $key;

    /**
     * The time-to-live in seconds for cached session tokens.
     *
     * Set by the library via {@see SessionCacheInterface::setTtl()} before any cache operation
     * is performed, defaulting to the value provided at construction time.
     * Will not change during a single PHP request. Implementing classes should
     * use this property directly in their {@see SessionCacheInterface::set()} implementation.
     *
     */
    protected int $ttl;

    /**
     * @param int $defaultTtl Default time-to-live in seconds for cached session tokens.
     *                        Defaults to 840 seconds (14 minutes), reflecting the
     *                        default FileMaker Data API session timeout. Adjust this
     *                        value if your FileMaker Server is configured with a
     *                        different session timeout.
     */
    public function __construct(int $defaultTtl = 840)
    {
        $this->ttl = $defaultTtl;
    }

    final public function setKey(string $key): void
    {
        $this->key = $key;
    }

    final public function setTtl(int $ttl): void
    {
        $this->ttl = $ttl;
    }
}
