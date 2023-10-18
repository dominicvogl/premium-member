# Raidboxes Coding challenge

### Premium Member Plugin

Welcome to our WordPress development mock challenge!
In this challenge, we kindly invite you to create a custom mock WordPress plugin for a membership website.
This plugin will handle user registration, activation and login functionality as well as the displaying of a member page.

### Requirements:

1. Your task is to create a new user role called "Raidboxes Premium Member" with special capabilities.
2. The user registration form should include basic fields like username, email, and password.
3. After successful registration, let's make it special by sending users a confirmation email with a unique activation link. The link should expire within 24 hours (See Code / Project guidelines) to ensure security and verify their membership.
4. At Raidboxes we'd like to provide a seamless user experience, so please create a login form shortcode that can be added to any page.
5. Once users successfully log in, they should be redirected to a personalized page displaying their username, email and password based on their user role.
6. Make sure that only the logged in Member are able to access **their** specific page above.
7. Let's add a user-friendly feature: a password reset option. Users should be able to reset their passwords via email if they forget them, for a hassle-free experience because a project manager had this bright idea very late in the project.
8. The plugin needs to be configurable in the WP-Dashboard. It should be possible for WP-Admins to alter the following:
- Registration active (Checkbox): Enable/Disables the registration
- Login active (Checkbox): Enable/Disables the possibility to login for these members, however the login form should still show.
- Link expiration time (number input): Here a number (in minutes) should be entered which overwrite thes expiration time of the link in step 3.
9. When deactivating the plugin, give the admin user a choice between keeping all the data that the plugin created in the database (hooks, filters AND user data) or deleting everything.

### Code / Project guidelines:

- We consider this task to take around 4 hours of time.
- You are free to use every tool you want, this includes Stack Overflow, Search Engines, Books, Plugin Boilerplates, Forums, Chatrooms and AI. However, you are not allowed to let a third party (i.E. external contractor) do the work for you.
- Please only deploy into this repository, commit and push often.
- We value WordPress best practices, so please follow the WordPress Coding Standards as you develop the plugin.
- Feel free to leverage hooks and filters to integrate your custom functionalities seamlessly.
- It's important to keep your code scalable, performance-oriented, and in line with WordPress core functionalities.
- Don't forget to provide comments and documentation in your code to explain your thought process and any complexities.
- For the frontend design, use the latest Bootstrap version with standard design. If you want to add CSS, please make use of SCSS/SASS.
- Mobile first.
- An absolute must is that you write unit tests for your plugin, that can be triggered the WordPress way.
- Security is a top priority! Ensure that your plugin has robust security measures in place. This includes sanitizing inputs, protecting against SQL injection attacks, and defending against password brute-forcing.

If you have any questions prior to starting this mock challenge, feel free to reach out to matthias@raidboxes.io