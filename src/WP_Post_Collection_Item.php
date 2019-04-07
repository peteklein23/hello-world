<?php

namespace PeteKlein\WP\PostCollection;

use PeteKlein\WP\PostCollection\Meta\WP_Post_Meta_List;
use PeteKlein\WP\PostCollection\Taxonomy\WP_Post_Terms;

/** container for  */
class WP_Post_Collection_Item
{
    public $post;
    public $meta_list = null;
    public $taxonomies = null;

    public function __construct(\WP_Post $post)
    {
        $this->post = $post;
    }

    public function set_meta_list(WP_Post_Meta_List $meta_list)
    {
        return $this->meta_list = $meta_list;
    }

    public function set_taxonomies(WP_Post_Terms $taxonomies)
    {
        $this->taxonomies = $taxonomies;
    }

    public function get_meta(string $key)
    {
        return $this->meta_list->get($key)->value;
    }
}
