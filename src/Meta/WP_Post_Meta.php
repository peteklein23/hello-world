<?php

namespace PeteKlein\WP\PostCollection\Meta;

/**
 * Simple container for metadata
 */
class WP_Post_Meta
{
    public $key;
    public $value;

    public function __construct(string $key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }
}
