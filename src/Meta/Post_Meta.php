<?php

namespace PeteKlein\WP\PostCollection\Meta;

class Post_Meta
{
    private $post_id;
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

    private function populate_missing_values($formatted_results)
    {
        foreach ($this->fields as $field) {
            if (empty($formatted_results[$field->key])) {
                $formatted_results[$field->key] = $field->default;
            }
        }

        return $formatted_results;
    }

    private function format_results(array $results)
    {
        $formatted_results = [];

        foreach ($results as $result) {
            $key = $result->meta_key;
            $value = $result->meta_value;
            $field = $this->get_field($key);

            if ($field->single) {
                $formatted_results[$key] = maybe_unserialize($value);
                continue;
            }

            if (empty($formatted_results[$key])) {
                $formatted_results[$key] = [];
            }
            $formatted_results[$key][] = $value;
        }

        return $this->populate_missing_values($formatted_results);
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

        if (!$this->has_fields()) {
            return $this->values = [];
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
                    'post_ids' => $post_ids,
                    'keys' => $keys
                ]
            );
        }

        return $this->values = $this->format_results($results);
    }
}
