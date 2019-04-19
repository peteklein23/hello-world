<?php

namespace PeteKlein\WP\PostCollection\Meta;

class Post_Meta
{
    public $post_id;
    private $fields = [];
    private $values = [];

    public function __construct(int $post_id)
    {
        $this->post_id = $post_id;
    }

    public function add_field(string $key, $default, bool $single = true)
    {
        $this->fields[] = new Post_Meta_Field($key, $default, $single);

        return $this;
    }

    public function get_field(string $key)
    {
        foreach ($this->fields as $field) {
            if ($field->key === $key) {
                return $field;
            }
        }

        return null;
    }

    private function has_fields()
    {
        return !empty($this->fields);
    }

    private function list_keys()
    {
        return array_column($this->fields, 'key');
    }

    private function populate_missing_values()
    {
        foreach ($this->fields as $field) {
            if (empty($this->values[$field->key])) {
                $this->values[$field->key] = $field->default;
            }
        }
    }

    public function set_value(string $key, string $value)
    {
        $field = $this->get_field($key);
        $unserializedValue = maybe_unserialize($value);

        if (empty($field)) {
            return $this;
        }
        
        if (empty($unserializedValue)) {
            $unserializedValue = $field->default;
        }

        if ($field->single) {
            $this->values[$key] = $unserializedValue;

            return $this;
        }

        if (empty($this->values[$key])) {
            $this->values[$key] = [];
        }

        $this->values[$key][] = $unserializedValue;

        return $this;
    }

    public function set_fields(array $fields)
    {
        foreach ($fields as $field) {
            if (!($field instanceof Post_Meta_Field)) {
                return new \WP_Error(
                    'post_meta_field_needed',
                    __('Sorry, all values passed must be an instance of Post_Meta_Field', 'peteklein'),
                    [
                        'field' => $field
                    ]
                );
            }
            $this->fields[] = $field;
        }

        return $this;
    }

    public function populate_from_results(array $results)
    {
        foreach ($results as $result) {
            $key = $result->meta_key;
            $value = $result->meta_value;
            
            $this->set_value($key, $value);
        }

        $this->populate_missing_values();
    }

    public function get(string $key)
    {
        if (!empty($this->values[$key])) {
            return $this->values[$key];
        }

        return null;
    }

    public function list()
    {
        return $this->values;
    }

    public function fetch()
    {
        global $wpdb;

        // empty values
        $this->values = [];

        if (!$this->has_fields()) {
            return true;
        }

        $keys = $this->list_keys();
        $key_list = "'" . join("','", $keys) . "'";

        $query = "SELECT 
            * 
        FROM $wpdb->postmeta
        WHERE meta_key IN ($key_list)
        AND post_id = $this->post_id";

        $results = $wpdb->get_results($query);
        if ($results === false) {
            return new \WP_Error(
                'fetch_post_meta_failed',
                __('Sorry, fetching the post meta failed.', 'peteklein'),
                [
                    'post_id' => $this->post_id,
                    'fields' => $this->fields
                ]
            );
        }

        $this->populate_from_results($results);

        return true;
    }
}
