<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Node.js-Executable
    |--------------------------------------------------------------------------
    | If the executable is not available on the global path the absolute path
    | can be specified.
    */
    'node_executable' => 'node',

    /*
    |--------------------------------------------------------------------------
    | Extension of JavaScript-files
    |--------------------------------------------------------------------------
    | Only consider files with the given extension as JavaScript files.
    */
    'extension' => 'js',

    /*
    |--------------------------------------------------------------------------
    | Directory where to search for translation-calls
    |--------------------------------------------------------------------------
    | Directory that will be searched for JavaScript-files, subdirectories will
    | be searched too.
    */
    'directory' => 'resources/assets/js/',

    /*
    |--------------------------------------------------------------------------
    | Default lemma used when inserting new keys
    |--------------------------------------------------------------------------
    | All new keys will have this lemma as the default translation, you can use
    | `%s` to get the key.
    */
    'lemma' => 'TODO: %s',
];
