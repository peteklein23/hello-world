<?php

namespace PeteKlein\WP\PostCollection\Meta;

class WP_Meta_Definition
{
    public $key;
    public $default;

    public function __construct(string $key, $default)
    {
        $this->key = $key;
        $this->default = $default;
    }

    public function value_or_default($value)
    {
        if (empty($value)) {
            return $this->default;
        }

        return $value;
    }
}
