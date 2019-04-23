<?php

namespace PeteKlein\WP\PostCollection\FeaturedImage;

class Featured_Image_Collector
{
    private $images = [];
    private $sizes = [];

    public function add_size(string $size)
    {
        $this->sizes[] = $size;
    }

    public function get(int $post_id)
    {
        foreach ($this->images as $images) {
            if ($images->post_id = $post_id) {
                return $images;
            }
        }

        return null;
    }

    public function list()
    {
        $list = [];
        foreach ($this->images as $images) {
            $list[$images->post_id] = $images->list();
        }

        return $list;
    }

    private function populate_images(array $results)
    {
        $formatted_results = [];
        foreach ($results as $result) {
            $post_id = $result->post_id;
            
            $featured_images = new Featured_Images($post_id);
            $featured_images->set_sizes($this->sizes);
            $featured_images->populate_result($result);
            $this->images[] = $featured_images;
        }
    }

    public function fetch(array $post_ids, string $size = 'thumbnail')
    {
        global $wpdb;

        $this->images = [];

        $post_list = join(',', $post_ids);

        $query = "SELECT
            pm1.post_id,
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

        $this->populate_images($results);
        
        return $this->list();
    }
}
