<?php

namespace PeteKlein\WP\PostCollection;

use PeteKlein\WP\PostCollection\FeaturedImage\FeaturedImageCollection;
use PeteKlein\WP\PostCollection\Meta\PostMetaCollection;
use PeteKlein\WP\PostCollection\Taxonomy\PostTaxonomyCollection;

abstract class PostCollection
{
    public $posts = [];
    public $meta;
    public $taxonomies;
    public $featuredImages;

    public function __construct(array $posts)
    {
        $this->featuredImages = new FeaturedImageCollection();
        $this->meta = new PostMetaCollection();
        $this->taxonomies = new PostTaxonomyCollection();
        
        foreach ($posts as $post) {
            $this->addPost($post);
        }
    }

    public function addPost(\WP_Post $post)
    {
        $this->posts[] = $post;

        return $this;
    }

    public function addImageSize(string $size)
    {
        $this->featuredImages->addSize($size);

        return $this;
    }

    public function addMeta(string $key, $default = null, bool $single = true)
    {
        $this->meta->addField($key, $default, $single);

        return $this;
    }

    public function addTaxonomy(string $taxonomy, $default)
    {
        $this->taxonomies->addField($taxonomy, $default);

        return $this;
    }

    private function listPostIds()
    {
        return array_column($this->posts, 'ID');
    }

    private function addDataToPosts()
    {
        foreach ($this->posts as &$post) {
            $post->meta = $this->meta->get($post->ID);
            $post->taxonomies = $this->taxonomies->get($post->ID);
            $post->featuredImages = $this->featuredImages->get($post->ID);
        }
    }

    public function fetchMeta(array $postIds)
    {
        return $this->meta->fetch($postIds);
    }

    public function fetchTaxonomies(array $postIds)
    {
        return $this->taxonomies->fetch($postIds);
    }

    public function fetchFeaturedImages(array $postIds)
    {
        return $this->featuredImages->fetch($postIds);
    }

    public function fetch()
    {
        $postIds = $this->listPostIds();
        $meta = $this->fetchMeta($postIds);
        $taxonomies = $this->fetchTaxonomies($postIds);
        $featuredImages = $this->fetchFeaturedImages($postIds);
        $this->addDataToPosts();

        return $this->getPosts();
    }

    public function getPosts()
    {
        return $this->posts;
    }
}
