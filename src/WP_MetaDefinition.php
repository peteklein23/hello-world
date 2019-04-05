<?php

namespace PeteKlein\WP\PostCollection;

class WP_MetaDefinition
{
    public $key;
    public $default;
    public $alias;

    public function __construct(string $key, $default, string $alias = null)
    {
        $this->key = $key;
        $this->default = $default;
        $this->alias = $alias;
    }

    public function valueOrDefault($value)
    {
        if (empty($value)) {
            return $this->default;
        }

        return $value;
    }
}
