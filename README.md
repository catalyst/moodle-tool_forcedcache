# moodle-tool_forcedcache

![GitHub Workflow Status (branch)](https://img.shields.io/github/actions/workflow/status/catalyst/moodle-tool_forcedcache/ci.yml?label=ci&branch=MOODLE_40_STABLE)

* [What is this?](#what-is-this)
* [Branches](#branches)
* [Installation](#installation)
* [Configuration](#configuration)
* [Debugging](#debugging)
* [Support](#support)

## What is this?

This is a moodle plugin that will override Moodle's default options for caching with custom configuration.
This allows for deterministic configuration, based on a lightweight configuration and rules stored in code.
This has the advantage of making caching code-configurable before deployment, and allows for more control of the cache configurations throughout your fleet.

## Branches

| Moodle version    | Branch           | PHP  |
|-------------------|------------------|------|
| Moodle 4.0+       | MOODLE_40_STABLE | 7.3+ |
| Moodle 3.5 - 3.11 | master           | 7.1+ |


## Installation

#### Requirements:
- If you are on Moodle < 3.9, you must have the changes in [MDL-41492](https://tracker.moodle.org/browse/MDL-41492), applied in your project as this plugin uses those interface points created.

#### Recommendations:
- We recommended to have [MDL-70233](https://tracker.moodle.org/browse/MDL-70233), installed to prevent default cache creation during CACHING_DISABLED conditions such as system upgrade.

1. [Clone the plugin](#step-1-clone-the-plugin)
2. [Apply core patches (if required)](#step-2-apply-core-patches-if-required)
3. [Wire up the configuration](#step-3-wire-up-the-configuration-if-required)
4. [Update cache configurations as needed](#step-4-configure-the-cache-settings)
5. [Apply the new cache rules](#step-5-apply-the-cache-rules)

Step 1: Clone the plugin
------------------------

Using git from the root directory of your moodle:

```
git clone https://github.com/catalyst/moodle-tool_forcedcache.git admin/tool/forcedcache
```

Then run the Moodle install or upgrade as normal.

https://docs.moodle.org/en/Installing_plugins


Step 2: Apply core patches (if required)
----------------------------------------

This plugin relies on [MDL-41492](https://tracker.moodle.org/browse/MDL-41492), so this patch must be applied to any Moodle prior
to 3.9. Patches have been bundled with this plugin, to allow for quick application of the patch for various supported Moodle versions.

Step 3: Wire up the configuration
-----------------------------------------------
All configuration in this plugin is declared in code. You could do one of the following:
- Set your configuration directly in a PHP `array` in config.php (Recommended)
- Or Create your own configuration file (JSON), and specify the `path` to it in config.php
- Or by updating the config.json that comes with the plugin, then moving it to an [appropriate location](#set-a-path-to-the-json-configuration).

*Note: Only an `array` OR a `path` can be specified. It is not valid to declare both at once.*


#### Defining the configuration `array` in PHP
The caching configuration can be set inside of `config.php`, by creating an associative PHP array with the appropriate structure.

Below is an example closely matching to our current production setup:

```php
$CFG->tool_forcedcache_config_array = [
    'stores' => [
        // APCu is an in memory cache local to each front end.
        // It has a limited size and doesn't like being full.
        // See: https://docs.moodle.org/en/APC_user_cache_(APCu)
        'APCu' => [
            'type' => 'apcu',
            'config' => [
                'prefix' => 'apcu_',
            ],
        ],
        // Redis is an all round great workhorse cache which
        // we use as the main shared cache.
        // https://docs.moodle.org/en/Redis_cache_store
        'redis' => [
            'type' => 'redis',
            'config' => [
                'server' => '127.0.0.1:6379',
                'prefix' => 'mdl_',
                'password' => '',
                'serializer' => 1,
                'compressor' => 2,
            ],
        ],
        // This is a file cache local to each front end on fast SSD.
        // It is conceptual similar to but should not be confused with
        // $CFG->localcachedir which is outside of MUC.
        'local_file' => [
            'type' => 'file',
            'config' => [
                'path' => '/tmp/local-cache-file',
                'autocreate' => 1,
            ],
        ],
        // This is a shared file cache inside normal $CFG->dataroot.
        'shared_file' => [
            'type' => 'file',
            'config' => [
                'path' => '/mnt/path/to/shared-cache-file',
                'autocreate' => 1,
            ],
        ],
    ],
    'rules' => [
        'application' => [
            // These 3 definitions are localizable, but also very small and highly
            // requested so are great candidates for APCu.
            [
                'conditions' => [
                    'name' => 'core/plugin_functions',
                ],
                'stores' => ['APCu', 'redis'],
            ],
            [
                'conditions' => [
                    'name' => 'core/string',
                ],
                'stores' => ['APCu', 'redis'],
            ],
            [
                'conditions' => [
                    'name' => 'core/langmenu',
                ],
                'stores' => ['APCu', 'redis'],
            ],
            // This is another special case similar to coursemodinfo below,
            // this cache has a very large number items so we would put it
            // into local and shared files, but don't due to MDL-69088.
            // In practice this doesn't matter as rebuilding these items is
            // relatively quick, unlike coursemodinfo which is very costly.
            [
                'conditions' => [
                    'name' => 'core/htmlpurifier',
                ],
                'stores' => ['local_file'],
            ],
            // Course mod info is a special case because it is so large so we
            // use files instead of redis for the shared stacked cache.
            [
                'conditions' => [
                    'name' => 'core/coursemodinfo',
                ],
                'stores' => ['local_file', 'shared_file'],
            ],
            // Everything else which is localizable we have in both a local
            // cache backed by a shared case to warm up the local caches faster
            // while auto scaling in new front ends.
            [
                'conditions' => [
                    'canuselocalstore' => true,
                ],
                'stores' => ['local_file', 'redis'],
            ],
            // Anything left over which cannot be localized just goes into shared
            // redis as is.
            [
                'stores' => ['redis'],
            ]
        ],
        'session' => [
            [
                'stores' => ['redis'],
            ]
        ],
        'request' => [],
    ],
    'definitionoverrides' => [
        'core/plugin_functions' => [
            'canuselocalstore' => true,
        ],
    ],
];
```


#### Set a `path` to the JSON configuration
If you choose to define your cache configuration in a JSON file, you will need to set this to a `$CFG` variable in `config.php` as shown below, to allow the plugin to use this as the preferred path to the configuration:
```
$CFG->tool_forcedcache_config_path = 'path/to/config.json';
```
If this is not supplied, the plugin will default to `config.json` inside of the plugin directory. The default is not a valid production path and this file should only serve as an example. Please move this file outside the [dirroot](https://moodle.org/mod/glossary/showentry.php?eid=20&displayformat=dictionary) directory. Once the path is decided on, the configuration can be viewed. See [Debugging](#debugging) for more information.


Step 4: Configure the cache settings
-------------------------------------
See [Configuration](#configuration) for all options.

Step 5: Apply the cache rules
-----------------------------
__Please ensure that you have visited `admin/tool/forcedcache/index.php` and confirmed that the <ins>configuration is valid</ins> and would be <ins>applying the rules you expect</ins> BEFORE updating the factory class.__

Once the plugin is installed and configured the way you want, the rules can be applied by setting a configuration variable inside `config.php`
```php
$CFG->alternative_cache_factory_class = 'tool_forcedcache_cache_factory';
```
This will set cache configurations to be readonly, and force the configuration specified in the code.

Once this has been set, you can test whether or not the plugin is `active` by visiting `admin/tool/forcedcache/index.php`. With a clean install, this will apply the default plugin configurations defined in `admin/tool/forcedcache/config.json`. If there are issues at this stage, we recommend you check the previous steps, and the [Debugging](#Debugging) section below.



Configuration
-------------

#### Configuration Object
When creating a new configuration object, it must match to a certain structure, or the plugin will not activate. The configuration object must have:
- a list of `stores` - which holds the list of cache stores available and their configuration.
- a list of `rules` - which defines the cache controls you want for different aspects of the system, such as caching at the application level, session level and request level.
- a list of `definitionoverrides` - which lets you overide the configuration of a particular [cache definitions](https://docs.moodle.org/en/Caching#Known_cache_definitions).


#### Stores
```php
'stores' => [
    'apcu-example' => [
        'type' => 'apcu',
        'config' => [
            'prefix' => 'mdl'
        ]
    ]
]
```
`stores` fields:
- should be a hashmap of `instance-name -> instance-configuration`.

The example store here is an APCu store with an `instance-name` of `apcu-example`.

`instance-configuration` fields:
- `type` is the plugin name of the matching store plugin, __without__ the `cachestore_` prefix. For example, `cachestore_apcu` would just be `apcu`.
- `config` is a hashmap containing the key and value of settings that would be mapped `1:1` to control the store's instance configuration.


#### Rules
```php
'rules' => [
    'application' => [
        [
            'conditions' => [ 'canuselocalstore' => true ],
            'stores' => [ 'apcu1', 'file1' ],
        ],
        [
            'stores' => [ 'file1' ],
        ],
    ],
    'session' => [
        [
            'conditions' => [ 'canuselocalstore' => true ],
            'stores' => [ 'apcu1', 'file1' ],
        ],
        [
            'stores' => [ 'file1' ],
        ],
    ],
    'request' => [],
],
```

`rules` fields:
- a hashmap of `cache-type -> rulesets` ([Learn about cache types - sometimes referred to as mode](https://docs.moodle.org/en/Caching)).
- The 3 required cache types, are `application`, `session` and `request`.
- `rulesets` are checked and the first ruleset evaluating to true is applied. If the condition for that ruleset is evaluated to false, the next ruleset is checked. If the ruleset has no conditions, this is automatically considered as evaluating to true.
    - __order matters__, the first matching set of conditions for a given ruleset will apply the stores configured.
    - If there are no rulesets defined for a cache type, or there are no rulesets that a definition can match, the definition will fall through to the default store instance used for that cache type.

`ruleset` fields:
- `stores`: a flat array of store `instance-names` as defined in the [previous section](#Stores).
    - __order matters__, the stores will be applied are preferred in the order defined, the first taking preference.
- `conditions` (optional) - a list of conditions which determines whether the list of `stores` defined in the same ruleset will apply.
    - The format for each condition is `name -> value`.
    -  Each condition is checked against the [cache definitions](https://docs.moodle.org/en/Caching#Known_cache_definitions)'s properties, which could be the `name`, `canuselocalstore`, or a combination of other cache definition properties.


#### Definition overrides
```php
'definitionoverrides' => [
    'core/plugin_functions' => [
        'canuselocalstore' => true
    ]
]
```
`definitionoverrides` fields:
- a hashmap of `cache-definition -> properties (to be overridden)`

`properties` fields:
- a hashmap of `name -> value`, which aligns with the property's name and value.

You can specify any config overrides here that should be applied to specific [cache definitions](https://docs.moodle.org/en/Caching#Known_cache_definitions). This is not always a safe operation, and the plugin makes no effort to ensure this won't cause issues.


#### Cache Stores Examples
Below are a list of cache stores and configuration boilerplates for cache stores that come pre-installed with Moodle.

##### APCu
```php
'APCu' => [
    'type' => 'apcu',
    'config' => [
        'prefix' => 'apcu_'
    ]
],
```

##### File Cache
```php
'local_file' => [
    'type' => 'file',
    'config' => [
        'path' => '/tmp/muc',
        'autocreate' => 1
    ]
],
```

##### Memcached
```php
'memcached' => [
    'type' => 'memcached',
    'config' => [
        'servers' => [
            [
                '127.0.0.1',
                '11211',
            ]
        ],
        'compression' => 1,
        'serialiser' => 1,
        'prefix' => 'mdl',
        'hash' => 0,
        'bufferwrites' => 0,
        'clustered' => false,
        'setservers' => [],
        'isshared' => 0
    ]
],
```

##### MongoDB
```php
'mongodb' => [
    'type' => 'mongodb',
    'config' => [
        'server' => 'mongodb://127.0.0.1:27017',
        'database' => 'mcache',
        'extendedmode' => false,
        'username' => 'username',
        'password' => 'password',
        'usesafe' => true
    ],
],
```

##### Redis
```php
'redis' => [
    'type' => 'redis',
    'config' => [
        'server' => '127.0.0.1:6379',
        'prefix' => 'mdl_',
        'password' => 'password',
        'serializer' => 1,
        'compressor' => 2,
    ],
],
```

## Debugging
To assist in debugging the configuration, `admin/tool/forcedcache/index.php` will display some information about the status of the plugin.
If there are any errors reported when creating configuration from the JSON file, the error message will be displayed on this page. If the JSON is able to be parsed, the rulesets configuration will be displayed for each of the caching modes.

If the plugin has been enabled, you can also visit `cache/admin.php` to view the overall configuration. If there are any store instances defined in the JSON that are not appearing in the list of configured instances, it means that a store instance was unable to be created from the supplied configuration. Check the `config` settings under the relevant store inside the defined configuration.

## Support
If you have issues please log them in github here

https://github.com/catalyst/moodle-tool_forcedcache/issues

Please note our time is limited, so if you need urgent support or want to
sponsor a new feature then please contact Catalyst IT Australia:

https://www.catalyst-au.net/contact-us

This plugin was developed by Catalyst IT Australia:

https://www.catalyst-au.net/

<img alt="Catalyst IT" src="https://cdn.rawgit.com/CatalystIT-AU/moodle-auth_saml2/master/pix/catalyst-logo.svg" width="400">
