<?php

namespace clentfort\LaravelFindJsLocalizations;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use clentfort\LaravelFindJsLocalizations\Exceptions\RuntimeException;

class PrefixFileManager
{
    /**
     * @var Illuminate\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    protected $language;

    /**
     * @var string
     */
    protected $langSourceDir;

    public function __construct($language, $langSourceDir)
    {
        $this->language = $language;
        $this->langSourceDir = $langSourceDir;

        $this->filesystem = new Filesystem();

        $directory = $this->getLanguageDir();
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
    }

    /**
     * Gets the path to the directory for the set language.
     *
     * @return string
     */
    protected function getLanguageDir()
    {
        return PathHelper::join(
            $this->langSourceDir,
            $this->language
        );
    }

    public function readPrefixFile($prefix)
    {
        $prefixFilePath = $this->getPrefixFilePath($prefix);
        if ($prefixFilePath && $this->filesystem->isFile($prefixFilePath)) {
            return new Collection(Arr::dot($this->filesystem->getRequire(
                $prefixFilePath
            )));
        }

        return new Collection();
    }

    public function writePrefixFile($prefix, $keys)
    {
        $prefixFilePath = $this->getPrefixFilePath($prefix);
        return $this->filesystem->put(
            $prefixFilePath, 
            var_export(Arr::dot($keys))
        );
    }

    protected function getPrefixFilePath($prefix)
    {
        return PathHelper::join(
            $this->getLanguageDir(),
            "$prefix.php"
        );
    }
}
