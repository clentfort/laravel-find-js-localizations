<?php

namespace clentfort\LaravelFindJsLocalizations;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use clentfort\LaravelFindJsLocalizations\Exceptions\RuntimeException;

class TranslationsManager
{
    /**
     * @var Illuminate\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @var Collection Collection of languages to manage by this instance
     */
    protected $languages;

    /**
     * @var string
     */
    protected $langSourceDir;

    /**
     * @param array|Collection $languages
     * @param string           $langSourceDir
     */
    public function __construct($languages, $langSourceDir, $defaultLemma = '')
    {
        $this->languages = new Collection($languages);
        $this->langSourceDir = realpath($langSourceDir);

        $this->filesystem = new Filesystem();

        $this->languages->each(function ($language) {
            $directory = $this->getLanguageDir($language);
            if (!$this->filesystem->exists($directory)) {
                if (!$this->filesystem->makeDirectory($directory, 0755, true)) {
                    throw new RuntimeException(
                        "Directory \"$directory\" does not exists and could ".
                        'not be created.'
                    );
                }
            }

            if (!$this->filesystem->isDirectory($directory)) {
                throw new RuntimeException(
                    "\"$directory\" is not a directory."
                );
            }
        });
    }

    /**
     * Gets the path to the directory for the language specified by `$language`.
     *
     * @param string $language
     *
     * @return string
     */
    protected function getLanguageDir($language)
    {
        return realpath(PathHelper::join($this->langSourceDir, $language));
    }

    /**
     * Loads a "group"-file in the given language.
     *
     * @param string $language On of the languages defined in $this->languages
     * @param string $group    The group file to load
     *
     * @return Collection A list of translations in that group
     */
    protected function loadTranslationGroupFile($language, $group)
    {
        $languageDir = $this->getLanguageDir($language);
        $groupFilePath = realpath(PathHelper::join($languageDir, $group.'.php'));
        if ($groupFilePath && $this->filesystem->isFile($groupFilePath)) {
            return new Collection(Arr::dot($this->filesystem->getRequire(
                $groupFilePath
            )));
        } else {
            return new Collection();
        }
    }

    public function determineUntranslatedKeys(Collection $keys)
    {
        $groupedKeys = static::groupKeysByPrefix($keys);

        return $this->languages->mapWithKeys(
            function ($language) use ($groupedKeys) {
                return [
                    $language => $this->determineUntranslatedKeysForLanguage(
                        $language,
                        $groupedKeys
                    ),
                ];
            }
        );
    }

    protected function determineUntranslatedKeysForLanguage(
        $language,
        Collection $groupedKeys
    ) {
        return $groupedKeys->mapWithKeys(
            function ($keys, $group) use ($language) {
                $translations = $this->loadTranslationGroupFile(
                    $language,
                    $group
                );

                return [
                    $group => $keys->flip()->diffKeys($translations)->keys(),
                ];
            }
        )->filter(function ($missing) {
            return count($missing) > 0;
        });
    }

    protected static function groupKeysByPrefix(Collection $keys)
    {
        return $keys->groupBy(function ($key) {
            return strstr($key, '.', true);
        })->map(function ($group) {
            return $group->map(function ($key) {
                return Str::substr(strstr($key, '.'), 1);
            });
        });
    }
}
