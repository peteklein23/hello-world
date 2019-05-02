<?php

namespace PeteKlein\WP\PostCollection\Meta;

/**
 * Gets and references metadata across multiple posts
 */
class PostMetaCollection
{
    private $fields = [];
    private $metaList = [];

    public function addField(string $key, $default, bool $single = true)
    {
        $this->fields[] = new PostMetaField($key, $default, $single);

        return $this;
    }

    public function getField(string $key)
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
        foreach ($this->metaList as $meta) {
            $formatted_list[$meta->postId] = $meta->list();
        }

        return $formatted_list;
    }

    public function get(int $postId)
    {
        foreach ($this->metaList as $meta) {
            if ($meta->postId === $postId) {
                return $meta;
            }
        }

        return null;
    }

    private function listKeys()
    {
        return array_column($this->fields, 'key');
    }

    public function getValue(int $postId, string $key)
    {
        $post = null;
        if (!empty($this->metaList[$postId])) {
            $post = $this->metaList[$postId];
        }

        if (!empty($post[$key])) {
            return $post[$key];
        }

        return null;
    }
    
    private function hasFields()
    {
        return !empty($this->fields);
    }

    private function populateMetaFromResults(array $results)
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

        // create PostMeta objects and set fields and values
        foreach ($formatted_results as $postId => $results) {
            $post_meta = new PostMeta($postId);
            $setFields = $post_meta->setFields($this->fields);
            if (is_wp_error($setFields)) {
                return $setFields;
            }
            $post_meta->populateFromResults($results);
            
            $this->metaList[] = $post_meta;
        }

        return true;
    }

    public function fetch(array $postIds)
    {
        global $wpdb;
        
        // empty meta list
        $this->metaList = [];

        if (!$this->hasFields()) {
            return true;
        }

        $postList = join(',', $postIds);
        $keys = $this->listKeys();
        $keyList = "'" . join("','", $keys) . "'";

        $query = "SELECT 
            * 
        FROM $wpdb->postmeta
        WHERE meta_key IN ($keyList)
        AND post_id IN ($postList)";

        $results = $wpdb->get_results($query);
        if ($results === false) {
            return new \WP_Error(
                'fetch_post_meta_failed',
                __('Sorry, fetching the post meta failed.', 'peteklein'),
                [
                    'post_ids' => $postIds,
                    'keys' => $keys
                ]
            );
        }

        $populate_meta = $this->populateMetaFromResults($results);
        if (is_wp_error($populate_meta)) {
            return $populate_meta;
        }

        return true;
    }

    private function flattenColumns(array $columns, bool $unique)
    {
        $columnValues = [];
        foreach ($columns as $column) {
            $columnValues = array_merge($columnValues, $column);
        }
        if (!$unique) {
            return $columnValues;
        }

        return array_unique($columnValues);
    }

    public function listColumn(string $key, bool $flatten = false, $unique = false)
    {
        $columns = [];
        foreach ($this->metaList as $meta) {
            $columns[] = $meta->get($key);
        }

        if (!$flatten) {
            return $columns;
        }

        return $this->flattenColumns($columns, $unique);
    }
}
