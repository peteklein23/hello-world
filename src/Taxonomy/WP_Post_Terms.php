<?php

namespace PeteKlein\WP\PostCollection\Taxonomy;

class WP_Post_Terms
{
    public $post_id;
    public $terms;

    public function __construct(int $post_id)
    {
        $this->post_id = $post_id;
    }

    public function add_term(object $term_obj)
    {
        $term = new \WP_Term($term_obj);
        $this->terms[] = $term;

        return $this;
    }
}
