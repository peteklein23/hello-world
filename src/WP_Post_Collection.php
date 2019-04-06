<?php

namespace PeteKlein\WP\PostCollection;

use PeteKlein\WP\PostCollection\Taxonomy\WP_Post_Taxonomies;

abstract class WP_Post_Collection
{
    public $posts = [];
    public $postCollectionItems = [];
    public $metaCollection = null;
    public $post_taxonomies = null;

    public function __construct(array $posts)
    {
        $this->metaCollection = new WP_MetaCollection();
        $this->post_taxonomies = new WP_Post_Taxonomies();
        
        foreach ($posts as $post) {
            $this->addPost($post);
        }
    }

    public function addPost(\WP_Post $post)
    {
        $this->posts[] = $post;

        return $this;
    }

    public function addMetaDefinition(string $key, $default = null)
    {
        $this->metaCollection->addMetaDefinition(new WP_MetaDefinition($key, $default));

        return $this;
    }

    public function addTaxonomyDefinition(string $taxonomy, $default)
    {
        $this->post_taxonomies->add_definition($taxonomy, $default);

        return $this;
    }

    private function listPostIds()
    {
        return array_column($this->posts, 'ID');
    }

    // TODO: separate these out into getMeta(), getTaxonomies() and getFeatureImages()
    // TODO: fire all three in fetch()
    public function list()
    {
        $postCollectionItems = [];
        $postIds = $this->listPostIds();
        // $metaList = $this->metaCollection->getByPostIds($postIds);
        return $taxonomyList = $this->post_taxonomies->fetch($postIds);
        foreach ($this->posts as $post) {
            $metaForPost = $this->metaCollection->getMetaByPostId($post->ID);
            $postCollectionItems[] = new WP_PostCollectionItem($post, $metaForPost);
        }

        $this->postCollectionItems = $postCollectionItems;

        return $this->postCollectionItems;
    }
}
