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

$languages = new Collection([
    'en' => new PrefixFileManager('en', './resources/lang/'),
    'de' => new PrefixFileManager('de', './resources/lang/'),
    'ar' => new PrefixFileManager('ar', './resources/lang/'),
]);

dd($languages->mapWithKeys(function ($prefixFileManager, $language) use ($keys) {
    $keys = TranslationKeysManager::determineUntranslatedKeys(
        $keys,
        $prefixFileManager
    );

    return [$language => $keys];
}));
