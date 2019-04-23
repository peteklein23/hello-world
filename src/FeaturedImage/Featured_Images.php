<?php

namespace PeteKlein\WP\PostCollection\FeaturedImage;

class Featured_Images
{
    public $post_id;
    private $sizes = [];
    private $images = [];

    public function __construct(int $post_id)
    {
        $this->post_id = $post_id;
    }

    public function add_size(string $size)
    {
        $this->sizes[] = $size;

        return $this;
    }

    public function set_sizes(array $sizes)
    {
        foreach ($sizes as $size) {
            $this->add_size($size);
        }
    }

    public function get(string $size)
    {
        if (!empty($this->images[$size])) {
            return $this->images[$size];
        }

        return null;
    }

    public function list()
    {
        return $this->images;
    }

    public function populate_result(object $result = null)
    {
        if (empty($result)) {
            return;
        }
        $formatted_result = [];
        
        $attachment_id = $result->attachment_id;
        $meta = maybe_unserialize($result->attachment_metadata);
        $sizes = $meta['sizes'];
        $file = $meta['file'];
        $base_url = wp_upload_dir()['baseurl'];

        foreach ($this->sizes as $size) {
            $height = 0;
            $width = 0;

            if (empty($sizes[$size])) {
                $image_url = trailingslashit($base_url) . $file;
            } else {
                $height = $sizes[$size]['height'];
                $width = $sizes[$size]['width'];
                $relative_path = dirname($file);
                $image_url = trailingslashit($base_url) . trailingslashit($relative_path) . $sizes[$size]['file'];
            }

            $url = apply_filters('wp_get_attachment_image_src', $image_url, $attachment_id, $size, false);
            ;
            
            $this->images[$size] = new Featured_Image(
                $url,
                $result->title,
                $result->caption,
                $result->alt,
                $result->description,
                $height,
                $width
            );
        }
    }

    public function fetch()
    {
        global $wpdb;

        $query = "SELECT
            pm1.meta_value AS attachment_id,
            pm2.meta_value AS attachment_metadata,
            pm3.meta_value AS alt,
            p.post_title AS title,
            p.post_content AS description,
            p.post_excerpt AS caption
        FROM $wpdb->postmeta pm1
        INNER JOIN $wpdb->postmeta pm2 ON pm1.meta_value = pm2.post_id AND pm2.meta_key = '_wp_attachment_metadata'
        INNER JOIN $wpdb->postmeta pm3 ON pm1.meta_value = pm3.post_id AND pm3.meta_key = '_wp_attachment_image_alt'
        INNER JOIN $wpdb->posts p ON pm1.meta_value = p.ID
        WHERE pm1.post_id IN ($this->post_id)
        AND pm1.meta_key = '_thumbnail_id'";

        $result = $wpdb->get_row($query);
        if ($result === false) {
            return new \WP_Error(
                'fetch_featured_images_failed',
                __('Sorry, fetching featured images failed.', 'peteklein'),
                [
                    'post_id' => $this->post_id,
                    'sizes' => $this->sizes
                ]
            );
        }

        $this->populate_result($result);
        
        return $this->list();
    }
}
