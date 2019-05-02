<?php

namespace PeteKlein\WP\PostCollection\Meta;

class PostMetaField
{
    public $key;
    public $default;
    public $single;

    public function __construct(string $key, $default, bool $single = true)
    {
        $this->key = $key;
        $this->default = $default;
        $this->single = $single;
    }
}
