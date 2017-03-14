<?php

namespace clentfort\LaravelFindJsLocalizations;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TranslationKeysManager
{
    /**
     * Builds a list of untranslated keys in $keys per language.
     *
     * @param Collection        $keys              A collection of $keys to
     *                                             check the translation status
     *                                             for
     * @param PrefixFileManager $prefixFileManager The PrefixFileManager used to
     *                                             read prefix-files
     *
     * @return Collection A nested collection of untranslated keys. The
     *                    structure is `[prefix => [keys]]`. Where `prefix` is a
     *                    common prefix shared by all keys in that collection.
     *                    I.e for the string `some.string` and
     *                    `some.otherstring` the collection would look liked
     *                    `['some' => ['string', 'otherstring']]`.
     */
    public static function determineUntranslatedKeys(
        Collection $keys,
        PrefixFileManager $prefixFileManager
    ) {
        // Translations are stored in "prefix"-files. This means a key
        // `some.string` will be saved in `some.php` with the index `string`.
        $groupedKeys = static::groupKeysByPrefix($keys);

        return $groupedKeys->mapWithKeys(
            function ($keys, $prefix) use ($prefixFileManager) {
                $translatedKeys = $prefixFileManager->readPrefixFile($prefix);

                return [
                    $prefix => $keys->flip()->diffKeys($translatedKeys)->keys(),
                ];
            }
        )->filter(function ($missing) {
            return $missing->isNotEmpty();
        });
    }

    /**
     * Groups the keys in $keys by their common prefix and removes the prefix
     * from the elements in a group.
     *
     * @param Collection $keys
     *
     * @return Collection A collection of collections of the form
     *                    `['common_prefix' => ['key1', 'key2', ...]]`.
     */
    protected static function groupKeysByPrefix(Collection $keys)
    {
        return $keys->sort()->groupBy(function ($key) {
            return strstr($key, '.', true);
        })->map(function ($group) {
            return $group->map(function ($key) {
                return Str::substr(strstr($key, '.'), 1);
            });
        });
    }
}
