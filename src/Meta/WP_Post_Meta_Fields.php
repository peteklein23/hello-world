<?php

namespace PeteKlein\WP\PostCollection\Meta;

class WP_Post_Meta_Fields
{
    public $definitions = [];
    public $meta_fields = [];

    public function add_definition(string $key, $default)
    {
        $this->definitions[] = new WP_Meta_Definition($key, $default);
    }

    private function get_meta_keys()
    {
        return array_column($this->definitions, 'key');
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

    private function create_post_metas(array $grouped_results)
    {
        foreach ($grouped_results as $post_id => $post_meta) {
            $post_metas = new WP_Post_Metas($post_id);

            foreach ($post_meta as $m) {
                $definition = $this->get_definition($m->meta_key);
                $value = $definition->value_or_default($m->meta_value);
                $post_metas->add_result($m->meta_key, $value);
            }
            $this->meta_fields[] = $post_metas;
        }

        return $this->meta_fields;
    }

    public function fetch(array $post_ids)
    {
        global $wpdb;

        $post_list = join(',', $post_ids);
        $keys = $this->get_meta_keys();
        $key_list = "'" . join("','", $keys) . "'";

        $query = "SELECT 
            * 
        FROM $wpdb->postmeta
        WHERE meta_key IN ($key_list)
        AND post_id IN ($post_list)";

        // TODO: error handling
        $results = $wpdb->get_results($query);
        $grouped_results = $this->group_results($results);
        
        return $this->create_post_metas($grouped_results);
    }

    private function get_definition(string $key)
    {
        foreach ($this->definitions as $md) {
            if ($md->key === $key) {
                return $md;
            }
        }

        return null;
    }

    public function get(int $post_id)
    {
        foreach ($this->meta_fields as $mr) {
            if ($mr->post_id === $post_id) {
                return $mr;
            }
        }

        return null;
    }

    public function set(int $post_id, array $valuesMap)
    {
    }
}
