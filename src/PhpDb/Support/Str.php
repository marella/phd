<?php

namespace PhpDb\Support;

class Str
{
    /**
     * Determine if a given string contains a given substring.
     *
     * @param string       $haystack
     * @param string|array $needles
     *
     * @return bool
     */
    public static function contains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}
