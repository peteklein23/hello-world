<?php

namespace PeteKlein\WP\PostCollection\Meta;

class WP_Meta_Definition_List
{
    public $definitions = [];

    public function add(string $key, $default, bool $single = false)
    {
        $this->definitions[] = new WP_Meta_Definition($key, $default);
    }

    public function get(string $key)
    {
        foreach ($this->definitions as $definition) {
            if ($definition->key === $key) {
                return $definition;
            }
        }

        return null;
    }

    /**
     * list keys of the array
     *
     * @return string[] defined meta keys
     */
    public function list_keys()
    {
        return array_column($this->definitions, 'key');
    }

    /**
     * returns all existing data, with keys populated if not present
     *
     * @param array $keys The array keys to return. Leaving emtpy returns all
     * @return
     */
    public function list(array $keys = null)
    {
        if (empty($keys)) {
            $keys = $this->list_keys();
        }
    }
}
