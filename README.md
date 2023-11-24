# WPMUDEV Test Plugin #

This is a plugin that can be used for testing coding skills for WordPress and PHP.

# Development

## Composer
Install composer packages
`composer install`

## Build Tasks (npm)
Everything should be handled by npm.

| Command              | Action                                                 |
|----------------------|--------------------------------------------------------|
| `npm run watch`      | Compiles and watch for changes.                        |
| `npm run compile`    | Compile production ready assets.                       |
| `npm run build`  | Build production ready submodule inside `/build/` folder |


# Tasks

1. For some reason when running npm’s build command, the zipped file is pretty huge. Is there a way to reduce the size of it?


2. The plugin creates a new admin menu called Google Auth. Its admin page contains two fields, one is to insert **Client ID** and the second for **Client Secret**. There is a button that needs to save the inputs by passing them to backend and storing them in the `wpmudev_plugin_test_settings` option. What you need to do here is :
- a. Make sure that page is translatable.
- b. The **Client Secret** field needs to be set to password, so that the input value is not visible.
- c. Add a functionality to that button so when it is clicked, it will send the inputs to this Rest endpoint `wp-json/wpmudev/v1/auth/auth-url` to be saved. Once successfully stored or of an error response, there should be a notification.
- d. That endpoint already exists, but you need to make sure that it is secure.
- e. Then fill in the endpoint’s callback so that it can store the inputs in db. The option’s name is `wpmudev_plugin_test_settings` (as mentioned above) and its value should be an array with the following structure:

        array(
                'client_id'     => ’CLIENT ID’,
                'client_secret' => ‘CLIENT SECRET’,
        )


- f. Since we have data in DB now, when refreshing the page the values should be pre-filled correctly in the **Client ID** and **Client Secret** fields.

3. In order to use Google’s oAuth, we need to set a return url. Please create a new endpoint that we can use as a return url.
- a. The url of the endpoint should be `/wp-json/wpmudev/v1/auth/confirm`
- b. That endpoint should provide the functionality to get the user’s email. If the email exists and user is not logged in, it should log in user. If the email does not exist, that endpoint ’s callback should extract username from email (before @), generate a new password, create a new user and then log user in. After logging in, user should be redirected to admin. If the extracted username exists already, redirect to home page.


4. Please create a shortcode that will do the following:
- a. If user is logged in, show a message **Hi [USERNAME], enjoy my site!**
- b. If user is not logged in, show a link that will point to Google api’s auth Url. The link’s text can be something like **Login using Google oAuth**.


5. Please create a new admin menu page with title **Posts maintenance**. That page will have a button with title **Scan posts**. When that button is clicked, it will go through all public post and pages. There should be a filter that can modify post types. For each one of those posts (or specified post types), it should set the post_meta with name `wpmudev_test_last_scan` to current timestamp. The same operation should be repeated daily.
***Note:***
*One hypothetical reason for this would be to scan the content of the posts images or banned words etc, so please do not do a direct query to update post meta without looping thought posts.*


6. For convenience of our sysadmins, they would like to run the **Scan posts** action you created above from terminal. Could you write WPCLI command for that and share instructions?


7. A user that tried this plugin on a site, reported that there is a conflict and site crashed, but didn't mention any other information. Can you guess which class(es) might be conflicting and provide a fix?


8. As in every software there should be some testing done. A good starting point would be to run unit tests. As a demonstration, you need to create a unit test that will check if there is any error when running **Scan posts** you created in step 5.


**Feel free to modify any part of plugin’s existing code to accomplish your goals, but make sure your code follows wp coding standards.**

Thank you for taking this test. We wish you best of luck :)
