<?php

namespace PeteKlein\WP\PostCollection;

class WP_PostMeta
{
    public $post_id;
    public $meta_key;
    public $meta_value;

    public function __construct(int $post_id, string $meta_key, $meta_value)
    {
        $this->post_id = $post_id;
        $this->meta_key = $meta_key;
        $this->meta_value = $meta_value;
    }
}
