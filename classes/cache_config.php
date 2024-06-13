<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This cache_config class generates the configuration array from reading a hardcoded JSON file
 *
 * Instead of the configuration file on shared disk.
 *
 * @package     tool_forcedcache
 * @author      Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_forcedcache_cache_config extends cache_config {

    /**
     * Array to track errors thrown during store instantiation.
     *
     * @var array
     */
    private $storeerrors;

    /**
     * This is a wrapper function that simply wraps around include_configuration,
     * and returns any exception messages.
     */
    public function get_inclusion_errors() {
        global $SESSION;
        unset($SESSION->tool_forcedcache_caching_exception);
        $this->include_configuration();
        if (!empty($SESSION->tool_forcedcache_caching_exception)) {
            $data = $SESSION->tool_forcedcache_caching_exception;
            unset($SESSION->tool_forcedcache_caching_exception);
        } else {
            $data = '';
        }
        return $data;
    }

    /**
     * This is where the magic happens. Instead of loading a file,
     * this generates rulesets based on a JSON file, and binds them to definitions.
     * If there are any errors during this process, it aborts and falls back to core configuration.
     *
     * @return array the configuration array.
     */
    protected function include_configuration() {
        global $CFG, $SESSION;

        try {
            return $this->generate_config_array();
        } catch (Exception $e) {
            // Store the error message in session, helps with debugging from a frontend display.
            // This may be overwritten depending on load order. Best to create a dummy cache instance then check.
            if (!empty($SESSION)) {
                $SESSION->tool_forcedcache_caching_exception = $e->getMessage();
            }

            // If plugin is supposed to be active, rethrow exception, can't continue with broken config.
            if (!empty($CFG->alternative_cache_factory_class)
                && $CFG->alternative_cache_factory_class === 'tool_forcedcache_cache_factory') {
                throw $e;
            }
        }
    }

    /**
     * This is a glue function that grabs all of the different components of the configuration,
     * and stitches them together.
     *
     * @return array the cache configuration array.
     */
    private function generate_config_array(): array {
        global $CFG;

        $config = self::read_config_file();

        // Initialise the error store array.
        $this->storeerrors = [];

        // Generate the stores config.
        $stores = $this->generate_store_instance_config($config['stores']);

        // Generate the mode mappings.
        $modemappings = $this->generate_mode_mapping($config['rules']);

        // Get the definitions.
        $definitions = $this->apply_definition_overrides(tool_forcedcache_cache_config_writer::locate_definitions(),
            $config['definitionoverrides']);

        // Generate definition mappings from rulesets.
        $definitionmappings = $this->generate_definition_mappings_from_rules($config['rules'], $definitions);

        // We can now remove the errored stores from the store array entirely.
        foreach ($this->storeerrors as $store) {
            unset($stores[$store]);
        }

        // Generate locks.
        $locks = $this->generate_locks();

        // Throw it all into an array and return.
        $config = [
            'stores' => $stores,
            'modemappings' => $modemappings,
            'definitions' => $definitions,
            'definitionmappings' => $definitionmappings,
            'locks' => $locks
        ];

        // Get the siteidentifier. Copies pattern from cache_config.
        // If the siteid is not yet known then we do not want it set which
        // means the caches will be disabled further down the chain.
        if (!empty($CFG->siteidentifier)) {
            $config['siteidentifier'] = md5((string) $CFG->siteidentifier);
        }

        return $config;

    }

    /**
     * This reads the JSON file at $path and parses it for use in cache generation.
     * Exceptions are thrown so that caching will fallback to core.
     *
     * @return array Associative array of configuration from JSON or config.
     * @throws cache_exception
     */
    public static function read_config_file(): array {
        global $CFG;
        $arrayexists = !empty($CFG->tool_forcedcache_config_array);
        $pathexists = !empty($CFG->tool_forcedcache_config_path);

        // If path and array are defined, explode, only one can exist.
        if ($arrayexists && $pathexists) {
            throw new cache_exception(get_string('config_path_and_array', 'tool_forcedcache'));
        } else if ($arrayexists) {
            // Check that atleast stores and rules are defined.
            $array = $CFG->tool_forcedcache_config_array;
            if (!array_key_exists('stores', $array) || !array_key_exists('rules', $array)) {
                throw new cache_exception(get_string('config_array_parse_fail', 'tool_forcedcache'));
            }

            // If definitionoverrides is missing (optional), instantiate as empty.
            if (!array_key_exists('definitionoverrides', $array)) {
                $array['definitionoverrides'] = [];
            }

            // Return config array.
            return $array;

        } else if ($pathexists) {
            // Else decide on the path, then try to load it.
            $path = realpath($CFG->tool_forcedcache_config_path);
        }

        // If the json file path is inside dirroot, throw an exception. This
        // should not be allowed as it would expose the configuration.
        if (!empty($path) && strpos($path, $CFG->dirroot) !== false) {
            throw new cache_exception(get_string('config_json_path_invalid', 'tool_forcedcache', [
                'path' => $path,
                'dirroot' => $CFG->dirroot
            ]));
        }

        // Now try to load the JSON.
        if (isset($path) && file_exists($path)) {
            $filedata = file_get_contents($path);
            $config = json_decode($filedata, true);
            if (!empty($config)) {
                // If definitionoverrides is missing (optional), instantiate as empty.
                if (!array_key_exists('definitionoverrides', $config)) {
                    $config['definitionoverrides'] = [];
                }

                return $config;
            } else {
                throw new cache_exception(get_string('config_json_parse_fail', 'tool_forcedcache'));
            }
        } else {
            throw new cache_exception(get_string('config_json_missing', 'tool_forcedcache'));
        }
    }

    /**
     * This instantiates any stores defined in the config,
     * and the default stores, which must always exist.
     *
     * @param array $stores the array of stores declared in the JSON file.
     * @return array a mapped configuration array of store instances.
     * @throws cache_exception
     */
    private function generate_store_instance_config(array $stores): array {
        $storesarr = array();
        foreach ($stores as $name => $store) {

            // First check that all the required fields are present in the store.
            if (!(array_key_exists('type', $store) ||
                  array_key_exists('config', $store))) {
                throw new cache_exception(get_string('store_missing_fields', 'tool_forcedcache', $name));
            }

            $storearr = array();
            $storearr['name'] = $name;
            $storearr['plugin'] = $store['type'];
            // Assume all configuration is correct.
            $storearr['configuration'] = $store['config'];
            $classname = 'cachestore_'.$store['type'];
            $storearr['class'] = $classname;

            // Now for the derived config from the store information provided.
            // Manually require the cache/lib.php file to get cache classes.
            $cachepath = __DIR__.'/../../../../cache/stores/' . $store['type'] . '/lib.php';
            if (!file_exists($cachepath)) {
                throw new cache_exception(get_string('store_bad_type', 'tool_forcedcache', $store['type']));
            }
            require_once($cachepath);
            $storearr['features'] = $classname::get_supported_features();
            $storearr['modes'] = $classname::get_supported_modes();

            // Set these to a default value.
            $storearr['default'] = false;
            $storearr['mappingsonly'] = false;
            $storearr['lock'] = 'cachelock_file_default';

            // Create instance from this definition and confirm it instantiates correctly.
            $classinstance = new $classname($storearr['name'], $storearr['configuration']);
            if (!$classinstance->is_ready()) {
                // Store the errored store here. Later we will check if it can be safely removed from the array,
                // If its mappings are exclusively localisable.
                $this->storeerrors[] = $name;
            }
            $storesarr[$name] = $storearr;
        }

        // Now instantiate the default stores (Must always exist).
        $storesarr = array_merge($storesarr, tool_forcedcache_cache_config_writer::get_default_stores());

        return $storesarr;
    }

    /**
     * This function generates the default mappings for each cache mode.
     *
     * @return array the generated default mode mappings.
     */
    public static function get_default_mode_mappings() : array {
        // Use the defaults from core.
        $modemappings = array(
            array(
                'mode' => cache_store::MODE_APPLICATION,
                'store' => 'default_application',
                'sort' => -1
            ),
            array(
                'mode' => cache_store::MODE_SESSION,
                'store' => 'default_session',
                'sort' => -1
            ),
            array(
                'mode' => cache_store::MODE_REQUEST,
                'store' => 'default_request',
                'sort' => -1
            )
        );

        return $modemappings;
    }

    /**
     * This function generates the mappings for each cache mode after rules are applied.
     *
     * @param array $rules
     * @return array the generated mode mappings post-rules.
     */
    private function generate_mode_mapping(array $rules): array {
        $modetostr = [
            cache_store::MODE_APPLICATION => 'application',
            cache_store::MODE_SESSION => 'session',
            cache_store::MODE_REQUEST => 'request',
        ];

        // Use the defaults from core.
        $modemappings = array_map(function ($modemapping) use ($rules, $modetostr) {
            $modekey = $modetostr[$modemapping['mode']];
            $moderules = $rules[$modekey] ?? null;
            // Only override the default store with the last rule set for a particular mode - if one exists.
            if (!empty($moderules)) {
                $lastrule = end($moderules);
                // Check if the rule has any conditions, if not it will be set
                // as the default store as it is considered the broadest rule.
                if (empty($lastrule['conditions'])) {
                    $modemapping['store'] = reset($lastrule['stores']);
                }
            }
            return $modemapping;
        }, self::get_default_mode_mappings());

        return $modemappings;
    }

    /**
     * This function takes the rules and definitions,
     * and creates mappings from definition->store(s)
     * based on a fallthrough pattern of the rules
     * from the JSON file.
     *
     * @param array $rules the rules array from the JSON
     * @param array $definitions a list of definitions to map.
     * @return array an array of ordered mappings for every definition to its ruleset.
     */
    private function generate_definition_mappings_from_rules(array $rules, array $definitions): array {
        $defmappings = array();
        $num = 1;
        foreach ($definitions as $defname => $definition) {
            // Find the mode of the definition to discover the mappings.
            $mode = $definition['mode'];

            // Decide on ruleset based on mode.
            switch ($mode) {
                case cache_store::MODE_APPLICATION:
                    $ruleset = $rules['application'];
                    break;

                case cache_store::MODE_SESSION:
                    $ruleset = $rules['session'];
                    break;

                case cache_store::MODE_REQUEST:
                    $ruleset = $rules['request'];
            }

            // If no rules are specified for a type,
            // Skip this definition to fall through to defaults.
            if (count($ruleset) === 0) {
                continue;
            }

            // Now decide on the ruleset that matches.
            $stores = array();
            foreach ($ruleset as $rule) {
                if (array_key_exists('conditions', $rule)) {
                    foreach ($rule['conditions'] as $condition => $value) {
                        // Precompute some checks to construct a clean bool condition.
                        $conditionmatches = array_key_exists($condition, $definition) && $value === $definition[$condition];
                        $namematches = ($condition === 'name') && ($defname === $value);

                        // Check if condition isn't present in definition or doesn't match.
                        // If nothing matches, jump out of this ruleset entirely.
                        if (!($conditionmatches || $namematches)) {
                            continue 2;
                        }
                    }
                }

                // If we get here, there are no conditions in this ruleset, or every one was a match.
                // We can safely bind stores, then break.
                $stores = $rule['stores'];
                break;
            }

            // Weirdness here. Some stuff sorts lowest as priority, Mappings sort highest as priority.
            $sort = count($stores);
            foreach ($stores as $store) {
                // Create the mapping for the definition -> store and add to the master list.
                $mappingarr = array();
                $mappingarr['store'] = $store;
                $mappingarr['definition'] = $defname;
                $mappingarr['sort'] = $sort;

                $defmappings[$num] = $mappingarr;

                // Increment the mapping counter, decrement local sorting counter for definition.
                $sort--;
                $num++;
            }
        }

        // We must now check any stores that were not ready during instantiation.
        // If *ONLY* localisable definitions are mapped, we can drop those mappings from the config.
        if (!empty($this->storeerrors)) {
            foreach ($this->storeerrors as $storename) {
                foreach ($defmappings as $key => $mapping) {
                    // Check if the mapping is to the naughty store.
                    if ($mapping['store'] !== $storename) {
                        continue;
                    }

                    // Check if this can be localised.
                    $definition = $definitions[$mapping['definition']];
                    if (empty($definition['canuselocalstore']) || !$definition['canuselocalstore']) {
                        throw new cache_exception(get_string('store_not_ready', 'tool_forcedcache', $storename));
                    } else {
                        // This mapping can be deleted, and the default fallthrough used.
                        // If the above exception is ever thrown, the config is hosed anyway.
                        unset($defmappings[$key]);
                    }
                }

                // Now reset the array keys for the main array, and move on.
                $defmappings = array_values($defmappings);
            }
        }

        return $defmappings;
    }

    /**
     * This is a copy of the default locking configuration used by core.
     * TODO figure out whether locking needs to be implemented in a robust way.
     *
     * @return array array of locks to use.
     */
    private function generate_locks() : array {
        return array(
            'default_file_lock' => array(
                'name' => 'cachelock_file_default',
                'type' => 'cachelock_file',
                'dir' => 'filelocks',
                'default' => true
            )
        );
    }

    /**
     * Takes the definitions array, and forces that specified config into the definition.
     * This may be unsafe, we are trusting that the configuration here is sane. Unforseen errors may arise.
     *
     * @param array $definitions the definitions to override.
     * @param array $overrides the overrides to apply
     * @return array the overridden definition values.
     */
    private function apply_definition_overrides(array $definitions, array $overrides): array {
        foreach ($overrides as $definition => $overrideitems) {
            if (array_key_exists($definition, $definitions)) {
                foreach ($overrideitems as $key => $item) {
                    $definitions[$definition][$key] = $item;
                }
            } else {
                throw new cache_exception(get_string('definition_not_found', 'tool_forcedcache', $definition));
            }
        }

        return $definitions;
    }

    /**
     * Overridden config file check.
     *
     * It does not matter if a file is present, so tell Moodle it always is to prevent attempts to create one.
     * There seems to be an edge case where this can cause the readonly to initialise badly based on factory state.
     *
     * @return bool
     */
    public static function config_file_exists() {
        return true;
    }

    /**
     * Hack alert. Wrapper needed for protected function, as we cannot do multiple
     * inheritance and the writer needs to also be able to load the config.
     *
     * @return array
     */
    public function include_configuration_wrapper(): array {
        return $this->include_configuration();
    }
}
