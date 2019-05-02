<?php

namespace PeteKlein\WP\PostCollection\Taxonomy;

class PostTaxonomyCollection
{
    public $fields = [];
    public $taxonomyList = [];

    public function addField(string $taxonomy, $default)
    {
        $this->fields[] = new PostTaxonomyField($taxonomy, $default);

        return $this;
    }

    private function hasFields()
    {
        return !empty($this->fields);
    }

    private function getTaxonomies()
    {
        return array_column($this->fields, 'taxonomy');
    }

    public function get(int $postId)
    {
        foreach ($this->taxonomyList as $postTaxonomies) {
            if ($postTaxonomies->postId === $postId) {
                return $postTaxonomies;
            }
        }

        return null;
    }

    public function list()
    {
        $formatted_list = [];
        foreach ($this->taxonomyList as $postTaxonomies) {
            $formatted_list[$postTaxonomies->postId] = $postTaxonomies->list();
        }

        return $formatted_list;
    }
    
    private function populateTaxonomiesFromResults($results)
    {
        $formatted_results = [];

        // sort posts by IDs
        foreach ($results as $result) {
            $postId = $result->post_id;

            if (empty($formatted_results[$postId])) {
                $formatted_results[$postId] = [];
            }

            $formatted_results[$postId][] = $result;
        }

        // create objects and set fields and values
        foreach ($formatted_results as $postId => $results) {
            $postTaxonomies = new PostTaxonomies($postId);
            $setFields = $postTaxonomies->setFields($this->fields);
            
            if (is_wp_error($setFields)) {
                return $setFields;
            }
            $postTaxonomies->populateFromResults($results);
            
            $this->taxonomyList[] = $postTaxonomies;
        }
        
        return true;
    }

    public function fetch(array $postIds)
    {
        global $wpdb;

        $this->taxonomyList = [];
        
        if (!$this->hasFields()) {
            return true;
        }

        $taxonomies = $this->getTaxonomies();
        $taxonomyList = "'" . join("', '", $taxonomies) . "'";
        $postList = join(', ', $postIds);

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
        WHERE tt.taxonomy IN ($taxonomyList)
            AND tr.object_id IN ($postList)";

        $results = $wpdb->get_results($query);

        $this->populateTaxonomiesFromResults($results);

        return true;
    }
}
