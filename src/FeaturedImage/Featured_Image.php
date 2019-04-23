<?php

namespace PeteKlein\WP\PostCollection\FeaturedImage;

class Featured_Image
{
    public $url;
    public $title;
    public $caption;
    public $alt;
    public $description;
    public $height = 0;
    public $width = 0;

    public function __construct(
        $url,
        $title,
        $caption,
        $alt,
        $description,
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
