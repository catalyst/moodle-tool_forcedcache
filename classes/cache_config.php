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
        include(__DIR__.'/../config.php');
        return $configuration;
    }
}
