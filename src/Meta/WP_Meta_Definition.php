<?php

namespace PeteKlein\WP\PostCollection\Meta;

class WP_Meta_Definition
{
    public $key;
    public $default;
    public $single;

    public function __construct(string $key, $default, bool $single = false)
    {
        $this->key = $key;
        $this->default = $default;
        $this->single = $single;
    }
}
