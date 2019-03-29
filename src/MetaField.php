<?php

namespace PeteKlein\WPEfficientMeta;

class MetaList
{
    public $key = [];
    public $default = [];
    public $validationRegex = [];

    public function __construct(string $key, $default, string $validationRegex)
    {
        $this->key = $key;
        $this->default = $default;
        $this->validationRegex = $validationRegex;

        return $this;
    }

    public function validate()
    {
    }
}
