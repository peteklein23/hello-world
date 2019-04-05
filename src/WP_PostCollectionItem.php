<?php

namespace PeteKlein\WP\PostCollection;

class WP_PostCollectionItem
{
    public $post;
    public $meta = [];

    public function __construct(\WP_Post $post, array $meta = [])
    {
        $this->post = $post;
        $this->meta = $meta;
    }
}
