<?php

namespace PeteKlein\WP\PostCollection\FeaturedImage;

class Featured_Images
{
    private $images = [];

    private function format_results(array $results, string $size)
    {
        $formatted_results = [];
        foreach ($results as $result) {
            $post_id = $result->post_id;
            $attachment_id = $result->attachment_id;
            $meta = maybe_unserialize($result->attachment_metadata);
            $sizes = $meta['sizes'];
            $file = $meta['file'];
            $base_url = wp_upload_dir()['baseurl'];
            if (empty($sizes[$size])) {
                $image_url = trailingslashit($base_url) . $file;
            } else {
                $relative_path = dirname($file);
                $image_url = trailingslashit($base_url) . trailingslashit($relative_path) . $sizes[$size]['file'];
            }

            $formatted_results[$post_id] = apply_filters('wp_get_attachment_image_src', $image_url, $attachment_id, $size, false);
        }

        return $formatted_results;
    }

    public function fetch(array $post_ids, string $size = 'thumbnail')
    {
        global $wpdb;

        $post_list = join(',', $post_ids);

        $query = "SELECT
            pm1.post_id,
            pm1.meta_value AS attachment_id,
            pm2.meta_value AS attachment_metadata
        FROM wp_postmeta pm1
        LEFT JOIN wp_postmeta pm2 ON pm1.meta_value = pm2.post_id AND pm2.meta_key = '_wp_attachment_metadata'
        WHERE pm1.post_id IN ($post_list)
        AND pm1.meta_key = '_thumbnail_id'";

        $results = $wpdb->get_results($query);
        if ($results === false) {
            return new \WP_Error(
                'fetch_featured_images_failed',
                __('Sorry, fetching featured images failed.', 'peteklein'),
                [
                    'post_ids' => $post_ids
                ]
            );
        }

        $this->images = $this->format_results($results, $size);
        
        return $this->images;
    }

    public function get(int $post_id)
    {
        if (!empty($this->images[$post_id])) {
            return $this->images[$post_id];
        }

        return null;
    }
}
