<?php

namespace PeteKlein\WP\PostCollection;

use PeteKlein\WP\PostCollection\Taxonomy\WP_Post_Taxonomy_Fields;
use PeteKlein\WP\PostCollection\Meta\WP_Post_Meta_Fields;
use PeteKlein\WP\PostCollection\FeaturedImage\WP_Featured_Images;

/**
 * Makes it easier and more efficient to query meta, taxonomy_fields and featured images at scale
 */
abstract class WP_Post_Collection
{
    public $posts = [];
    public $meta_fields = null;
    public $taxonomy_fields = null;
    public $featured_images = null;
    public $featured_image_size = null;

    public function __construct(array $posts)
    {
        $this->meta_fields = new WP_Post_Meta_Fields();
        $this->taxonomy_fields = new WP_Post_Taxonomy_Fields();
        $this->featured_images = new WP_Featured_Images();
        
        foreach ($posts as $post) {
            $this->add_post($post);
        }
    }

    public function add_post(\WP_Post $post)
    {
        $this->posts[] = $post;

        return $this;
    }

    public function add_meta_field(string $key, $default = null, bool $single = true)
    {
        $this->meta_fields->add_field($key, $default, $single);

        return $this;
    }

    public function add_taxonomy_field(string $taxonomy, $default)
    {
        $this->taxonomy_fields->add_field($taxonomy, $default);

        return $this;
    }

    private function list_post_ids()
    {
        return array_column($this->posts, 'ID');
    }

    private function augment_posts()
    {
        foreach ($this->posts as &$post) {
            $post->meta = $this->meta_fields->list($post->ID);
            $post->taxonomies = $this->taxonomy_fields->list($post->ID);
            $post->featured_image = $this->featured_images->get($post->ID);
        }
    }

    public function fetch_meta()
    {
        return $this->meta_fields->fetch($this->list_post_ids());
    }

    public function fetch_taxonomies()
    {
        return $this->taxonomy_fields->fetch($this->list_post_ids());
    }

    public function fetch_featured_images()
    {
        if (empty($this->featured_image_size)) {
            return [];
        }
        
        return $this->featured_images->fetch($this->list_post_ids(), $this->featured_image_size);
    }

    public function fetch()
    {
        $meta = $this->fetch_meta();
        $taxonomy_fields = $this->fetch_taxonomies();
        $featured_images = $this->fetch_featured_images();
        $this->augment_posts();

        return $this->get_posts();
    }

    public function get_posts()
    {
        return $this->posts;
    }
}
