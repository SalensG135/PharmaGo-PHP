<?php

declare(strict_types=1);

namespace PharmaGo\Core;

final class Env
{
    /** @var array<string,string> */
    private static array $vars = [];

    public static function load(string $file): void
    {
        if (!is_readable($file)) {
            return;
        }
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            if ($line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            self::$vars[trim($k)] = trim($v);
        }
    }

    public static function get(string $key, string $default = ''): string
    {
        return self::$vars[$key] ?? (getenv($key) !== false ? (string) getenv($key) : $default);
    }
}
