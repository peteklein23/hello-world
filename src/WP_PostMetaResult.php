<?php

namespace PeteKlein\WP\PostCollection;

class WP_PostMetaResult
{
    public $key;
    public $value;

    public function __construct(string $key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }
}
