<?php

namespace PeteKlein\WP\PostCollection;

use PeteKlein\WP\PostCollection\FeaturedImage\Featured_Images;
use PeteKlein\WP\PostCollection\Meta\Post_Meta_Collector;
use PeteKlein\WP\PostCollection\Taxonomy\Post_Taxonomy_Collector;

/**
 * Makes it easier and more efficient to query meta, taxonomies and featured images at scale
 */
abstract class Post_Data_Collector
{
    public $posts = [];
    public $meta = null;
    public $taxonomies = null;
    public $featured_images = null;
    public $featured_image_size = null;

    public function __construct(array $posts)
    {
        $this->meta = new Post_Meta_Collector();
        $this->taxonomies = new Post_Taxonomy_Collector();
        $this->featured_images = new Featured_Images();
        
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
        $this->meta->add_field($key, $default, $single);

        return $this;
    }

    public function add_taxonomy_field(string $taxonomy, $default)
    {
        $this->taxonomies->add_field($taxonomy, $default);

        return $this;
    }

    private function list_post_ids()
    {
        return array_column($this->posts, 'ID');
    }

    private function augment_posts()
    {
        foreach ($this->posts as &$post) {
            $post->meta = $this->meta->list($post->ID);
            $post->taxonomies = $this->taxonomies->list($post->ID);
            $post->featured_image = $this->featured_images->get($post->ID);
        }
    }

    public function fetch_meta()
    {
        $post_ids = $this->list_post_ids();

        return $this->meta->fetch($post_ids);
    }

    public function fetch_taxonomies()
    {
        $post_ids = $this->list_post_ids();

        return $this->taxonomies->fetch($post_ids);
    }

    public function fetch_featured_images()
    {
        if (empty($this->featured_image_size)) {
            return [];
        }
        $post_ids = $this->list_post_ids();
        
        return $this->featured_images->fetch($post_ids, $this->featured_image_size);
    }

    public function fetch()
    {
        $meta = $this->fetch_meta();
        $taxonomies = $this->fetch_taxonomies();
        $featured_images = $this->fetch_featured_images();
        $this->augment_posts();

        return $this->get_posts();
    }

    public function get_posts()
    {
        return $this->posts;
    }
}
