<?php

namespace PeteKlein\WP\PostCollection\Meta;

class WP_Post_Metas
{
    public $post_id;
    public $results = [];

    public function __construct($post_id)
    {
        $this->post_id = $post_id;
    }

    public function add_result(string $key, $value)
    {
        $this->results[] = new WP_Post_Meta($key, $value);
    }
}
