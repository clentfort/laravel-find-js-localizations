<?php

namespace clentfort\LaravelFindJsLocalizations;

class PathHelper {

    /**
     * Joins two pathes $a and $b with the DIRECTORY_SEPERATOR.
     *
     * @param array $paths
     *
     * @return string
     */
    public static function join(...$paths)
    {
        return implode(DIRECTORY_SEPARATOR, $paths);
    }
}
