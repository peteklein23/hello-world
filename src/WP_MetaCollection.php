<?php

namespace PeteKlein\WP\PostCollection;

class WP_MetaCollection
{
    public $metaDefinitions = [];
    public $meta = [];

    public function __construct(array $metaDefinitions)
    {
        foreach ($metaDefinitions as $metaDefinition) {
            $this->addMetaDefinition($metaDefinition);
        }
    }

    public function addMetaDefinition(WP_MetaDefinition $metaDefinition)
    {
        $this->metaDefinitions[] = $metaDefinition;
    }

    public function listMetaKeys()
    {
        return array_column($this->metaDefinitions, 'key');
    }

    public function fetchByPostIds(array $postIds)
    {
        global $wpdb;

        $postIdInClauseValues = join(',', $postIds);
        $metaKeys = $this->listMetaKeys();
        $metaKeyInClauseValues = "'" . join(',', $metaKeys) . "'";

        $query = "SELECT 
            * 
        FROM $wpdb->postmeta
        WHERE meta_key IN ($metaKeyInClauseValues)
        AND post_id IN ($postIdInClauseValues)";

        // TODO: error handling
        $metaResults = $wpdb->get_results($query);

        return $this->processMetaResults($metaResults);
    }

    private function processMetaResults(array $metaResults)
    {
        // populate defaults and put into WP_PostMeta Objects
        $meta = [];
        foreach ($metaResults as $m) {
            $key = $m->meta_key;

            $metaDefinition = $this->getMetaDefinitionByKey($key);
            $value = $metaDefinition->valueOrDefault($m->meta_value);
            $meta[] = new WP_PostMeta($m->post_id, $key, $value);
        }

        $this->meta = $meta;

        return $this->meta;
    }

    private function getMetaDefinitionByKey(string $key)
    {
        foreach ($this->metaDefinitions as $md) {
            if ($md->key === $key) {
                return $md;
            }
        }

        return null;
    }

    public function getMetaByPostId(int $postId)
    {
        $metaForPost = [];
        foreach ($this->meta as $m) {
            if ($m->post_id === $postId) {
                $metaForPost[] = $m;
            }
        }

        // see if all meta definitions are satisfed
        $metaKeysForPost = array_column($metaForPost, 'meta_key');
        foreach ($this->metaDefinitions as $md) {
            if (!in_array($md->key, $metaKeysForPost)) {
                $metaForPost[] = new WP_PostMeta($postId, $md->key, $md->default);
            }
        }

        return $metaForPost;
    }

    public function set(int $postId, array $valuesMap)
    {
    }
}
