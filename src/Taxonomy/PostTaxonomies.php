<?php

namespace PeteKlein\WP\PostCollection\Taxonomy;

class PostTaxonomies
{
    public $postId;
    public $fields = [];
    public $taxonomyList = [];

    public function __construct(int $postId)
    {
        $this->postId = $postId;
    }

    public function addField(string $taxonomy, $default)
    {
        $this->fields[] = new PostTaxonomyField($taxonomy, $default);

        return $this;
    }

    public function getField(string $taxonomy)
    {
        foreach ($this->taxonomy as $taxonomy) {
            if ($field->taxonomy === $taxonomy) {
                return $field;
            }
        }

        return null;
    }

    private function hasFields()
    {
        return !empty($this->fields);
    }

    private function listTaxonomies()
    {
        return array_column($this->fields, 'taxonomy');
    }

    private function populateMissingValues()
    {
        foreach ($this->fields as $field) {
            if (empty($this->taxonomyList[$field->taxonomy])) {
                $this->taxonomyList[$field->taxonomy] = $field->default;
            }
        }

        return true;
    }

    public function setTaxonomy(string $taxonomy, \WP_Term $term)
    {
        if (empty($this->taxonomyList[$taxonomy])) {
            $this->taxonomyList[$taxonomy] = [];
        }

        $this->taxonomyList[$taxonomy][] = $term;
    }

    public function setFields(array $fields)
    {
        foreach ($fields as $field) {
            if (!($field instanceof PostTaxonomyField)) {
                return new \WP_Error(
                    'post_taxonomy_field_needed',
                    __('Sorry, all values passed must be an instance of PostTaxonomyField', 'peteklein'),
                    [
                        'field' => $field
                    ]
                );
            }
            $this->fields[] = $field;
        }

        return $this;
    }

    public function populateFromResults(array $results)
    {
        foreach ($results as $result) {
            $taxonomy = $result->taxonomy;
            $term = new \WP_Term($result);

            $this->setTaxonomy($taxonomy, $term);
        }

        $this->populateMissingValues();
    }

    public function get(string $taxonomy)
    {
        if (!empty($this->taxonomyList[$taxonomy])) {
            return $this->taxonomyList[$taxonomy];
        }

        return null;
    }

    public function list()
    {
        return $this->taxonomyList;
    }

    public function fetch()
    {
        global $wpdb;

        $this->taxonomyList = [];

        if (!$this->hasFields()) {
            return true;
        }

        $taxonomies = $this->listTaxonomies();
        $taxonomyList = "'" . join("', '", $taxonomies) . "'";

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
        WHERE tt.taxonomy IN ($taxonomyList)
            AND tr.object_id = $this->postId";

        $results = $wpdb->get_results($query);
        if ($results === false) {
            return new \WP_Error(
                'fetch_post_taxonomies_failed',
                __('Sorry, fetching the post taxonomies failed.', 'peteklein'),
                [
                    'post_id' => $this->postId,
                    'fields' => $this->fields
                ]
            );
        }

        $this->populateFromResults($results);

        return true;
    }
}
