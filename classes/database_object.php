<?php

namespace local_coursegoals;

defined('MOODLE_INTERNAL') || die();

use Exception;

abstract class database_object
{
    public int $id;

    abstract static function create(\stdClass $fields);
    abstract function delete() : bool;
    abstract function update(\stdClass $fields) : bool;

    /**
     * Returns database table name related to current class
     *
     * @return string database table name
     */
    protected static function get_table_name(): string
    {
        return static::TABLE;
    }


    /**
     * @param int $id instance id of current class
     *
     * @throws \dml_exception A DML specific exception is thrown for any errors.
     */
    public function __construct(int $id)
    {
        global $DB;

        $entry = $DB->get_record(static::get_table_name(), ['id' => $id]);
        if (!$entry) {
            return false;
        } else {
            $fields = (array)$entry;
            foreach ($fields as $field => $value) {
                $this->{$field} = $value;
            }
        }
    }

    /**
     * Creates new entry for the class in database and returns new instance
     *
     * @param \stdClass $fields object containing fields depending on exact class
     * @return mixed Instance of original class
     *
     * @throws \coding_exception
     */
    protected static function _create(\stdClass $fields)
    {
        global $DB;

        self::validate_create_fields($fields);

        if (!empty($fields->id)) {
            unset($fields->id);
        }
        $id = $DB->insert_record(static::get_table_name(), $fields);

        if (!$id) {
            throw new \coding_exception('Database object creation error');
        }

        $class = get_called_class();
        return new $class($id);
    }

    /**
     * Deletes instance of the class.
     *
     * @return bool whether instance is successfully deleted
     * @throws \dml_exception A DML specific exception is thrown for any errors.
     * @throws \coding_exception
     */
    protected function _delete(): bool
    {
        global $DB;

        if (!$this->can_be_deleted()) {
            throw new \coding_exception('Database object can not be deleted due to dependencies.');
        }

        return $DB->delete_records(static::get_table_name(), ['id' => $this->id]);
    }

    /**
     * Updates fields of the current instance in the database.
     *
     * @return bool whether instance is successfully deleted
     * @throws \dml_exception A DML specific exception is thrown for any errors.
     * @throws \coding_exception
     */
    protected function _update(\stdClass $fields): bool
    {
        global $DB;

        self::validate_update_fields($fields);

        $fields->id = $this->id;
        $result = $DB->update_record(static::get_table_name(), $fields);
        if ($result) {
            $props = get_object_vars($this);
            foreach ($props as $prop => &$value) {
                if (isset($fields->{$prop})) {
                    $this->{$prop} = $fields->{$prop};
                }
            }
        }

        return $result;
    }

    /**
     * @param array|null $conditions
     * @param string $fields
     * @return array
     * @throws \dml_exception
     */
    public static function list(array $conditions = null, $fields = '*', $sort = '') {
        global $DB;
        return $DB->get_records(self::get_table_name(), $conditions, $sort, $fields);
    }

    public static function menu(array $conditions = null) {
        $list = self::list($conditions, 'id');
        $menu = [];
        $class = get_called_class();
        foreach ($list as $item) {
            $instance = new $class($item->id);
            $menu[$item->id] = $instance->get_name();
        }
        return $menu;
    }

    /**
     * Returns whether instance with exact id exists in database of not.
     *
     * @return bool true if exists
     * @throws \dml_exception A DML specific exception is thrown for any errors.
     */
    public static function exists($id): bool
    {
        global $DB;

        return $DB->count_records(self::get_table_name(), ['id' => $id]) > 0;
    }

    /**
     * Returns a list of field names required for creating new instance. Override if needed.
     * @return array
     */
    protected static function get_create_required_fields():array {
        return [];
    }

    /**
     * Returns a list of field names that can not be updated directly by update() function. Override if needed.
     * @return array
     */
    protected static function get_update_prohibited_fields() : array {
        return [];
    }

    /**
     * @param \stdClass $fields
     * @throws \coding_exception
     */
    private static function validate_create_fields(\stdClass $fields) {
        $provided_fields = array_keys((array)$fields);
        $diff = array_diff(static::get_create_required_fields(), $provided_fields);
        if (count($diff) > 0) {
            throw new \coding_exception('Following required fields must be set: ' . implode(',', $diff));
        }
    }

    /**
     * @param \stdClass $fields
     * @throws \coding_exception
     */
    private static function validate_update_fields(\stdClass $fields) {
        $provided_fields = array_keys((array)$fields);
        $diff = array_intersect(static::get_update_prohibited_fields(), $provided_fields);
        if (count($diff) > 0) {
            throw new \coding_exception('Following fields are prohibited to be updated directly: ' . implode(',', $diff));
        }
    }

    /**
     * Override if needed
     * @return bool
     */
    public function can_be_updated() : bool {
        return true;
    }

    /**
     * Override if needed
     * @return bool
     */
    public function can_be_deleted() : bool {
        return true;
    }

    /**
     * Returns formatted name of the instance with filters applied
     * @return string
     */
    public function get_name() : string {
        if (!empty($this->name)) {
            return format_string($this->name);
        }
        return '';
    }

}