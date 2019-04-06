<?php

namespace PeteKlein\WP\PostCollection;

use PeteKlein\WP\PostCollection\Taxonomy\WP_Post_Taxonomies;
use PeteKlein\WP\PostCollection\Meta\WP_Post_Meta_Fields;

abstract class WP_Post_Collection
{
    public $posts = [];
    public $items = [];
    public $meta_fields = null;
    public $taxonomies = null;

    public function __construct(array $posts)
    {
        $this->meta_fields = new WP_Post_Meta_Fields();
        $this->taxonomies = new WP_Post_Taxonomies();
        
        foreach ($posts as $post) {
            $this->addPost($post);
        }
    }

    public function addPost(\WP_Post $post)
    {
        $this->posts[] = $post;

        return $this;
    }

    public function add_meta_definition(string $key, $default = null)
    {
        $this->meta_fields->add_definition($key, $default);

        return $this;
    }

    public function add_taxonomy_definition(string $taxonomy, $default)
    {
        $this->taxonomies->add_definition($taxonomy, $default);

        return $this;
    }

    private function list_post_ids()
    {
        return array_column($this->posts, 'ID');
    }

    public function create_items()
    {
        $items = [];
        foreach ($this->posts as $post) {
            $collection_item = new WP_Post_Collection_Item($post);

            $metas = $this->meta_fields->get($post->ID);
            $collection_item->set_metas($metas);
            
            $terms = $this->taxonomies->get($post->ID);
            $collection_item->set_taxonomies($terms);

            $items[] = $collection_item;
        }

        $this->items = $items;

        return $this->items;
    }

    public function fetch_meta(array $post_ids)
    {
        return $this->meta_fields->fetch($post_ids);
    }

    public function fetch_taxonomies(array $post_ids)
    {
        return $this->taxonomies->fetch($post_ids);
    }

    public function fetch()
    {
        $post_ids = $this->list_post_ids();
        $meta = $this->fetch_meta($post_ids);
        $taxonomies = $this->fetch_taxonomies($post_ids);

        return $this->create_items();
    }
}
