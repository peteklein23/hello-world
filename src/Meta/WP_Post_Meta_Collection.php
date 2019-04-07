<?php

namespace PeteKlein\WP\PostCollection\Meta;

/**
 * Gets and references metadata across multiple posts
 */
class WP_Post_Meta_Collection
{
    public $definition_list = null;
    public $meta_lists = [];

    public function __construct()
    {
        $this->definition_list = new WP_Meta_Definition_List();
    }

    public function add_definition(string $key, $default)
    {
        $this->definition_list->add($key, $default);
    }

    /** group results by post ID */
    private function group_results(array $results)
    {
        $grouped_results = [];
        foreach ($results as $result) {
            $post_id = $result->post_id;
            if (empty($grouped_results[$post_id])) {
                $grouped_results[$post_id] = [];
            }

            $grouped_results[$post_id][] = $result;
        }

        return $grouped_results;
    }

    private function create_post_meta_lists(array $grouped_results)
    {
        foreach ($grouped_results as $post_id => $post_meta) {
            $post_meta_list = new WP_Post_Meta_List($post_id, $this->definition_list);

            foreach ($post_meta as $m) {
                $post_meta_list->add_meta($m->meta_key, $m->meta_value);
            }
            $this->meta_lists[] = $post_meta_list;
        }

        return $this->meta_lists;
    }

    public function fetch(array $post_ids)
    {
        global $wpdb;

        $post_list = join(',', $post_ids);
        $keys = $this->definition_list->list_keys();
        $key_list = "'" . join("','", $keys) . "'";

        $query = "SELECT 
            * 
        FROM $wpdb->postmeta
        WHERE meta_key IN ($key_list)
        AND post_id IN ($post_list)";

        // TODO: error handling
        $results = $wpdb->get_results($query);
        $grouped_results = $this->group_results($results);
        
        return $this->create_post_meta_lists($grouped_results);
    }

    public function get(int $post_id)
    {
        foreach ($this->meta_lists as $mr) {
            if ($mr->post_id === $post_id) {
                return $mr;
            }
        }

        return null;
    }
}
