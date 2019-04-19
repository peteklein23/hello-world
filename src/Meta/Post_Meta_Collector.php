<?php

namespace PeteKlein\WP\PostCollection\Meta;

/**
 * Gets and references metadata across multiple posts
 */
class Post_Meta_Collector
{
    private $fields = [];
    private $meta_list = [];

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

    public function list()
    {
        $formatted_list = [];
        foreach ($this->meta_list as $meta) {
            $formatted_list[$meta->post_id] = $meta->list();
        }

        return $formatted_list;
    }

    public function get(int $post_id)
    {
        foreach ($this->meta_list as $meta) {
            if ($meta->post_id === $post_id) {
                return $meta;
            }
        }

        return null;
    }

    private function list_keys()
    {
        return array_column($this->fields, 'key');
    }

    public function get_value(int $post_id, string $key)
    {
        $post = null;
        if (!empty($this->meta_list[$post_id])) {
            $post = $this->meta_list[$post_id];
        }

        if (!empty($post[$key])) {
            return $post[$key];
        }

        return null;
    }
    
    private function has_fields()
    {
        return !empty($this->fields);
    }

    private function populate_meta_from_results(array $results)
    {
        $formatted_results = [];

        // sort posts by IDs
        foreach ($results as $result) {
            $post_id = $result->post_id;

            if (empty($formatted_results[$post_id])) {
                $formatted_results[$post_id] = [];
            }

            $formatted_results[$post_id][] = $result;
        }

        // create Post_Meta objects and set fields and values
        foreach ($formatted_results as $post_id => $results) {
            $post_meta = new Post_Meta($post_id);
            $set_fields = $post_meta->set_fields($this->fields);
            if (is_wp_error($set_fields)) {
                return $set_fields;
            }
            $post_meta->populate_from_results($results);
            
            $this->meta_list[] = $post_meta;
        }

        return true;
    }

    public function fetch(array $post_ids)
    {
        global $wpdb;
        
        // empty meta list
        $this->meta_list = [];

        if (!$this->has_fields()) {
            return true;
        }

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

        $populate_meta = $this->populate_meta_from_results($results);
        if (is_wp_error($populate_meta)) {
            return $populate_meta;
        }

        return true;
    }
}
