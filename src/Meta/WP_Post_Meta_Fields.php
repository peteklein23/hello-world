<?php

namespace PeteKlein\WP\PostCollection\Meta;

/**
 * Gets and references metadata across multiple posts
 */
class WP_Post_Meta_Fields
{
    private $fields = [];
    private $values = [];

    public function add_field(string $key, $default, bool $single = true)
    {
        $this->fields[] = new WP_Post_Meta_Field($key, $default, $single);
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

    public function list(int $post_id)
    {
        if (!empty($this->values['post_id'])) {
            return $this->values['post_id'];
        }

        return null;
    }

    private function list_keys()
    {
        return array_column($this->fields, 'key');
    }

    public function get(int $post_id, string $key)
    {
        $post = null;
        if (!empty($this->values['post_id'])) {
            $post = $this->values['post_id'];
        }

        if (!empty($post[$key])) {
            return $post[$key];
        }

        return null;
    }

    private function populate_missing_values($formatted_results)
    {
        foreach ($formatted_results as $post_id => &$meta) {
            foreach ($this->fields as $field) {
                if (empty($meta[$field->key])) {
                    $meta[$field->key] = $field->default;
                }
            }
        }

        return $formatted_results;
    }

    private function format_results(array $results)
    {
        $formatted_results = [];

        foreach ($results as $result) {
            $post_id = $result->post_id;
            $key = $result->meta_key;
            $value = $result->meta_value;
            $field = $this->get_field($key);

            if (empty($formatted_results[$post_id])) {
                $formatted_results[$post_id] = [];
            }

            if ($field->single) {
                $formatted_results[$post_id][$key] = $value;
                continue;
            }

            if (empty($formatted_results[$post_id][$key])) {
                $formatted_results[$post_id][$key] = [];
            }
            $formatted_results[$post_id][$key][] = $value;
        }

        return $this->populate_missing_values($formatted_results);
    }

    public function fetch(array $post_ids)
    {
        global $wpdb;

        $post_list = join(',', $post_ids);
        $keys = $this->list_keys();
        $key_list = "'" . join("','", $keys) . "'";

        $query = "SELECT 
            * 
        FROM $wpdb->postmeta
        WHERE meta_key IN ($key_list)
        AND post_id IN ($post_list)";

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

        $formatted_results = $this->format_results($results);
        $this->values = $formatted_results;
        
        return $this->values;
    }
}
