<?php

namespace PeteKlein\WP\PostCollection\Taxonomy;

class Post_Taxonomy_Collector
{
    public $fields = [];
    public $taxonomy_list = [];

    public function add_field(string $taxonomy, $default)
    {
        $this->fields[] = new Post_Taxonomy_Field($taxonomy, $default);

        return $this;
    }

    private function has_fields()
    {
        return !empty($this->fields);
    }

    private function get_taxonomies()
    {
        return array_column($this->fields, 'taxonomy');
    }

    public function get(int $post_id)
    {
        foreach ($this->taxonomy_list as $post_taxonomies) {
            if ($post_taxonomies->post_id === $post_id) {
                return $post_taxonomies;
            }
        }

        return null;
    }

    public function list()
    {
        $formatted_list = [];
        foreach ($this->taxonomy_list as $post_taxonomies) {
            $formatted_list[$post_taxonomies->post_id] = $post_taxonomies->list();
        }

        return $formatted_list;
    }
    
    private function populate_taxonomies_from_results($results)
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
            $post_taxonomies = new Post_Taxonomies($post_id);
            $set_fields = $post_taxonomies->set_fields($this->fields);
            
            if (is_wp_error($set_fields)) {
                return $set_fields;
            }
            $post_taxonomies->populate_from_results($results);
            
            $this->taxonomy_list[] = $post_taxonomies;
        }
        
        return true;
    }

    public function fetch(array $post_ids)
    {
        global $wpdb;

        $this->taxonomy_list = [];
        
        if (!$this->has_fields()) {
            return true;
        }

        $taxonomies = $this->get_taxonomies();
        $taxonomy_list = "'" . join("', '", $taxonomies) . "'";
        $post_list = join(', ', $post_ids);

        $query = "SELECT
            tr.object_id as post_id,
            tt.term_id,
            t.name,
            t.slug,
            t.term_group,
            tt.term_taxonomy_id,
            tt.taxonomy,
            tt.description,
            tt.parent,
            tt.count
        FROM $wpdb->term_relationships tr
        INNER JOIN $wpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN $wpdb->terms AS t ON t.term_id = tt.term_id
        WHERE tt.taxonomy IN ($taxonomy_list)
            AND tr.object_id IN ($post_list)";

        $results = $wpdb->get_results($query);

        $this->populate_taxonomies_from_results($results);

        return true;
    }
}
