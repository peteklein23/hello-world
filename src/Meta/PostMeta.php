<?php

namespace PeteKlein\WP\PostCollection\Meta;

class PostMeta
{
    public $postId;
    private $fields = [];
    private $values = [];

    public function __construct(int $postId)
    {
        $this->postId = $postId;
    }

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

    private function hasFields()
    {
        return !empty($this->fields);
    }

    private function listKeys()
    {
        return array_column($this->fields, 'key');
    }

    private function populateMissingValues()
    {
        foreach ($this->fields as $field) {
            if (empty($this->values[$field->key])) {
                $this->values[$field->key] = $field->default;
            }
        }
    }

    public function setValue(string $key, string $value)
    {
        $field = $this->getField($key);
        $unserializedValue = maybe_unserialize($value);

        if (empty($field)) {
            return $this;
        }
        
        if (empty($unserializedValue)) {
            $unserializedValue = $field->default;
        }

        if ($field->single) {
            $this->values[$key] = $unserializedValue;

            return $this;
        }

        if (empty($this->values[$key])) {
            $this->values[$key] = [];
        }

        $this->values[$key][] = $unserializedValue;

        return $this;
    }

    public function setFields(array $fields)
    {
        foreach ($fields as $field) {
            if (!($field instanceof PostMetaField)) {
                return new \WP_Error(
                    'post_meta_field_needed',
                    __('Sorry, all values passed must be an instance of PostMetaField', 'peteklein'),
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
            $key = $result->meta_key;
            $value = $result->meta_value;
            
            $this->setValue($key, $value);
        }

        $this->populateMissingValues();
    }

    public function get(string $key)
    {
        if (!empty($this->values[$key])) {
            return $this->values[$key];
        }

        return null;
    }

    public function list()
    {
        return $this->values;
    }

    public function fetch()
    {
        global $wpdb;

        // empty values
        $this->values = [];

        if (!$this->hasFields()) {
            return true;
        }

        $keys = $this->listKeys();
        $keyList = "'" . join("','", $keys) . "'";

        $query = "SELECT 
            * 
        FROM $wpdb->postmeta
        WHERE meta_key IN ($keyList)
        AND post_id = $this->postId";

        $results = $wpdb->get_results($query);
        if ($results === false) {
            return new \WP_Error(
                'fetch_post_meta_failed',
                __('Sorry, fetching the post meta failed.', 'peteklein'),
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
