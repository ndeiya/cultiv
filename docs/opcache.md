# PHP OPcache Recommendations

OPcache improves PHP performance by storing precompiled script bytecode in shared memory, thereby removing the need for PHP to load and parse scripts on each request.

## Recommended php.ini Settings

For a production environment hosting Cultiv, add or modify the following settings in your `php.ini`:

```ini
; Enable OPcache
zend_extension=opcache
opcache.enable=1

; How much memory to allocate for compiled code (adjust based on RAM)
opcache.memory_consumption=128

; The amount of memory used to store interned strings
opcache.interned_strings_buffer=8

; The maximum number of keys (files) allowed in the OPcache hash table
opcache.max_accelerated_files=10000

; How often to check file timestamps for changes (0 = never check, 60 = check every minute)
; In production, 0 is best for performance if you clear cache manually on deploy.
opcache.revalidate_freq=2

; Save comments (essential for some libraries, though not strictly required for Cultiv)
opcache.save_comments=1

; Fast shutdown (improves request finishing speed)
opcache.fast_shutdown=1
```

## How to Verify
You can verify if OPcache is active by running:
```bash
php -i | grep opcache
```
Or by creating a `phpinfo()` page and searching for "Zend OPcache".
