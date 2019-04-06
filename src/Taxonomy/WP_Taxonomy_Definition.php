<?php

namespace PeteKlein\WP\PostCollection\Taxonomy;

class WP_Taxonomy_Definition
{
    public $slug;
    public $default;

    public function __construct(string $slug, $default)
    {
        $this->slug = $slug;
        $this->default = $default;
    }

    public function valueOrDefault($value)
    {
        if (empty($value)) {
            return $this->default;
        }

        return $value;
    }
}
