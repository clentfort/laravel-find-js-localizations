<?php

namespace clentfort\LaravelFindJsLocalizations;

include './vendor/autoload.php';

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Builds a list of translations keys from a file a sourceFileKeyList.
 *
 * @param array $sourceFileKeyList
 *
 * @return array
 */
function sourceFileKeyListToTranslationFileKeyMap($sourceFileKeyList)
{
    return $sourceFileKeyList->reduce(function ($keys, $file) {
        return $keys->merge(array_map(function ($key) {
            return $key->value;
        }, $file->keys));
    }, new Collection())->groupBy(function ($key) {
        return strstr($key, '.', true);
    })->map(function ($file) {
        return $file->map(function ($key) {
            return Str::substr(strstr($key, '.'), 1);
        });
    });
}

/**
 * Receives the available languages by looking at the top level directories in
 * $directory.
 *
 * @param string $directory
 *
 * @return Collection
 */
function getLanguageDirectories($directory)
{
    $filesystem = new Filesystem();

    return new Collection($filesystem->directories($directory));
}

list($sourceFileKeyList, $errors) = (new Finder('./resources/assets/js', '/usr/bin/node'))->findTranslationKeys();
$translationFileKeyMap = sourceFileKeyListToTranslationFileKeyMap($sourceFileKeyList);
$translationFiles = $translationFileKeyMap->keys();
$languageDirs = getLanguageDirectories('./resources/lang');

$filesystem = new Filesystem();
$translationFileKeyMap->each(function ($keys, $translationFile) use ($languageDirs, $filesystem) {
    $languageDirs->each(function ($languageDir) use ($translationFile, $filesystem, $keys) {
        $path = $languageDir.DIRECTORY_SEPARATOR.$translationFile.'.php';
        if ($filesystem->exists($path)) {
            $translations = new Collection(Arr::dot(include($path)));
            $missing = $keys->flip()->diffKeys($translations);
            if ($missing->count() > 0) {
                var_dump($path);
                var_dump($missing->all());
            }
        }
    });
});
