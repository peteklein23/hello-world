<?php

namespace PeteKlein\WP\PostCollection;

abstract class WP_PostCollection
{
    public $posts = [];
    public $metaCollection;
    public $metaFields = [];
    public $taxonomyList = null;

    public function __construct(array $posts, array $metaDefinitions)
    {
        foreach ($posts as $post) {
            $this->addPost($post);
        }

        $this->metaCollection = new WP_MetaCollection($metaDefinitions);
    }

    public function addPost(\WP_Post $post)
    {
        $this->posts[] = $post;
    }

    public function listPostIds()
    {
        return array_column($this->posts, 'ID');
    }

    public function list()
    {
        $postCollectionItems = [];
        $postIds = $this->listPostIds();
        $metaList = $this->metaCollection->fetchByPostIds($postIds);
        foreach ($this->posts as $post) {
            $metaForPost = $this->metaCollection->getMetaByPostId($post->ID);
            $postCollectionItems[] = new WP_PostCollectionItem($post, $metaForPost);
        }

        return $postCollectionItems;
    }
}
