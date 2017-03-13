<?php

namespace clentfort\LaravelFindJsLocalizations;

include './vendor/autoload.php';

use Illuminate\Support\Collection;

list($sourceFileKeyList, $errors) = (new Finder('./resources/assets/js', '/usr/bin/node'))->findTranslationKeys();

$keys = $sourceFileKeyList->reduce(function ($keys, $file) {
    return $keys->merge(array_map(function ($key) {
        return $key->value;
    }, $file->keys));
}, new Collection());

$manager = new TranslationsManager(['en', 'de'], './resources/lang/');
dd($manager->determineUntranslatedKeys($keys));
