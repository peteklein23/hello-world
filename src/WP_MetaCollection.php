<?php

namespace PeteKlein\WP\PostCollection;

class WP_MetaCollection
{
    public $metaDefinitions = [];
    public $metaResults = [];

    public function addMetaDefinition(WP_MetaDefinition $metaDefinition)
    {
        $this->metaDefinitions[] = $metaDefinition;
    }

    private function listMetaKeys()
    {
        return array_column($this->metaDefinitions, 'key');
    }

    public function getByPostIds(array $postIds)
    {
        global $wpdb;

        $postIdInClauseValues = join(',', $postIds);
        $metaKeys = $this->listMetaKeys();
        $metaKeyInClauseValues = "'" . join("','", $metaKeys) . "'";

        $query = "SELECT 
            * 
        FROM $wpdb->postmeta
        WHERE meta_key IN ($metaKeyInClauseValues)
        AND post_id IN ($postIdInClauseValues)";

        // TODO: error handling
        $results = $wpdb->get_results($query);

        return $formattedResults = $this->formatResults($results);
    }

    private function formatResults(array $results)
    {
        /** organize by post Id */
        $formattedResults = [];
        foreach ($results as $result) {
            $postId = $result->post_id;
            if (empty($formattedResults[$postId])) {
                $formattedResults[$postId] = [];
            }

            $formattedResults[$postId][] = $result;
        }

        /** populate defaults and put into result */
        // TODO: account for multiple entries
        foreach ($formattedResults as $postId => $postMeta) {
            $postMetaResults = new WP_PostMetaResults($postId);
            foreach ($postMeta as $m) {
                $metaDefinition = $this->getMetaDefinitionByKey($m->meta_key);
                $value = $metaDefinition->valueOrDefault($m->meta_value);
                $postMetaResults->addResult($m->meta_key, $value);
            }
            $this->metaResults[] = $postMetaResults;
        }

        return $this->metaResults;
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
        foreach ($this->metaResults as $mr) {
            if ($mr->postId === $postId) {
                return $mr;
            }
        }
    }

    public function set(int $postId, array $valuesMap)
    {
    }
}
