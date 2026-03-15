<?php
/**
 * Rate Limiter
 * File-based rate limiting for login and other sensitive endpoints.
 */

class RateLimiter
{
    private string $storageDir;

    public function __construct()
    {
        $this->storageDir = STORAGE_PATH . '/rate_limits';
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    /**
     * Check if a key (e.g. IP address) is rate-limited.
     *
     * @param string $key           Identifier (e.g. IP address)
     * @param int    $maxAttempts   Maximum attempts allowed
     * @param int    $windowSeconds Time window in seconds
     * @return bool True if the request is allowed, false if rate-limited
     */
    public function check(string $key, int $maxAttempts = 5, int $windowSeconds = 900): bool
    {
        $file = $this->getFilePath($key);

        if (!file_exists($file)) {
            return true;
        }

        $data = json_decode(file_get_contents($file), true);
        if (!$data) {
            return true;
        }

        // Clean up expired attempts
        $cutoff = time() - $windowSeconds;
        $data['attempts'] = array_filter($data['attempts'] ?? [], fn($ts) => $ts > $cutoff);

        // Write cleaned data back
        file_put_contents($file, json_encode($data), LOCK_EX);

        return count($data['attempts']) < $maxAttempts;
    }

    /**
     * Record a failed attempt for a key.
     */
    public function increment(string $key): void
    {
        $file = $this->getFilePath($key);

        $data = ['attempts' => []];
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?: ['attempts' => []];
        }

        $data['attempts'][] = time();

        file_put_contents($file, json_encode($data), LOCK_EX);
    }

    /**
     * Clear all attempts for a key (e.g. on successful login).
     */
    public function clear(string $key): void
    {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Get remaining seconds until rate limit resets.
     */
    public function getRetryAfter(string $key, int $windowSeconds = 900): int
    {
        $file = $this->getFilePath($key);
        if (!file_exists($file)) {
            return 0;
        }

        $data = json_decode(file_get_contents($file), true);
        if (empty($data['attempts'])) {
            return 0;
        }

        $oldest = min($data['attempts']);
        $resetAt = $oldest + $windowSeconds;
        $remaining = $resetAt - time();

        return max(0, $remaining);
    }

    /**
     * Get the file path for a rate limit key.
     */
    private function getFilePath(string $key): string
    {
        // Sanitize key to a safe filename
        $safeKey = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $key);
        return $this->storageDir . '/' . $safeKey . '.json';
    }
}
