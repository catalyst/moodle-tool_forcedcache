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

class tool_forcedcache_cache_config extends cache_config {

    //FOR NOW RETURN TRUE, LATER CHECK IF CONFIG TEMPLATE (or whatever) IS SETUP CORRECTLY
    //public static function config_file_exists() {return true;}

    //THIS IS THE JUICE. THIS WILL RETURN THE CONFIG WE SET FROM RULESETS ETC.
    // MANY MORE FUNCTIONS WILL BE CALLED FROM HERE TO SETUP THE CONFIG

    // INITIAL TESTING, POINT TO EXAMPLE FILE
    protected function include_configuration() {
        /*include(__DIR__.'/../config.php');
        try {
            $this->generate_config_array();
        } catch(Exception $e) {
        }*/
        return $this->generate_config_array();
    }

    private function generate_config_array() {
        // READFILE
        $config = $this->read_config_file();
        // GENERATE STORES CONFIG
        $stores = $this->generate_store_instance_config($config['stores']);

        //GENERATE MODE MAPPINGS
        $modemappings = $this->generate_mode_mapping($config['rules']);

        // GENERATE DEFINITIONS
        $definitions = tool_forcedcache_cache_config_writer::locate_definitions();

        // GENERATE DEFINITIONS FROM RULESETS
        $definitionmappings = $this->generate_definition_mappings_from_rules($config['rules'], $definitions);

        // GENERATE LOCKS
        $locks = $this->generate_locks();

        // GENERATE SITEIDENTIFIER
        $siteidentifier = cache_helper::get_site_identifier();

        //Throw it all into an array and return
        return array(
            'siteidentifier' => $siteidentifier,
            'stores' => $stores,
            'modemappings' => $modemappings,
            'definitions' => $definitions,
            'definitionmappings' => $definitionmappings,
            'locks' => $locks
        );
    }

    // TODO safety around the reads
    private function read_config_file() {
        $filedata = file_get_contents(__DIR__.'/../config.json');
        return json_decode($filedata, true);
    }

    // TODO, if no store exists for a mode, use the configured default.
    private function generate_store_instance_config($stores) {
        $storesarr = array();
        foreach ($stores as $name => $store) {
            $storearr = array();
            $storearr['name'] = $name;
            $storearr['plugin'] = $store['type'];
            // Assume all configuration is correct.
            // If anything borks, we will fallback to core caching.
            $storearr['configuration'] = $store['config'];

            $classname = 'cachestore_'.$store['type'];
            $storearr['class'] = $classname;

            // Now for the derived config from the store information provided.
            // Manually require the cache/lib.php file to get cache classes.
            $cachepath = __DIR__.'/../../../../cache/stores/' . $store['type'] . '/lib.php';
            require_once($cachepath);
            $storearr['features'] = $classname::get_supported_features();
            $storearr['modes'] = $classname::get_supported_modes();

            // Handle default in a separate case
            $storearr['default'] = false;

            // Mappingsonly enabled as we will be using rulesets to bind all.
            $storearr['mappingsonly'] = 'false';

            // Force default locking... For now... (how mysterious).
            $storearr['lock'] = 'cachelock_file_default';

            $storesarr[$name] = $storearr;
        }
        return $storesarr;
    }

    private function generate_mode_mapping($rules) {
        // Here we must decide on how the stores are going to be used
        $modemappings = array();


        // Check all 3 modes sequentially.
        // TODO Check mode is supported by store and exception.
        // TODO Check store exists before mapping it.
        // TODO Ensure sorting isnt borked. Shouldnt matter, as we will explicitly bind it.
        // TODO Ensure config.json is properly formed/ordered (indexes)

        $sort = 0;
        $modemappings = array_merge($modemappings,
            $this->create_mappings($rules['application'], cache_store::MODE_APPLICATION, $sort));
        $sort = count($modemappings);

        // Now do the exact same for Session.
        $modemappings = array_merge($modemappings,
            $this->create_mappings($rules['session'], cache_store::MODE_SESSION, $sort));
        $sort += count($modemappings);

        // Finally for Request.
        $modemappings = array_merge($modemappings,
            $this->create_mappings($rules['request'], cache_store::MODE_REQUEST, $sort));

        return $modemappings;
    }

    private function create_mappings($rules, $mode, $sortstart) {
        $mappedstores = array();
        $sort = $sortstart;

        if (count($rules) === 0) {
            return array();
        }

        foreach ($rules['local'] as $key => $mapping) {
            // Create the mapping.
            $maparr = [];
            $maparr['mode'] = $mode;
            $maparr['store'] = $mapping;
            $maparr['sort'] = $sort;
            $modemappings[$sort] = $maparr;
            $sort++;

            // Now store the mapping name and mode to prevent duplication.
            $mappedstores[] = $mapping;
        }

        // Now we construct the non-locals, after checking they aren't already mapped.
        foreach ($rules['non-local'] as $key => $mapping) {
            if (in_array($mapping, $mappedstores)) {
                continue;
            }

            // Create the mapping.
            $maparr = [];
            $maparr['mode'] = $mode;
            $maparr['store'] = $mapping;
            $maparr['sort'] = $sort;
            $modemappings[$sort] = $maparr;
            $sort++;

            // Now store the mapping name and mode to prevent duplication.
            $mappedstores[] = $mapping;
        }

        return $modemappings;
    }

    private function generate_definition_mappings_from_rules($rules, $definitions) {
        $defmappings = array();
        $num = 1;
        foreach ($definitions as $defname => $definition) {
            // Find the mode of the definition to discover the mappings.
            $mode = $definition['mode'];

            // Decide on ruleset based on mode. NOT SURE IF NEEDED
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
            if (count($ruleset) === 0) {
                continue;
            }
            // Now decide if localised.
            if (array_key_exists('canuselocalstore', $definition) &&
                $definition['canuselocalstore']) {
                $ruleset = $ruleset['local'];
            } else {
                $ruleset = $ruleset['non-local'];
            }

            // Use the rulecount to construct the ordering.
            $numrules = count($ruleset);

            // Now foreach rule, create the mapping.
            // TODO Ensure mapping exists
            foreach ($ruleset as $key => $rule) {
                $mappingarr = array();
                $mappingarr['store'] = $rule;
                $mappingarr['definition'] = $defname;
                $mappingarr['sort'] = $numrules;

                // Now write to the master mapping array.
                $defmappings[$num] = $mappingarr;
                $num++;
            }
        }
        return $defmappings;
    }

    // TODO figure out whether locking needs to be added to this.
    // Taken from core default configuration.
    private function generate_locks() {
        return array(
            'default_file_lock' => array(
                'name' => 'cachelock_file_default',
                'type' => 'cachelock_file',
                'dir' => 'filelocks',
                'default' => true
            )
        );
    }
}
