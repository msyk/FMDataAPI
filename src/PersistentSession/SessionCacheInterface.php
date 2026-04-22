<?php

namespace INTERMediator\FileMakerServer\RESTAPI\PersistentSession;
interface SessionCacheInterface
{
  /**
   * Retrieve a cached token.
   * @param string $key
   * @return string|false returns the cached token, or false if the key doesn't exist.'
   */
  public function get(string $key): string|false;

  /**
   * Store a token with a TTL in seconds.
   * @param string $key
   * @param string $value
   * @param int $ttl
   */
  public function set(string $key, string $value, int $ttl): void;

  /**
   * Delete a cached token.
   * @param string $key
   */
  public function delete(string $key): void;
}
