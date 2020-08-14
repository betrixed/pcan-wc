<?php

function str_contains(string $haystack, string $needle): bool
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }

function str_starts_with(string $haystack, string $needle): bool
    {
        return 0 === \strncmp($haystack, $needle, \strlen($needle));
    }

function str_ends_with(string $haystack, string $needle): bool
    {
        return '' === $needle || ('' !== $haystack && 0 === \substr_compare($haystack, $needle, -\strlen($needle)));
    }
