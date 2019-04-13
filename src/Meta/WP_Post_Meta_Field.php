<?php

namespace PeteKlein\WP\PostCollection\Meta;

class WP_Post_Meta_Field
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
