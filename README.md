# WordPress Post Collection

Post Collections greatly reduces the number of queries made when
grabbing featured images, meta data and taxonomies across multiple posts.

## Installation

Add the dependency to `composer.json`. (This is still in alpha so expect changes.)

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/peteklein23/wp-post-collection"
    }
  ],
  "require": {
    "peteklein23/wp-post-collection": "dev-master"
  }
}
```

Run `composer install`. Then, include the autoloader in Your Theme's `functions.php`

```php
require_once ABSPATH . '/vendor/autoload.php';

// add thumbnail support for desired post types
add_theme_support('post-thumbnails', ['post', 'page']);
```

## Adding and Using a Collection

### Extend the Abstract Class and Specify Your Data

```php
<?php

namespace YourNamespace\PostTypes\Posts;

use PeteKlein\WP\PostCollection\PostCollection as Collection;

class PostCollection extends Collection
{
    public function __construct(array $posts)
    {
        parent::__construct($posts);

        // add image sizes
        $this->addImageSize('thumbnail')
            ->addImageSize('medium');

        // add meta fields
        $this->addMeta('location', '')
            ->addMeta('otherthing', null, true)
            ->addMeta('pages', []);

        // add taxonomy definitions
        $this->addTaxonomy('category', null)
            ->addTaxonomy('post_tag', []);
    }
}
```

### Pass WP_Post Objects to Your Post Collection

```php
<?php
// archive-post.php

use YourNamespace\PostTypes\Posts\PostCollection;

$postCollection = new PostCollection($posts);
$postCollection->fetch();
?>
<table>
    <tr>
        <th>Image</th>
        <th>Post Title</th>
        <th>Categories</th>
        <th>Tags</th>
        <th>Location</th>
        <th>Other thing</th>
    </tr>
    <?php foreach ($posts as $post): ?>
        <tr>
            <td>
                <img src="<?php echo $post->featuredImages->get('thumbnail')->url; ?>" />
            </td>
            <td><?php echo $post->post_title; ?></td>
            <td>
                <ul>
                    <?php foreach ($post->taxonomies->get('category') as $term): ?>
                        <li><?php echo $term->name ?></li>
                    <?php endforeach; ?>
                </ul>
            </td>
            <td>
                <ul>
                    <?php foreach ($post->taxonomies->get('post_tag') as $term): ?>
                        <li><?php echo $term->name ?></li>
                    <?php endforeach; ?>
                </ul>
            </td>
            <td>
                <?php echo $post->meta->get('location'); ?>
            </td>
            <td><?php echo $post->meta->get('otherthing') ?></td>
        </tr>

    <?php endforeach; ?>
</table>

<?php
get_footer();
```

## Adding and Using Data for a Single Post

### Extend the Abstract Class and Specify Your Data

```php
<?php

namespace YourNamespace\PostTypes\Posts;

use PeteKlein\WP\PostCollection\PostCollection as Collection;

class PostDetail extends Detail
{
    public function __construct($post)
    {
        parent::__construct($post);

        /** add meta definitions */
        $this->meta->addField('location', [], false)
            ->addField('otherthing', null, true);

        /** add texonomies definitions */
        $this->taxonomies->addField('category', null)
            ->addField('post_tag', []);

        /** add featured image sizes */
        $this->featuredImages->addSize('thumbnail')
            ->addSize('medium');
    }
}
```

### Pass WP_Post Object to Your Post Detail

```php
<?php
// single-post.php

use YourNamespace\PostTypes\Post\PostDetail;

$postDetail = new PostDetail($post);
$postDetail->fetch();

get_header();
?>
// TODO
<?php
get_footer();

```
