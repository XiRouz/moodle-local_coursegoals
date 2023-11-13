<?php
/**
 * @package    local_coursegoals
 * @copyright  2023 Lavrentev Semyon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$subplugins = (array) json_decode(file_get_contents(__DIR__ . "/subplugins.json"))->plugintypes;