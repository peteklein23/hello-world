<?php

namespace PeteKlein\WP\PostCollection\Taxonomy;

class WP_Post_Taxonomies
{
    public $definitions = [];
    public $post_terms = null;

    public function add_definition(string $taxonomy, $default)
    {
        $this->definitions[] = new WP_Taxonomy_Definition($taxonomy, $default);

        return $this;
    }

    private function get_taxonomy_slugs()
    {
        return array_column($this->definitions, 'slug');
    }
    
    /** group by post id */
    private function group_results(array $results)
    {
        $grouped_results = [];
        
        foreach ($results as $r) {
            $post_id = $r->post_id;
            if (empty($results[$post_id])) {
                $results[$post_id] = [];
            }
            unset($r->post_id);
            $grouped_results[$post_id][] = $r;
        }

        return $grouped_results;
    }

    /** */
    private function create_post_terms(array $grouped_results)
    {
        foreach ($grouped_results as $post_id => $terms) {
            $post_terms = new WP_Post_Terms($post_id);

            foreach ($terms as $term_obj) {
                $post_terms->add_term($term_obj);
            }
            $this->post_terms[] = $post_terms;
        }

        return $this->post_terms;
    }

    /** run query to get post_terms */
    public function fetch(array $post_ids, bool $refetch = false)
    {
        global $wpdb;

        if (!empty($this->post_terms) && !$refetch) {
            return $this->post_terms;
        }

        $slugs = $this->get_taxonomy_slugs();
        $slug_list = "'" . join("', '", $slugs) . "'";
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
        WHERE tt.taxonomy IN ($slug_list)
            AND tr.object_id IN ($post_list)";

        $results = $wpdb->get_results($query);
        $grouped_results = $this->group_results($results);
        $post_post_terms = $this->create_post_terms($grouped_results);

        return $this;
    }
}
