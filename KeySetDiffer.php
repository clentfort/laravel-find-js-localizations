<?php

namespace clentfort\LaravelFindJsLocalizations;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class KeySetDiffer
{
    /**
     * Builds a list of keys that are missing in the given KeySet.
     *
     * @param Collection $keys   A collection of $keys to
     *                           check the translation status
     *                           for
     * @param KeySet     $keySet The KeySet used to read files
     *
     * @return Collection A nested collection of untranslated keys. The
     *                    structure is `[prefix => [keys]]`. Where `prefix` is a
     *                    common prefix shared by all keys in that collection.
     *                    I.e for the string `some.string` and
     *                    `some.otherstring` the collection would look liked
     *                    `['some' => ['string', 'otherstring']]`.
     */
    public static function diffKeys(
        Collection $keys,
        KeySet $keySet
    ) {
        // Translations are stored in "prefix"-files. This means a key
        // `some.string` will be saved in `some.php` with the index `string`.
        $groupedKeys = static::groupKeysByPrefix($keys);

        return $groupedKeys->mapWithKeys(
            function ($keys, $prefix) use ($keySet) {
                $currentKeys = $keySet->getKeysWithPrefix($prefix);

                return [
                    $prefix => $keys->flip()->diffKeys($currentKeys)->keys(),
                ];
            }
        )->filter(function ($newKeys) {
            return $newKeys->isNotEmpty();
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
