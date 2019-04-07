<?php

namespace PeteKlein\WP\PostCollection\Meta;

/**
 * Holds and manages meta from a single post
 */
class WP_Post_Meta_List
{
    public $post_id;
    public $definitions = null;
    public $meta = [];

    public function __construct(int $post_id, WP_Meta_Definition_List $definitions)
    {
        $this->post_id = $post_id;
        $this->definitions = $definitions;
    }

    public function add_meta(string $key, $value)
    {
        $this->meta[] = new WP_Post_Meta($key, $value);
    }

    public function get(string $key)
    {
        // account for multiple entries
        foreach ($this->meta as $meta) {
            if ($meta->key === $key) {
                return $meta;
            }
        }

        return null;
    }

    public function value_or_default($value)
    {
        if (empty($value)) {
            return $this->default;
        }

        return $value;
    }
}
