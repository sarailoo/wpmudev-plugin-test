# WPMUDEV Test Plugin #

This is a plugin that can be used for testing coding skills for WordPress and PHP.

# Development

## Composer
Install composer packages
`composer install`

## Build Tasks (npm)
Everything should be handled by npm.

Install npm packages
`npm install`

| Command              | Action                                                |
|----------------------|-------------------------------------------------------|
| `npm run watch`      | Compiles and watch for changes.                       |
| `npm run compile`    | Compile production ready assets.                      |
| `npm run build`  | Build production ready bundle inside `/build/` folder |

## Unit Testing
To run the unit tests for the plugin, including the Scan Posts functionality, use the following command:
`phpunit --filter Scan_Posts_Test`

## Customizing Post Types for Scan
You can change the post types that are scanned by the plugin by using the `wpmudev_scan_post_types filter`. By default, the plugin scans post and page post types. Here is an example of how to add a custom post type to the filter:
```
add_filter( 'wpmudev_scan_post_types', function( $post_types ) {
    $post_types[] = 'custom_post_type';
    return $post_types;
} );
```

## Shortcode for Google Login
The plugin provides a shortcode `[wpmudev_google_auth]` that can be used to display a Google login link. Use this shortcode in any post or page where you want to display the login link:
`[wpmudev_google_auth]`
This shortcode will generate a link that users can click to log in using their Google account.

## WP-CLI Command for Scan Posts
The plugin includes a WP-CLI command to execute the Scan Posts action from the terminal. This can be useful for system administrators who want to automate the scan process or run it manually.

To use the WP-CLI command, run:

`wp wpmudev scan-posts`

### Usage Instructions
- Ensure WP-CLI is installed and configured for your WordPress installation.
- Run the command `wp wpmudev scan-posts` from your terminal.

This command will scan all public posts and pages, updating the `wpmudev_test_last_scan` post meta with the current timestamp.