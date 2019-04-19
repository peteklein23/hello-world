<?php

namespace PeteKlein\WP\PostCollection\Taxonomy;

class Post_Taxonomies
{
    public $post_id;
    public $fields = [];
    public $taxonomy_list = [];

    public function __construct(int $post_id)
    {
        $this->post_id = $post_id;
    }

    public function add_field(string $taxonomy, $default)
    {
        $this->fields[] = new Post_Taxonomy_Field($taxonomy, $default);

        return $this;
    }

    public function get_field(string $taxonomy)
    {
        foreach ($this->taxonomy as $taxonomy) {
            if ($field->taxonomy === $taxonomy) {
                return $field;
            }
        }

        return null;
    }

    private function has_fields()
    {
        return !empty($this->fields);
    }

    private function list_taxonomies()
    {
        return array_column($this->fields, 'taxonomy');
    }

    private function populate_missing_values()
    {
        foreach ($this->fields as $field) {
            if (empty($this->taxonomy_list[$field->taxonomy])) {
                $this->taxonomy_list[$field->taxonomy] = $field->default;
            }
        }

        return true;
    }

    public function set_taxonomy(string $taxonomy, \WP_Term $term)
    {
        if (empty($this->taxonomy_list[$taxonomy])) {
            $this->taxonomy_list[$taxonomy] = [];
        }

        $this->taxonomy_list[$taxonomy][] = $term;
    }

    public function set_fields(array $fields)
    {
        foreach ($fields as $field) {
            if (!($field instanceof Post_Taxonomy_Field)) {
                return new \WP_Error(
                    'post_taxonomy_field_needed',
                    __('Sorry, all values passed must be an instance of Post_Taxonomy_Field', 'peteklein'),
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
            $taxonomy = $result->taxonomy;
            $term = new \WP_Term($result);

            $this->set_taxonomy($taxonomy, $term);
        }

        $this->populate_missing_values();
    }

    public function get(string $taxonomy)
    {
        if (!empty($this->taxonomy_list[$taxonomy])) {
            return $this->taxonomy_list[$taxonomy];
        }

        return null;
    }

    public function list()
    {
        return $this->taxonomy_list;
    }

    public function fetch()
    {
        global $wpdb;

        $this->taxonomy_list = [];

        if (!$this->has_fields()) {
            return true;
        }

        $taxonomies = $this->list_taxonomies();
        $taxonomy_list = "'" . join("', '", $taxonomies) . "'";

        $query = "SELECT
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
            AND tr.object_id = $this->post_id";

        $results = $wpdb->get_results($query);
        if ($results === false) {
            return new \WP_Error(
                'fetch_post_taxonomies_failed',
                __('Sorry, fetching the post taxonomies failed.', 'peteklein'),
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
