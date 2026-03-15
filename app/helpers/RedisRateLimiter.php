<?php
/**
 * Redis Rate Limiter
 * Redis-based rate limiting for high-performance applications.
 * Falls back to file-based rate limiting if Redis is unavailable.
 */

class RedisRateLimiter
{
    private ?\Predis\Client $redis = null;
    private RateLimiter $fallbackLimiter;
    private bool $redisAvailable = false;

    public function __construct()
    {
        // Try to connect to Redis
        try {
            $this->redis = new \Predis\Client([
                'scheme' => 'tcp',
                'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                'port' => $_ENV['REDIS_PORT'] ?? 6379,
                'password' => $_ENV['REDIS_PASSWORD'] ?? null,
            ], [
                'parameters' => [
                    'password' => $_ENV['REDIS_PASSWORD'] ?? null,
                ]
            ]);
            
            // Test connection
            $this->redis->ping();
            $this->redisAvailable = true;
        } catch (Throwable $e) {
            // Redis not available, use file-based fallback
            $this->redisAvailable = false;
            $this->fallbackLimiter = new RateLimiter();
        }
    }

    /**
     * Check if a key (e.g. IP address or user_id) is rate-limited.
     *
     * @param string $key           Identifier (e.g. IP address or user_id)
     * @param int    $maxAttempts   Maximum attempts allowed
     * @param int    $windowSeconds Time window in seconds
     * @return bool True if the request is allowed, false if rate-limited
     */
    public function check(string $key, int $maxAttempts = 5, int $windowSeconds = 60): bool
    {
        if (!$this->redisAvailable) {
            return $this->fallbackLimiter->check($key, $maxAttempts, $windowSeconds);
        }

        try {
            $redisKey = "rate_limit:{$key}";
            
            // Get current count
            $current = $this->redis->get($redisKey);
            
            if ($current === null) {
                // First request in window
                $this->redis->setex($redisKey, $windowSeconds, 1);
                return true;
            }
            
            if ((int)$current >= $maxAttempts) {
                // Rate limit exceeded
                return false;
            }
            
            // Increment counter
            $this->redis->incr($redisKey);
            return true;
        } catch (Throwable $e) {
            // Redis error, fallback to file-based
            return $this->fallbackLimiter->check($key, $maxAttempts, $windowSeconds);
        }
    }

    /**
     * Record a failed attempt for a key.
     */
    public function increment(string $key, int $windowSeconds = 60): void
    {
        if (!$this->redisAvailable) {
            $this->fallbackLimiter->increment($key);
            return;
        }

        try {
            $redisKey = "rate_limit:{$key}";
            $this->redis->incr($redisKey);
            $this->redis->expire($redisKey, $windowSeconds);
        } catch (Throwable $e) {
            $this->fallbackLimiter->increment($key);
        }
    }

    /**
     * Clear all attempts for a key (e.g. on successful login).
     */
    public function clear(string $key): void
    {
        if (!$this->redisAvailable) {
            $this->fallbackLimiter->clear($key);
            return;
        }

        try {
            $redisKey = "rate_limit:{$key}";
            $this->redis->del($redisKey);
        } catch (Throwable $e) {
            $this->fallbackLimiter->clear($key);
        }
    }

    /**
     * Get remaining seconds until rate limit resets.
     */
    public function getRetryAfter(string $key): int
    {
        if (!$this->redisAvailable) {
            return $this->fallbackLimiter->getRetryAfter($key);
        }

        try {
            $redisKey = "rate_limit:{$key}";
            $ttl = $this->redis->ttl($redisKey);
            return max(0, $ttl);
        } catch (Throwable $e) {
            return $this->fallbackLimiter->getRetryAfter($key);
        }
    }
}
