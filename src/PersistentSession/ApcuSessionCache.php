<?php

namespace INTERMediator\FileMakerServer\RESTAPI\PersistentSession;

use RuntimeException;

class ApcuSessionCache implements SessionCacheInterface
{
  public function __construct()
  {
    if (!function_exists('apcu_fetch')) {
      throw new RuntimeException("APCu is required to use ApcuSessionCache.");
    }
  }

  public function get(string $key): string|false
  {
    $value = apcu_fetch($key);
    return is_string($value) ? $value : false;
  }

  public function set(string $key, string $value, int $ttl): void
  {
    apcu_store($key, $value, $ttl);
  }

  public function delete(string $key): void
  {
    apcu_delete($key);
  }
}
