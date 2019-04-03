<?php

namespace PeteKlein\WP\PostCollection;

abstract class PostCollection
{
    public $postType;
    public $posts = [];

    public function __construct(array $posts = [])
    {
        foreach ($posts as $post) {
            $this->addPost($post);
        }
    }

    public function addPost(\WP_Post $post)
    {
        $this->posts[] = $post;
    }
}
