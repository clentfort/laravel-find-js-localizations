<?php

namespace clentfort\LaravelFindJsLocalizations;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Output\OutputInterface;

class FindMissing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'js-localization:missing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scans the JavaScript files for translation-keys '.
                             'that are missing from the translation-files.';

    /**
     * The config for the command.
     *
     * @var array
     */
    protected $config;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Create a new command instance.
     *
     * @param array $config The configuration for this command
     */
    public function __construct(Filesystem $filesystem, array $config)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->config = $config;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line(
            "Analysing JavaScript-files in {$this->config['directory']}, this ".
            'might take a while.'
        );
        $this->line('');

        $keyList = $this->getKeyList();
        $keySets = $this->getAllKeySets();

        $keySets->each(function ($keySet, $path) use ($keyList) {
            list(
                $missingKeys,
                $missingKeyCount
            ) = $this->getKeysMissingFromSet($keySet, $keyList);

            $write = $this->confirm(
                "Found $missingKeyCount keys missing in ${path}, do you want ".
                'to add these?'
            );

            if (!$write) {
                return;
            }

            $this->addMissingKeysToKeySet($keySet, $missingKeys);
        });
    }

    /**
     * Gets a list of all translation-keys used in the JavaScript-files.
     *
     * @return Collection
     */
    private function getKeyList()
    {
        list(
            $fileKeyList,
            $errors
        ) = (new KeyFinder(
            $this->filesystem,
            base_path($this->config['directory']),
            $this->config['node_executable'],
            $this->config['extension']
        ))->findKeys();

        if (!$errors->isEmpty()) {
            $this->warn(
                'Some files could not be parsed, the following errors were '.
                'reported:'
            );
            $errors->each(function ($error) {
                $this->warn(" * $error");
            });
        }

        return static::fileKeyListToKeyList($fileKeyList);
    }

    /**
     * Finds keys that are in $keyList but not in $keySet.
     *
     * @param KeySet     $keySet
     * @param Collection $keyList
     *
     * @return array a collection of keys, grouped by their common preifx, that
     *               are missing from $keySet and the total number of foudn
     *               keys
     */
    private function getKeysMissingFromSet(
        KeySet $keySet,
        Collection $keyList
    ) {
        $missingKeys = KeySetDiffer::diffKeys($keySet, $keyList);
        $missingKeyCount = $missingKeys->reduce(function ($total, $group) {
            return $total + $group->count();
        }, 0);

        return [$missingKeys, $missingKeyCount];
    }

    private function addMissingKeysToKeySet(
        KeySet $keySet,
        Collection $missingKeys
    ) {
        return $missingKeys->each(function ($group, $prefix) use ($keySet) {
            $keys = $keySet->getKeysWithPrefix($prefix);
            // $group contains a list of keys not yet in the keys of the
            // key-set, since we want to use the keys as the indices of an
            // associative array we need to flip them first.
            $keys = $keys->merge($group->flip()->map(function () {
                return 'Missing Translation';
            }));

            if ($this->isVerbose()) {
                $this->line("Writing \"$prefix.php\".");
            }

            $keySet->setKeysWithPrefix($prefix, $keys);
        });
    }

    /**
     * Determines the languages used by the application by looking at the
     * directories in resources/lang.
     *
     * @return Collection A collection of [$dir => KeySet]
     */
    private function getAllKeySets()
    {
        $languageDirsPath = base_path('resources/lang');
        $languageDirs = $this->filesystem->directories($languageDirsPath);

        $languages = [];
        foreach ($languageDirs as $languageDir) {
            $languages[$languageDir] = new KeySet(
                $this->filesystem,
                $languageDir
            );
        }

        return new Collection($languages);
    }

    /**
     * Checks if the command is run with the verbose-flag.
     *
     * @return bool
     */
    private function isVerbose()
    {
        $verbosityLevel = $this->getOutput()->getVerbosity();

        return $verbosityLevel >= OutputInterface::VERBOSITY_VERBOSE;
    }

    /**
     * Extracts all keys from a file-key-list generated by KeyFinder#findKeys.
     *
     * @param Collection $fileKeyList
     *
     * @return Collection a collection of translation-keys
     */
    private static function fileKeyListToKeyList($fileKeyList)
    {
        return $fileKeyList->reduce(function ($keys, $file) {
            return $keys->merge(array_map(function ($key) {
                return $key->value;
            }, $file->keys));
        }, new Collection());
    }
}
