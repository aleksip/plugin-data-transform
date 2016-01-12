# Data Transform Plugin for Pattern Lab PHP

## Installation

To install the plugin run:

```sh
composer require aleksip/plugin-data-transform
```


## Features

Currently the plugin provides 3 transform functions for the JSON data read by Pattern Lab.


### Include pattern files

If a value contains the name of a pattern in shorthand partials syntax, the plugin will replace the value with the rendered pattern:

```json
{
    "key": "atoms-form-element-label.html"
}
```

Advanced syntax with support for passing variables and disabling access to the default data:

```json
{
    "key": {
        "include()": {
            "pattern": "atoms-form-element-label.html",
            "with": {
                "title": "Textfield label"
            },
            "only": true
        }
    }
}
```

In both examples the value of `key` will replaced with the rendered pattern.


### Join text values

```json
{
    "key": {
        "join()": [
            "molecules-comment.html",
            "<div class=\"indented\">",
            "molecules-comment.html",
            "</div>",
            "molecules-comment.html"
        ]
    }
}
```

The value of `key` will be replaced with the joined strings. Note that in the example `molecules-comment.html` is the name of a pattern in shorthand partials syntax. These will be replaced with the rendered pattern before the join.


### Create Drupal `Attribute` objects

```json
{
    "key": {
        "Attribute()": {
            "id": [ "edit-submit" ],
            "type": [ "submit" ],
            "value": [ "Submit" ],
            "class": [ "button", "button-primary" ]
        }
    }
}
```

The value of `key` will be replaced with an [Attribute object](https://www.drupal.org/node/2513632).


## More examples

All functions provided by this plugin are used extensively in [Shila Drupal Theme StarterKit](https://github.com/aleksip/starterkit-shila-drupal-theme).
