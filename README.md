# Find Localizations in Laravel JavaScript Assets

A tools that helps finding untranslated strings in Laravel JavaScript assets for
projects using [rmariuzzo/Laravel-JS-Localization][rmariuzzo] and
[andywer/laravel-js-localization][andywer]. Inspired by
[potsky/laravel-localization-helpers][potsky].

[rmariuzzo]: https://github.com/rmariuzzo/Laravel-JS-Localization
[andywer]:  https://github.com/andywer/laravel-js-localization
[potsky]: https://github.com/potsky/laravel-localization-helpers

# Usage

For now consider the tool as a POC, so don't expect any fancy configuration
options. To get a list of keys that are not yet included in the
translations-files you can run `php Missing.php`. The script will scan all
files with the ending `.js` in the directory `resources/assets/js` relative to
the CWD. It will than look for translations in `resources/lang` and check the
files in the different language directories for inclusion of the keys found in
step one. Any keys that are missing from the translation-files will be echoed to
STDOUT.

```
string(19) "./resources/lang/de/a.php"
array(1) {
  ["my.string"]=>
  int(8)
}
string(19) "./resources/lang/en/a.php"
array(1) {
  ["my.string"]=>
  int(8)
}
string(19) "./resources/lang/de/b.php"
array(1) {
  ["some.status"]=>
  int(1)
}
string(19) "./resources/lang/en/c.php"
array(1) {
  ["something.else"]=>
  int(1)
}
string(19) "./resources/lang/en/d.php"
array(1) {
  ["otherthing.is.missing1"]=>
  int(26)
  ["otherthing.is.missing2"]=>
  int(27)
}
```

## `index.js`
Pipe a list of files (separated by a `\n` to the tool), it will output a
JSON-serialized array in the following format:

```json
[
    {
        "file": "somefile.js",
            "keys": [
            {
                "loc": {
                    "start": {
                        "line": 1,
                        "column": 1
                    },
                    "end": {
                        "line": 1,
                        "column": 10
                    }
                },
                "value": "my.String"
            },
            {
                ...
            }
        ]
    },
    {
        ...
    }
]
```

# License

MIT

# Contributing

If you want to contribute feel free to send a PR. If you are improving on the
JavaScript please make sure you run [prettier][prettier] with the
`--single-quote` flag before creating the MR. Thank you.

[prettier]: https://github.com/jlongster/prettier
