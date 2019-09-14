# Data Transform Plugin for Pattern Lab

## Installation

To install and use the plugin run the following command in the Pattern Lab root directory:

```sh
composer require aleksip/plugin-data-transform
```


## Configuration options

For `Attribute` object support to work properly, your Pattern Lab `config.yml` file needs to have the following setting:
 
```yaml
twigAutoescape: false
```

The default values for Data Transform Plugin specific options in `config.yml` are:

```yaml
plugins:
    dataTransform:
        enabled: true
        verbose: false
```


### Enabling and disabling the plugin

Once installed, it is possible to enable and disable Data Transform Plugin using the `enabled` setting.


### Verbose mode

Occasionally it might happen that there is a problem with a data file, and PHP notices and/or warnings with long stack traces are displayed when Pattern Lab is generated. In a large project it can be difficult to find the problematic data file, but turning on Data Transform Plugin's verbose mode using the `verbose` setting can help.

In verbose mode Data Transform plugin reports each pattern it processes and all data transform functions performed. It also suppresses regular PHP error messages and reports about errors in an easier to read way.

Important note: due to the way verbose mode is implemented, it might not work if other plugins that interact with the Twig `Environment` object are used.


## Features

### Pattern-specific data file support for included patterns

Pattern Lab core only supports global data files and a pattern-specific data file for the main pattern. This plugin adds pattern-specific data file support for included patterns. This feature works with the include function provided by this plugin with all PatternEngines and also with regular includes in template files with Twig PatternEngine.

Please note that global data from the `_data` directory is considered to be pattern-specific data and will overwrite data inherited from a parent pattern. If you want to override data of an included pattern you can use the `with` keyword.


### Data transform functions

Currently the plugin provides four transform functions for the data read by Pattern Lab. The examples provided are in JSON but Pattern Lab supports YAML too.


#### Include pattern files

If a value contains the name of a pattern in shorthand partials syntax, the plugin will replace the value with the rendered pattern:

```json
{
    "key": "atoms-form-element-label-html"
}
```

Advanced syntax with support for passing variables (`with`) and disabling access to the default data (`only`):

```json
{
    "key": {
        "include()": {
            "pattern": "atoms-form-element-label-html",
            "with": {
                "title": "Textfield label"
            },
            "only": true
        }
    }
}
```

In both examples the value of `key` will replaced with the rendered pattern.

For more information about `with` and `only` please refer to the [Twig `include` documentation](https://twig.symfony.com/doc/2.x/tags/include.html).


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

The value of `key` will be replaced with the joined strings. Note that in the example `molecules-comment-html` is the name of a pattern in shorthand partials syntax. These will be replaced with the rendered pattern before the join.


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

The value of `key` will be replaced with an `Url` object. Note that in the example the value of `attributes` will be replaced with an `Attribute` object before the `Url` object is created.
