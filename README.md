<a href="https://travis-ci.org/catalyst/moodle-tool_forcedcache">
<img src="https://travis-ci.org/catalyst/moodle-tool_forcedcache.svg?branch=master">
</a>

# moodle-tool_forcedcache

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

For all Moodle branches please use the master branch.


## Installation

#### Requirements:
- If you are on Moodle < 3.9, you must have the changes in [MDL-41492](https://tracker.moodle.org/browse/MDL-41492), applied in your project as this plugin uses those interface points created.

#### Recommendations:
- We recommended to have [MDL-70233](https://tracker.moodle.org/browse/MDL-70233), installed to prevent default cache creation during CACHING_DISABLED conditions such as system upgrade.

1. Clone the plugin
2. Apply core patches (if required)
3. Enable the plugin
4. Wire up the configuration (if required)
5. Update cache configurations as needed

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

Step 3: Enable the plugin
-------------------------
Once the plugin is installed, it can be enabled by setting a configuration variable inside `config.php`
```php
$CFG->alternative_cache_factory_class = 'tool_forcedcache_cache_factory';
```
This will set cache configurations to be readonly, and force the configuration specified in the code.

Once this has been set, you can test whether or not the plugin is enabled by visiting `admin/tool/forcedcache/index.php`. Assuming a clean install, this will apply the default plugin configurations defined in `admin/tool/forcedcache/config.json`. If there are issues at this stage, we recommend you check the previous steps, and the [Debugging](#Debugging) section below.


Step 4: Wire up the configuration (if required)
-----------------------------------------------
All configuration in this plugin is declared in code. You could do one of the following:
- Create your own configuration file (JSON), and specify the `path` to it in config.php
- Or set your configuration directly in a PHP `array` in config.php
- Or by updating the config.json that comes with the plugin,
    - as the plugin loads this by default, you may skip to the next section as the following would not apply to you.

*Note: Only an `array` OR a `path` can be specified. It is not valid to declare both at once.*

#### Set a `path` to the JSON configuration
If you choose to define your cache configuration in a JSON file, you will need to set this to a `$CFG` variable in `config.php` as shown below, to allow the plugin to use this as the preferred path to the configuration:
```
$CFG->tool_forcedcache_config_path = 'path/to/config.json';
```
If this is not supplied, the plugin will default to `config.json` inside of the plugin directory.
Once the path is decided on, the configuration can be viewed. See [Debugging](#debugging) for more information.


#### Defining the configuration `array` in PHP
Alternatively, the caching configuration can be set inside of `config.php`, by creating an associative PHP array with an identical structure to the JSON.

This will have identical behaviour to loading this config from a JSON file.

```php
$CFG->tool_forcedcache_config_array = [
  'stores' => [
    'apcu2' => [
      'type' => 'apcu',
      'config' => [
        'prefix' => 'mdl_'
      ]
    ],
    'file2' => [
      'type' => 'file',
      'config' => [
        'path' => '/tmp/hardcode',
        'autocreate' => 1
      ]
    ]
  ],
  'rules' => [
    'application' => [
      [
        'conditions' => [
          'canuselocalstore' => true
        ],
        'stores' => ['apcu2', 'file2']
      ],
      [
        'stores' => ['file2']
      ]
    ],
    'session' => [
      [
        'conditions' => [
          'canuselocalstore' => true
        ],
        'stores' => ['apcu2', 'file2']
      ],
      [
        'stores' => ['file2']
      ]
    ],
    'request' => []
  ]
];
```


Configuration
-------------

#### Configuration Object
When creating a new configuration object, it must match to a certain structure, or the plugin will not activate. The configuration object must have:
- a list of `stores` - which holds the list of cache stores available and their configuration.
- a list of `rules` - which defines the cache controls you want for different aspects of the system, such as caching at the application level, session level and request level.
- a list of `definitionoverrides` - which lets you overide the configuration of a particular [cache definitions](https://docs.moodle.org/en/Caching#Known_cache_definitions).


#### Stores
```json
"stores": {
  "apcu-example": {
    "type": "apcu",
    "config": {
        "prefix": "mdl"
    }
  }
}
```
`stores` fields:
- should be a hashmap of `instance-name -> instance-configuration`.

The example store here is an APCu store with an `instance-name` of `apcu-example`.

`instance-configuration` fields:
- `type` is the plugin name of the matching store plugin, __without__ the `cachestore_` prefix. For example, `cachestore_apcu` would just be `apcu`.
- `config` is a hashmap containing the key and value of settings that would be mapped `1:1` to control the store's instance configuration.


#### Rules
```json
"rules": {
    "application": [
      {
        "conditions" : {
          "canuselocalstore": true
        },
        "stores": ["apcu1","file1"]
      },
      {
        "stores":["file1"]
      }
    ],
    "session": [
      {
        "conditions": {
          "canuselocalstore": true
        },
        "stores": ["apcu1","file1"]
      },
      {
        "stores": ["file1"]
      }
    ],
    "request": {}
  }
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
```json
"definitionoverrides": {
    "core/databasemeta": {
        "canuselocalstore": true
    }
}
```
`definitionoverrides` fields:
- a hashmap of `cache-definition -> properties (to be overridden)`

`properties` fields:
- a hashmap of `name -> value`, which aligns with the property's name and value.

You can specify any config overrides here that should be applied to specific [cache definitions](https://docs.moodle.org/en/Caching#Known_cache_definitions). This is not always a safe operation, and the plugin makes no effort to ensure this won't cause issues.


#### Cache Stores Examples
Below are a list of cache stores and configuration boilerplates for cache stores that come pre-installed with Moodle.

##### APCu
```json
"apcu1": {
    "type": "apcu",
    "config": {
        "prefix": "mdl"
    }
}
```

##### File Cache
```json
"file1": {
    "type": "file",
    "config": {
        "path": "/tmp/filecache",
        "autocreate": 1
    }
}
```

##### Memcached
```json
"memcached1": {
    "type": "memcached",
    "config": {
        "servers": {
            "0": {
                "0": "127.0.0.1",
                "1": "11211"
            }
        },
        "compression": 1,
        "serialiser": 1,
        "prefix": "mdl",
        "hash": 0,
        "bufferwrites": 0,
        "clustered": false,
        "setservers": [],
        "isshared": 0
    }
}
```

##### MongoDB
```json
"mongodb1": {
    "type": "mongodb",
    "config": {
        "server": "mongodb://127.0.0.1:27017",
        "database": "mcache",
        "extendedmode": false,
        "username": "username",
        "password": "password",
        "usesafe": true
    }
}
```

##### Redis
```json
"redis1": {
    "type": "redis",
    "config": {
        "server": "127.0.0.1:6379",
        "prefix": "mdl_",
        "password": "password",
        "serializer": 1,
        "compressor": 0
    }
}
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
