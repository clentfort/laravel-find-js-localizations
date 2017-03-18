<?php

namespace clentfort\LaravelFindJsLocalizations;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Process\ProcessBuilder;
use clentfort\LaravelFindJsLocalizations\Exceptions\RuntimeException;

class KeyFinder
{
    /**
     * @var Illuminate\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * The path to the JavaScript source files, i.e. `resources/assets/js/`.
     *
     * @var string
     */
    protected $jsSourceDir;

    /**
     * The path to the nodejs executable.
     *
     * @var string
     */

    /**
     * The filetype of the JavaScript source-files, i.e. `.js` or `.mjs`.
     *
     * @var string
     */
    protected $jsSourceFileType;

    /**
     * @var string jsSourceDir
     * @var string nodeExecutable
     * @var string jsSourceFileType The type of the JavaScript source-files i.e.
     *             `.js` or `.mjs`
     */
    public function __construct(
        Filesystem $filesystem,
        $jsSourceDir,
        $nodeExecutable,
        $jsSourceFileType = 'js'
    ) {
        $this->filesystem = $filesystem;
        $this->jsSourceDir = $jsSourceDir;
        $this->nodeExecutable = $nodeExecutable;
        $this->jsSourceFileType = $jsSourceFileType;

        if (!$this->filesystem->isDirectory($jsSourceDir)) {
            throw new RuntimeException(
                "\"$jsSourceDir\" is not a directory."
            );
        }
    }

    /**
     * Generates a list of all JavaScript-files in the $jsSourceDir
     * including any subdirectories.
     *
     * @return Collection A list of all the JavaScript-files below $jsSourceDir
     */
    protected function listJsSourceFiles()
    {
        return (new Collection($this->filesystem->allFiles($this->jsSourceDir)))
            ->filter(function ($file) {
                return Str::endsWith($file, ".$this->jsSourceFileType");
            })
            ->map(function ($file) {
                return PathHelper::join(
                    $this->jsSourceDir,
                    $file->getRelativePathname()
                );
            });
    }

    /**
     * @var Collection A list of JavaScript source-files to look for
     *                 translations in
     *
     * @return array a list where the first item is a \Laravel\Collection of the
     *               founds translation-keys grouped by the file they were found
     *               in and the second item being a list of errors logged to
     *               STDOUT
     */
    protected function findKeysInJsSourceFiles(Collection $files)
    {
        /**
         * @var Process
         */
        $finder = (new ProcessBuilder([
            $this->nodeExecutable,
            PathHelper::join(dirname(__FILE__), 'index.js'),
        ]))->getProcess();
        $finder->setInput($files->implode("\n"));

        // Use must run here so we don't have to check for the status ourselves
        $finder->mustRun();
        $json = json_decode($finder->getOutput());

        // The process should always return valid JSON unless something went
        // wrong invoking it.
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = json_last_error_msg();
            throw new RuntimeException(
                'Could not parse JSON returned by node. Failed with error: '.
                "\"$error\""
            );
        }

        return [
            new Collection($json),
            new Collection(array_filter(
                explode(PHP_EOL, $finder->getErrorOutput())
            )),
        ];
    }

    /**
     * Searches for localization-calls in the configure `$jsSourceDir` and
     * returns a list of found keys grouped by the file they were found in.
     *
     * @return array
     */
    public function findKeys()
    {
        $files = $this->listJsSourceFiles();

        return $this->findKeysInJsSourceFiles($files);
    }
}
