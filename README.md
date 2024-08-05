Sure, here is the updated README file with the new sections added in proper Markdown format:

```markdown
# WPMUDEV Test Plugin #

This is a plugin that can be used for testing coding skills for WordPress and PHP.

# Development

## Composer
Install composer packages
```bash
composer install
```

## Build Tasks (npm)
Everything should be handled by npm.

Install npm packages
```bash
npm install
```

| Command              | Action                                                |
|----------------------|-------------------------------------------------------|
| `npm run watch`      | Compiles and watch for changes.                       |
| `npm run compile`    | Compile production ready assets.                      |
| `npm run build`      | Build production ready bundle inside `/build/` folder |

## Unit Testing

To run the unit tests for the plugin, including the `Scan Posts` functionality, use the following command:

```bash
phpunit --filter Scan_Posts_Test
```

## Customizing Post Types for Scan

You can change the post types that are scanned by the plugin by using the `wpmudev_scan_post_types` filter. By default, the plugin scans `post` and `page` post types. Here is an example of how to add a custom post type to the filter:

```php
add_filter( 'wpmudev_scan_post_types', function( $post_types ) {
    $post_types[] = 'custom_post_type';
    return $post_types;
} );
```

## Shortcode for Google Login

The plugin provides a shortcode `[wpmudev_google_login]` that can be used to display a Google login link. Use this shortcode in any post or page where you want to display the login link.

```bash
[wpmudev_google_login]
```

This shortcode will generate a link that users can click to log in using their Google account.
```

### Explanation of the Additions

1. **Unit Testing Command for Scan Posts**:
   - Added a section explaining how to run the unit tests specifically for the `Scan Posts` functionality using PHPUnit.

2. **Changing Post Types in the Filter**:
   - Added a section explaining how to use the `wpmudev_scan_post_types` filter to customize the post types that are scanned by the plugin. Provided an example code snippet.

3. **Using the Shortcode for Google Login**:
   - Added a section explaining how to use the `[wpmudev_google_login]` shortcode to display a Google login link on any post or page.