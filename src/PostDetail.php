<?php

namespace PeteKlein\WP\PostCollection;

use PeteKlein\WP\PostCollection\FeaturedImage\FeaturedImages;
use PeteKlein\WP\PostCollection\Meta\PostMeta;
use PeteKlein\WP\PostCollection\Taxonomy\PostTaxonomies;

abstract class PostDetail
{
    public $post;
    public $meta;
    public $taxonomies;
    public $featuredImages;

    public function __construct(\WP_Post $post)
    {
        $this->featuredImages = new FeaturedImages($post->ID);
        $this->meta = new PostMeta($post->ID);
        $this->taxonomies = new PostTaxonomies($post->ID);
        
        $this->post = $post;
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

    private function addDataToPost()
    {
        $this->post->meta = $this->meta->get($this->post->ID);
        $this->post->taxonomies = $this->taxonomies->get($this->post->ID);
        $this->post->featuredImages = $this->featuredImages->get($this->post->ID);
    }

    public function getPost()
    {
        return $this->post;
    }

    public function fetch()
    {
        $featuredImages = $this->featuredImages->fetch();
        $meta = $this->meta->fetch();
        $taxonomies = $this->taxonomies->fetch();
        
        $this->addDataToPost();

        return $this->getPost();
    }
}
