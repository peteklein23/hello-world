<?php

namespace PeteKlein\WP\PostCollection;

use PeteKlein\WP\PostCollection\Meta\WP_Post_Metas;
use PeteKlein\WP\PostCollection\Taxonomy\WP_Post_Terms;

class WP_Post_Collection_Item
{
    public $post;
    public $metas = null;
    public $taxonomies = null;

    public function __construct(\WP_Post $post)
    {
        $this->post = $post;
    }

    public function set_metas(WP_Post_Metas $metas)
    {
        return $this->metas = $metas;
    }

    public function set_taxonomies(WP_Post_Terms $taxonomies)
    {
        $this->taxonomies = $taxonomies;
    }
}
