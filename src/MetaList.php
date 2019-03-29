<?php

namespace PeteKlein\WPEfficientMeta;

class MetaList
{
    public $metaFields = [];

    public function __construct(array $metaFields = [])
    {
        foreach ($metaFields as $metaField) {
            $this->addMetaField($metaField);
        }
    }

    public function addMetaField(MetaField $metaField)
    {
        $metaList[] = $metaField;
    }

    public function listValues(array $fieldKeys, array $postIds, bool $indexById = false)
    {
        global $wpdb;

        if ($fieldKeys) {
        
            // run query and get results
            $query = "";
        }
    }

    public function getFieldKeys()
    {
    }

    public function setValues(array $fieldValues)
    {
    }
}
