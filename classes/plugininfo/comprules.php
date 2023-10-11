<?php

/**
 * @package    local_coursegoals
 * @copyright  2023 Lavrentev Semyon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursegoals\plugininfo;

defined('MOODLE_INTERNAL') || die();

/**
 * This class defines comprules subplugininfo
 */
class comprules extends \core\plugininfo\base {
    /** @var string the plugintype name, eg. mod, auth or workshopform */
    public $type = 'local';
    /** @var string full path to the location of all the plugins of this type */
    public $typerootdir = '/local/coursegoals/comprules';
    /** @var string the plugin name, eg. assignment, ldap */
    public $name = 'comprules';
    /** @var string the localized plugin name */
    public $displayname = 'Completion rules of course goal tasks';
    /** @var string the plugin source, one of core_plugin_manager::PLUGIN_SOURCE_xxx constants */
    public $source;
    /** @var string fullpath to the location of this plugin */
    public $rootdir;
    /** @var int|string the version of the plugin's source code */
    public $versiondisk;
    /** @var int|string the version of the installed plugin */
    public $versiondb;
    /** @var int|float|string required version of Moodle core  */
    public $versionrequires;
    /** @var array explicitly supported branches of Moodle core  */
    public $pluginsupported;
    /** @var int first incompatible branch of Moodle core  */
    public $pluginincompatible;
    /** @var mixed human-readable release information */
    public $release = '0.1';
    /** @var array other plugins that this one depends on, lazy-loaded by {@link get_other_required_plugins()} */
    public $dependencies;
    /** @var int number of instances of the plugin - not supported yet */
    public $instances;
    /** @var int order of the plugin among other plugins of the same type - not supported yet */
    public $sortorder;
    /** @var core_plugin_manager the plugin manager this plugin info is part of */
    public $pluginman;
}

