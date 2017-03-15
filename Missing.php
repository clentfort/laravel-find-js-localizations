<?php

namespace clentfort\LaravelFindJsLocalizations;

include './vendor/autoload.php';

use Illuminate\Support\Collection;

list($sourceFileKeyList, $errors) = (new KeyFinder('./resources/assets/js', '/usr/bin/node'))->findKeys();

$keys = $sourceFileKeyList->reduce(function ($keys, $file) {
    return $keys->merge(array_map(function ($key) {
        return $key->value;
    }, $file->keys));
}, new Collection());

$keySets = new Collection([
    'en' => new KeySet('./resources/lang/en/'),
    'de' => new KeySet('./resources/lang/de/'),
    'ar' => new KeySet('./resources/lang/ar/'),
]);

dd($keySets->mapWithKeys(function ($keySet, $language) use ($keys) {
    $keys = KeySetDiffer::diffKeys($keys, $keySet);

    return [$language => $keys];
}));
