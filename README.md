# Find Localizations in Laravel JavaScript Assets

A tools that helps finding untranslated strings in Laravel JavaScript assets for
projects using [rmariuzzo/Laravel-JS-Localization][rmariuzzo] and
[andywer/laravel-js-localization][andywer]. Inspired by
[potsky/laravel-localization-helpers][potsky].

[rmariuzzo]: https://github.com/rmariuzzo/Laravel-JS-Localization
[andywer]:  https://github.com/andywer/laravel-js-localization
[potsky]: https://github.com/potsky/laravel-localization-helpers

# Usage

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

# Contributions

If you want to contribute feel free to send a PR. If you are improving on the
JavaScript please make sure you run [prettier][prettier] with the
`--single-quote` flag before creating the MR. Thank you.

[prettier]: https://github.com/jlongster/prettier
