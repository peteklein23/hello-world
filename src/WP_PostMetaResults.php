<?php

namespace PeteKlein\WP\PostCollection;

class WP_PostMetaResults
{
    public $postId;
    public $metaResults = [];

    public function __construct($postId)
    {
        $this->postId = $postId;
    }

    public function addResult(string $key, $value)
    {
        $this->metaResults[] = new WP_PostMetaResult($key, $value);
    }
}
