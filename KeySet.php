<?php

namespace clentfort\LaravelFindJsLocalizations;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use clentfort\LaravelFindJsLocalizations\Exceptions\RuntimeException;

class KeySet
{
    /**
     * @var Illuminate\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    protected $directory;

    public function __construct($directory)
    {
        $this->directory = $directory;

        $this->filesystem = new Filesystem();

        if (!$this->filesystem->exists($this->directory)) {
            if (!$this->filesystem->makeDirectory($this->directory, 0755, true)) {
                throw new RuntimeException(
                    "Directory \"$this->directory\" does not exists and could ".
                    'not be created.'
                );
            }
        }

        if (!$this->filesystem->isDirectory($this->directory)) {
            throw new RuntimeException(
                "\"$this->directory\" is not a directory."
            );
        }
    }

    public function getKeysWithPrefix($prefix)
    {
        $prefixFilePath = $this->getFilePath($prefix);
        if ($prefixFilePath && $this->filesystem->isFile($prefixFilePath)) {
            return new Collection(Arr::dot($this->filesystem->getRequire(
                $prefixFilePath
            )));
        }

        return new Collection();
    }

    public function setKeysWithPrefix($prefix, $keys)
    {
        $prefixFilePath = $this->getFilePath($prefix);

        return $this->filesystem->put(
            $prefixFilePath,
            var_export(Arr::dot($keys))
        );
    }

    protected function getFilePath($prefix)
    {
        return PathHelper::join(
            $this->directory,
            "$prefix.php"
        );
    }
}
