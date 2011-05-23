<?php

/**
 * The function limits string till the last space character (" ")
 *
 * @param string $str
 * @param number $number
 * @return string
 */
function limit_string($str, $number) {
    if (strlen($str) < $number) {
        return $str;
    }

    // return the cutted text with "..."
    return substr($str, 0, strrpos($str, " ", -1 * (strlen($str) - $number + 1))) . ' ...';
}
