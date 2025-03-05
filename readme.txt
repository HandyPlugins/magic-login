=== Magic Login - Passwordless Authentication for WordPress - Login Without Password ===
Contributors:      handyplugins, m_uysl
Tags:              login, passwordless, passwordless-login, magic-login, magic-link
Requires at least: 5.0
Tested up to:      6.7
Requires PHP:      7.2
Stable tag:        2.4
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html
Donate link:       https://handyplugins.co/donate/

Passwordless login for WordPress. Streamline the login process by sending magic links to your users.

== Description ==

Easy, secure, and passwordless authentication for WordPress.

Streamline the login process by sending links to your users. No more passwords to remember, no more password resets, and no more password strength requirements.

**[Learn more about Magic Login](https://handyplugins.co/magic-login-pro/)**

= Key Features 🌟 =

- **Passwordless Authentication**: No more forgotten passwords or complex requirements.
- **Magic Links**: Secure, unique links sent directly to users' email inboxes.
- **Auto Login**: Support for auto-login links in outgoing emails. It's useful when pending action from a user, such as reply a comment, complete the checkout, etc.
- **User-Friendly**: Simplifies the login process for all users.
- **Enhanced Security**: Reduces risks associated with weak passwords.

= How does it work? 🪄 =

1. User enters their email address.
2. A unique magic link is sent to their inbox.
3. Clicking the link authenticates and logs in the user.

= PRO Features 🎩 =

Here are the premium features that come with Magic Login Pro:

- __SMS Login:__ Send magic login links via SMS. [Learn more](https://handyplugins.co/docs/passwordless-authentication-with-sms/).
- __Registration:__ Enable easy user registration directly from the login form or with a shortcode. [Learn more](https://handyplugins.co/docs/magic-login-registration/).
- __CLI Command:__ Use WP-CLI to create login links.
- __Brute Force Protection:__ Limit rate of login attempts and block IP temporarily.
- __Login request throttling:__ Limit login link generation for a certain period.
- __IP Check:__ Enhance the security by restricting users to log in from the same IP address that requested the link.
- __Domain Restriction:__ Allow only certain domains to use the magic link.
- __Login Email Customization:__ Customize login message by using email placeholders.
- __Login Redirect:__ Redirect users to a specific page right after login. You can also redirect different pages based on the user role.
- __WooCommerce Integration:__ Seamless checkout experience for returning customers. [Learn more](https://handyplugins.co/docs/magic-login-woocommerce-integration/).
- __Easy Digital Downloads (EDD) Integration:__ Enhance the checkout experience with seamless magic login support. [Learn more](https://handyplugins.co/docs/magic-login-edd-integration/).
- __FluentCRM Integration:__ Send magic login links directly via FluentCRM. [Learn more](https://handyplugins.co/docs/magic-login-fluent-crm/).
- __reCAPTCHA Integration:__ Safeguard your login and registration forms from spam with Google reCAPTCHA. [Learn more](https://handyplugins.co/docs/magic-login-spam-protection/#1-toc-title).
- __Cloudflare Turnstile Integration:__ Enhance spam protection for your login and registration forms using Cloudflare Turnstile. [Learn more](https://handyplugins.co/docs/magic-login-spam-protection/#2-toc-title).
- __API Support:__ Integrate Magic Login with your custom applications using the REST API.

By upgrading to Magic Login Pro you also get access to one-on-one help from our knowledgeable support team and our extensive documentation site.

**[Explore Magic Login Pro](https://handyplugins.co/magic-login-pro/)**

= Documentation =
Our documentation can be found on [https://handyplugins.co/docs-category/magic-login-pro/](https://handyplugins.co/docs-category/magic-login-pro/)

= Contributing & Bug Report =
Bug reports and pull requests are welcome on [GitHub](https://github.com/HandyPlugins/magic-login). Some of our features are pro only, please consider before sending PR.

__If you like Magic Login, then consider checking out our other projects:__

* <a href="https://handyplugins.co/magic-login-pro/" rel="friend">Magic Login Pro</a> – Easy, secure, and passwordless authentication for WordPress.
* <a href="https://handyplugins.co/easy-text-to-speech/" rel="friend">Easy Text-to-Speech for WordPress</a> – Transform your textual content into high-quality synthesized speech with Amazon Polly.
* <a href="https://handyplugins.co/handywriter/" rel="friend">Handywriter</a> – AI-powered writing assistant that can help you create content for your WordPress.
* <a href="https://handyplugins.co/paddlepress-pro/" rel="friend">PaddlePress PRO</a> – Paddle Plugin for WordPress
* <a href="https://poweredcache.com/" rel="friend">Powered Cache</a> – Caching and Optimization for WordPress – Easily Improve PageSpeed & Web Vitals Score
* <a href="https://handyplugins.co/wp-accessibility-toolkit/" rel="friend">WP Accessibility Toolkit</a> – A collection of tools to help you make your WordPress more accessible.


== Installation ==

= Manual Installation =

1. Upload the entire `/magic-login` directory to the `/wp-content/plugins/` directory.
2. Activate Magic Login through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= What is passwordless authentication? =

Passwordless authentication is an authentication method in which a user can log in to a computer system without entering (and remembering) a password.

= Are the magic links secure? =

Yes! In fact, we thought this is more secure than the regular login due to most of the users are using weak passwords. Since magic login generates a random token for a limited time frame it makes the links quite strong and secure.

= When do login links expire? =

It expires in 5 minutes by default. You can change TTL under the "Token Lifespan" on the settings page. Enter "0" to disable automatic expiration.

= Why am I not getting login links? =

Magic Login uses WordPress built-in mail functions. So, it depends on your configuration. We highly recommend to use an SMTP service for better email delivery.

= How can I use a passwordless login form on any page? =

You can use `[magic_login_form]` shortcode or block. [Learn More.](https://handyplugins.co/docs/add-magic-login-form-to-a-page/)

= Why are users redirected back to the page where they added the magic login form via shortcode? =

This behavior occurs because the magic login form is designed to use the current page as the target redirection URL by default. It's a way to ensure a smooth user experience by bringing users back to the page they started from.
However, if you wish to alter this behavior, you can easily do so by passing an empty redirect_to="" parameter within the shortcode.  [Learn More.](https://handyplugins.co/docs/magic-login-shortcode/)


== Screenshots ==

1. Login Page
2. Settings Page
3. Login Email
4. Login Block

== Changelog ==

= 2.4 (March 05, 2025) =
* [New Feature] Code Login – Users can log in with a code sent to their email or phone number instead of clicking a link.
* [Improvement] UI update for SMS Login feature. [Learn More](https://handyplugins.co/docs/passwordless-authentication-with-sms/)
* [Improvement] Applied `login_redirect` filter before `magic_login_redirect` to allow other plugins to modify the redirect URL.
* [Improvement] login.php is now deprecated in favor of LoginManager class.
* [Fix] Properly encode the redirection URL on the wp-login page.
* [Fix] Corrected various typos. Props [@szepeviktor](https://github.com/szepeviktor)
* Read the full update: [Magic Login 2.4](https://handyplugins.co/blog/magic-login-2-4-now-with-sms-login/)

= 2.3.5 (January 03, 2025) =
* [Fixed] Ensure proper handling of email recipient for {{MAGIC_LINK}} integration.
* [Fixed] French translation.
* [Improved] Applied `array_shift()` to extract the first recipient if `$atts['to']` is an array.

= 2.3.4 (December 18, 2024) =
* [Improved] {{MAGIC_LINK}} placeholder to support encoded values.
* [Improved] JavaScript handling for the magic login button by using `esc_url_raw` for form action and redirect URLs.
* [Added] `magic_login_get_wp_login_url` filter for customizing the login URL.
* [Deprecated] \MagicLogin\Utils\get_magic_login_url, use \MagicLogin\Utils\get_wp_login_url instead.
* [Updated] Dependencies.

= 2.3.3 (November 19, 2024) =
* [Improved] Enhanced login request handling with prioritized processing and added logging for header_sent scenarios.
* [Improved] Implemented pre-validation checks before magic link replacement to prevent potential issues.
* [Updated] Dependencies.
* Tested with WP 6.7

= 2.3.2 (October 07, 2024) =
* [Improved] Two-factor compatibility.
* [Updated] Dependencies.

= 2.3.1 (September 09, 2024) =
* Minor tweaks and adjustments.
* [Updated] Dependencies.

= 2.3 (July 10, 2024) =
* [Added] [REST API](https://handyplugins.co/docs/magic-login-rest-api/) option to UI.
* [Added] Passing `magic_login_form` to shortcode_attr for better customization.
* [Updated] Dependency updates.
* Tested with WP 6.6
* Learn more about the new features: [Magic Login 2.3](https://handyplugins.co/blog/magic-login-rest-api-support/)

= 2.2 (May 29, 2024) =
* [Added] Settings UI update with reflecting new PRO features.
* [Added] Custom events for AJAX requests.
* [Added] New filter `magic_login_token_ttl_by_user` to customize TTL for users.
* [Added] New filter `magic_login_error_message` to customize error messages.
* [Improved] Form styles.
* [Refactored] Improved settings page UI.
* [Refactored] Enhanced class autoloading.
* [Updated] Dependency updates.
* Learn more about the new features: [Magic Login Pro 2.2](https://handyplugins.co/blog/magic-login-registration-and-spam-protection/)

= 2.1.3 (April 19, 2024) =
* Improvements on uninstallation process.

= 2.1.2 (April 08, 2024) =
* Fix auto-login link when the recipient is specified in an array format.
* Dependency updates.

= 2.1.1 (March 13, 2024) =
* Tested with WP 6.5
* Dependency updates.

= 2.1 (February 13, 2024) =
* Updated settings page with PRO features.
* Added new attributes for shortcode; it's more flexible than ever. [Learn More](https://handyplugins.co/docs/magic-login-shortcode/)
* Fix: Encode the redirect_to parameter in the login link. (Better nG firewall compatibility)
* Dependency updates.

= 2.0.1 (January 15, 2024) =
* Fix German language that breaks auto-login links.
* Dependency updates.

= 2.0 (November 07, 2023) =
* Add {{MAGIC_LINK}} support to all outgoing emails that received by a single user.
* Add new placeholder supports: {{FIRST_NAME}}, {{LAST_NAME}}, {{FULL_NAME}}, {{DISPLAY_NAME}}, {{USER_EMAIL}}
* Add ajax spinner to the login form.
* Dependency updates.
* Minor tweaks on settings form.

= 1.9.1 (October 26, 2023) =
* Added French translation.
* Dependency updates.
* Fix deprecated variable format.
* Tested with WP 6.4

= 1.9 (July 25, 2023) =
* Added: AJAX support for login requests.
* Bumped PHP requirement to 7.2+
* Small tweaks and improvements.
* Tested with WP 6.3

= 1.8.1 (May 15, 2023) =
* Added: Styling for two-factor plugin.
* Minor UI changes.
* Small tweaks and improvements.
* Tested with WP 6.2

= 1.8 (February 18, 2023) =
* New feature: Token Validity - allows to specify how many times a token can be used.
* Improvements on the default login screen
* i18n improvements
* Added: German translation.
* Added: Autocomplete support.
* Added: New token `{{TOKEN_VALIDITY_COUNT}}` to customize email content.

= 1.7 (January 21, 2023) =
* PHP 8.1: fix deprecated 'FILTER_SANITIZE_STRING'
* UI/UX improvements on default login screen
* i18n improvements. Props [@emreerkan](https://github.com/emreerkan)
* Fix: standard wordpress redirect functionality. Props [@maartenhunink](https://github.com/maartenhunink)
* Fix: Skip the auto-login link for the magic login itself.
* Fix: Send email only once.

= 1.6 (October 26, 2022) =
* New feature: Auto Login Links

= 1.5.2 (September 27, 2022) =
* Bug fix: token validation

= 1.5.1 (September 26, 2022) =
* Fixed: redirection issue.
* Minor UI updates.
* Small tweaks and improvements.
* Tested with WP 6.1

= 1.5 (September 12, 2022) =
* Fixed: save tokens hashed in DB. Props [@snicco](https://github.com/snicco/snicco)
* Added: username-only mode. define `MAGIC_LOGIN_USERNAME_ONLY` in the config file to use it.
* Email improvements: Check email contents before converting line breaks to `<br/>` tags.
* Small tweaks and improvements.

= 1.3 (April 19, 2022) =
* Tested with WP 6.0
* UI updates.
* Fire `wp_login` hook as WP Core does on successful login.
* Add new filter: `magic_login_email_headers`.
* Fix email title html escaping.
* Small tweaks and improvements.

= 1.2.2 =
* Tested with WP 5.9
* Update Shared UI
* Fix compatibility issue with TML plugin
* Add redirection cancellation option to the login block.
* Check `logged-in` while saving the settings
* Small tweaks and improvements.

= 1.2.1 =
* New: Integrate with the standard login form.
* Fix: Enqueue admin assets on the settings page only.
* Allow login block only once for a post.
* Small tweaks and improvements.

= 1.2 =
* New: Magic Login Block - It's much easier to add and customize the login form in the block editor.
* Customizable token intervals added. (removed 1-60 minutes restriction)
* New placeholder added: {{EXPIRES_WITH_INTERVAL}} to display TTL with the interval.
* Updated Shared UI
* Improved documentation on settings page.
* New: Show an error message when the user doesn't exist.
* New filter: Added `magic_login_invalid_token_error_message` to customize error message.

= 1.1.3 =
* Fix: Scheduled expired token cleanup

= 1.1.2 =
* Update Shared UI
* Shortcode `magic_login_form` now supports `redirect_to` attribute
* fix: don't display login form if the user already logged-in

= 1.1.1 =
* Hotfix: return shortcode output instead of printing

= 1.1 =
* Tested with WP 5.8
* Shortcode `magic_login_form` support added!
* fix: make sure `deactivate_plugins` exists when manually switching versions

= 1.0.3 =
* Update Shared UI
* fix: add text-domain for missing strings

= 1.0.2 =
* Update Shared UI
* Tested with WP 5.7

= 1.0.1 =
* Update Shared UI

= 1.0 =
* First release

== Upgrade Notice ==

= 1.5 =
 - The tokens will be hashed before saving in meta with this version. Due to this change, existing tokens will not work right after the update.

= 1.0 =
First Release
