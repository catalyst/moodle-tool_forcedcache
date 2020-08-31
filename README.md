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

This is a moodle plugin that will override Moodle's default mode of caching based on a configuration file.
It is replaced with a deterministic configuration, based on a lightweight configuration and rules stored in code.
This has the advantage of making caching code-configurable before deployment, and allowing for identical caching
configurations throughout your fleet.

## Branches

For all Moodle branches please use the master branch.

## Installation

To install this plugin, MDL-41492 is required to be backported to create the interface points for the plugin.

Step 1: Clone the plugin
------------------------

Using git from the root directory of your moodle:

```
git clone https://github.com/catalyst/moodle-tool_forcedcache.git admin/tool/forcedcache
```

Then run the Moodle upgrade as normal.


https://docs.moodle.org/en/Installing_plugins


Step 2: Apply core patches
--------------------------

This plugin relies on [MDL-41492](https://tracker.moodle.org/browse/MDL-41492), so this patch must be applied to any Moodle prior
to 3.9. Patches have been bundled with this plugin, to allow for quick application of the patch for various supported Moodle versions.

## Configuration
All of the plugin configuration is deliberately performed in code, through modification of the config.json file included with the plugin, provided as an example, or through the creation of a seperate JSON file somewhere on the system.

### JSON Fields
If creating a new JSON file, it must match to a certain structure, or the plugin will not activate.

#### Stores

```php
$CFG->tool_forcedcache_config_array = [
    'stores' => [
        'APCu' => [
            'type' => 'apcu',
            'config' => [
                'prefix' => 'apcu_',
            ],
        ],
        'redis' => [
            'type' => 'redis',
            'config' => [
                'server' => '...',
                'prefix' => 'redis_',
                'password' => '',
                'serializer' => 1,
                'compressor' => 2,
            ],
        ],
        'local_file' => [
            'type' => 'file',
            'config' => [
                'path' => '/tmp/muc',
                'autocreate' => 1,
            ],
        ],
        'stacked_file' => [
            'type' => 'file',
            'config' => [
                'path' => '/var/lib/sitedata/cache/stacked',
                'autocreate' => 1,
            ],
        ],
    ],
```

A field called `stores` must be defined as an array. Inside, each store can be declared as an indexed array of name -> values.
The example stores above are a great production base of 4 stores available for different cache definitions.
The type is the plugin name of the matching store plugin and the config is a keyed array specific to each cache store plugin.

#### Rules
```
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

A field called `rules` must be defined as an array. This array must contain 3 named arrays `application`, `session` and `request`.
These correspond to the 3 caching modes available. Inside each of these modes, various rulesets can be defined, to map cache definitions
to the declared store instances in the above stores section. Rulesets should be declared as non-indexed arrays declared in preferential order.
E.g. Definitions will be checked against the top ruleset, then 2 etc. Inside each of these rulesets, there are 2 sections, `conditions` and `stores`. Conditions is an optional array of condition -> value, which will be checked against the cache definition looking for matches. Stores is an ordered array of store instance names to map to, using the names declared in the `stores` array discussed in the above section.

If every condition defined in the conditions array is satisfied, the definition will be mapped to each of the stores, with the first store taking preference. If not, the definition will be checked against the next ruleset in the list. `conditions` is not required in a ruleset. If it is omitted, every definition will map to the stores in that ruleset, if they have not already been mapped to a higher ruleset. `stores` is required in every ruleset. If there are no rulesets defined for a mode, or there are no rulesets that a definition can match, the definition will fall through to the default store instance used for that mode.

#### Preinstalled Cache Required Config
##### APCu
```
"apcu1": {
      "type": "apcu",
      "config": {
        "prefix": "mdl"
      }
    }
```

##### File Cache
```
"file1": {
      "type": "file",
      "config": {
        "path": "/tmp/filecache",
        "autocreate": 1
      }
    }
```

##### Memcached
```"memcached1": {
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
```
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
```
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

### $CFG settings
Once a JSON has been defined to control the caching, a variable inserted into config.php can be used to control the path the plugin uses as a configuration file.
```
$CFG->tool_forcedcache_config_path = 'path/to/config.json';
```
If this is not supplied, the plugin will default to `config.json` inside of the plugin directory.
Once the path is decided on, the configuration can be viewed. See [Debugging](#debugging) for more information.

Alternatively, config can be set inside of config.php, by creating an associative PHP array with an identical structure to the JSON.

```
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

This will have identical behaviour to reading this config from the JSON.
*Note: Only an array OR a path can be specified. It is not valid to declare both at once.*


When the configuration is suitable, the plugin can be enabled by setting a second config variable inside config.php
```
$CFG->alternative_cache_factory_class = 'tool_forcedcache_cache_factory';
```
This will set caching to be readonly, and force the configuration specified in the JSON.

## Debugging
To assist in debugging the configuration, `admin/tool/forcedcache/index.php` will display some information about the status of the plugin.
If there are any errors reported when creating configuration from the JSON file, the error message will be displayed on this page. If the JSON is able to be parsed, the rulesets configuration will be displayed for each of the caching modes.

If the plugin has been enabled, you can also visit `cache/admin.php` to view the overall configuration. If there are any store instances defined in the JSON that are not appearing in the list of configured instances, it means that a store instance was unable to be created from the supplied configuration. Check the `config` field for the store inside the JSON.

## Support
If you have issues please log them in github here

https://github.com/catalyst/moodle-tool_forcedcache/issues

Please note our time is limited, so if you need urgent support or want to
sponsor a new feature then please contact Catalyst IT Australia:

https://www.catalyst-au.net/contact-us

This plugin was developed by Catalyst IT Australia:

https://www.catalyst-au.net/

<img alt="Catalyst IT" src="https://cdn.rawgit.com/CatalystIT-AU/moodle-auth_saml2/master/pix/catalyst-logo.svg" width="400">
