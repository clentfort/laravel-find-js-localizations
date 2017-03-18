<?php

namespace clentfort\LaravelFindJsLocalizations;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use clentfort\LaravelFindJsLocalizations\Exceptions\RuntimeException;

class KeySet
{
    /**
     * @var string
     */
    const FILE_TEMPLATE = <<<PHP
<?php

return %s;
PHP;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    protected $directory;

    public function __construct(Filesystem $filesystem, $directory)
    {
        $this->filesystem = $filesystem;
        $this->directory = $directory;

        if (!$this->filesystem->exists($this->directory)) {
            throw new RuntimeException(
                "Directory \"$this->directory\" does not exists."
            );
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
            $keys = $this->filesystem->getRequire($prefixFilePath);
            if (is_array($keys)) {
                $keys = Arr::dot($keys);
                ksort($keys);

                return new Collection($keys);
            }
        }

        return new Collection();
    }

    public function setKeysWithPrefix($prefix, $keys)
    {
        $prefixFilePath = $this->getFilePath($prefix);

        return $this->filesystem->put(
            $prefixFilePath,
            static::exportKeys($keys)
        );
    }

    /**
     * @return string
     */
    protected static function exportKeys($keys)
    {
        $keys = Arr::dot($keys);
        ksort($keys);

        return sprintf(static::FILE_TEMPLATE, var_export($keys, true));
    }

    protected function getFilePath($prefix)
    {
        return PathHelper::join(
            $this->directory,
            "$prefix.php"
        );
    }
}
