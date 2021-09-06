<?php

namespace App;

use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle): bool
    {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle): bool
    {
        return $needle !== '' && substr($haystack, -strlen($needle)) === (string)$needle;
    }
}

final class TwigExtension extends AbstractExtension
{
    public function __construct()
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('strip', [$this, 'stripText']),
        ];
    }

    /**
     * Replaces strings within a string.
     *
     * @param string    $str  String to replace in
     * @param string    $search Replace values
     * @param string    $side accept: both, left, right
     * @return string
     *
     * @throws RuntimeError When an invalid trimming side is used (not a string or not 'left', 'right', or 'both')
     */
    public function stripText(string $str, string $search, string $side = 'both'): string
    {
        switch ($side) {
            case 'both':
                // return $this->after($this->replaceLast($search, '', $str), $search);
                return $this->replaceFirst($search, '', $this->replaceLast($search, '', $str));
            case 'left':
                // return $this->after($str, $search);
                return $this->replaceFirst($search, '', $str);
            case 'right':
                return $this->replaceLast($search, '', $str);
            default:
                throw new RuntimeError('Strip side must be "left", "right" or "both".');
        }
    }

    /**
     * Return the remainder of a string after the first occurrence of a given value.
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    private function after(string $subject, string $search): string
    {
        if (!str_starts_with($subject, $search)) {
            return $subject;
        }

        if ($search === '') {
            return $subject;
        }

        return array_reverse(explode($search, $subject, 2))[0];
    }

    /**
     * Replace the first occurrence of a given value in the string.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $subject
     * @return string
     */
    private function replaceFirst(string $search, string $replace, string $subject): string
    {
        if (!str_starts_with($subject, $search)) {
            return $subject;
        }

        $search = (string) $search;

        if ($search === '') {
            return $subject;
        }

        $position = strpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    /**
     * Replace the last occurrence of a given value in the string.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $subject
     * @return string
     */
    private function replaceLast(string $search, string $replace, string $subject): string
    {
        if (!str_ends_with($subject, $search)) {
            return $subject;
        }

        if ($search === '') {
            return $subject;
        }

        $position = strrpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }
}
