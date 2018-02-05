# Data Transform Plugin for Pattern Lab

Since version 1.2.0 this plugin should once again work with all PatternEngines.


## Installation

To install and use the plugin run:

```sh
composer require aleksip/plugin-data-transform
```


## Features

### Pattern-specific data file support for included patterns

Pattern Lab core only supports global data files and a pattern-specific data file for the main pattern. This plugin adds pattern-specific data file support for included patterns. This feature works with the include function provided by this plugin with all PatternEngines and also with regular includes in template files with Twig PatternEngine.

Please note that global data from the `_data` directory is considered to be pattern-specific data and will overwrite data inherited from a parent pattern. If you want to override data of an included pattern you can use the `with` keyword.


### Data transform functions

Currently the plugin provides four transform functions for the data read by Pattern Lab. The examples provided are in JSON but Pattern Lab supports YAML too!


#### Include pattern files

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


#### Include pseudo-pattern files

It is also possible to include [pseudo-patterns](http://patternlab.io/docs/pattern-pseudo-patterns.html) using the shorthand partials syntax, by replacing the tilde (~) with a dash (-). So for example the pseudo-pattern `shila-card.html~variant.json` can be included like so:

```json
{
    "key": "molecules-shila-card-html-variant"
}
```


#### Join text values

```json
{
    "key": {
        "join()": [
            "molecules-comment-html",
            "<div class=\"indented\">",
            "molecules-comment-html",
            "</div>",
            "molecules-comment-html"
        ]
    }
}
```

The value of `key` will be replaced with the joined strings. Note that in the example `molecules-comment.html` is the name of a pattern in shorthand partials syntax. These will be replaced with the rendered pattern before the join.


#### Create Drupal `Attribute` objects

```json
{
    "key": {
        "Attribute()": {
            "id": ["edit-submit"],
            "type": ["submit"],
            "value": ["Submit"],
            "class": ["button", "button-primary"]
        }
    }
}
```

The value of `key` will be replaced with an [`Attribute` object](https://www.drupal.org/node/2513632).


#### Create Drupal `Url` objects

```json
{
    "key": {
        "Url()": {
            "url": "http://example.com",
            "options": {
                "attributes": {
                    "Attribute()": {
                        "class": ["link"]
                    }
                }
            }
        }
    }
}
```

The value of `key` will be replaced with an `Url` object. Note that in the example `attributes` will be replaced with an `Attribute` object before the `Url` object is created.


## More examples

Most features provided by this plugin are used in [Shila Drupal theme](https://github.com/aleksip/shila-drupal-theme).
