<?php

namespace PeteKlein\WP\PostCollection\FeaturedImage;

class FeaturedImage
{
    public $url;
    public $title;
    public $caption;
    public $alt;
    public $description;
    public $height;
    public $width;

    public function __construct(
        string $url = '',
        string $title = '',
        string $caption = '',
        string $alt = '',
        string $description = '',
        $height = 0,
        $width = 0
    ) {
        $this->url = $url;
        $this->title = $title;
        $this->caption = $caption;
        $this->alt = $alt;
        $this->description = $description;
        $this->height = $height;
        $this->width = $width;
    }
}
