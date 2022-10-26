=== Magic Login - Passwordless Authentication for WordPress ===
Contributors:      handyplugins,m_uysl
Tags:              login, passwordless, passwordless-login, magic-login, magic-link
Requires at least: 5.0
Tested up to:      6.1
Requires PHP:      5.6
Stable tag:        1.6
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

Passwordless login for WordPress.

== Description ==

Easy, secure, and passwordless authentication for WordPress.

Streamline the login process by sending links to your users. No more passwords to remember, no more password resets, and no more password strength requirements.

**[Learn more about Magic Login](https://handyplugins.co/magic-login-pro/)**

= How does it work? 🪄 =
Magic login uses a technique called "magic links". The magic link is a unique link sent directly to your email inbox which allows you to authenticate once.

__Auto Login__: Magic Login also supports auto-login links for outgoing emails. It's useful when pending action from a user, such as reply a comment, complete the checkout, etc..

= PRO Features 🎩 =

Here are the premium features that come with Magic Login Pro:

- __CLI Command:__ Use WP-CLI to create login links.
- __Brute Force Protection:__ Limit rate of login attempts and block IP temporarily.
- __Login request throttling:__ Limit login link generation for a certain time of period.
- __IP Check:__ Enhance the security by restricting users to log in from the same IP address that requested the link.
- __Domain Restriction:__ Allow only certain domains to use the magic link.
- __Login Email Customization:__ Customize login message by using email placeholders.
- __Login Redirect:__ Redirect users to a specific page right after login. You can also redirect different pages based on the user role.

By upgrading to Magic Login Pro you also get access to one-on-one help from our knowledgeable support team and our extensive documentation site.

**[Learn more about Magic Login Pro](https://handyplugins.co/magic-login-pro/)**

= Contributing & Bug Report =
Bug reports and pull requests are welcome on [Github](https://github.com/HandyPlugins/magic-login). Some of our features are pro only, please consider before sending PR.

= Documentation =
Our documentation can be found on [https://handyplugins.co/magic-login-pro/docs/](https://handyplugins.co/magic-login-pro/docs/)


== Installation ==

= Manual Installation =

1. Upload the entire `/magic-login` directory to the `/wp-content/plugins/` directory.
2. Activate Magic Login through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= What is passwordless authentication? =

Passwordless authentication is an authentication method in which a user can log in to a computer system without entering (and remembering) a password.

= Is the magic links are secure? =

Yes! In fact, we thought this is more secure than the regular login due to most of the users are using weak passwords. Since magic login generates a random token for a limited time frame it makes the links quite strong and secure. Also, tokens can be used only once.

= When do login links expire? =

It expires in 5 minutes by default. You can change TTL under the "Token Lifespan" on the settings page. Enter "0" to disable automatic expiration.

= Why am I not getting login links? =

Magic Login uses WordPress built-in mail functions. So, it depends on your configuration. We highly recommend to use an SMTP service for better email delivery.

= How can I use a passwordless login form on any page? =

You can use `[magic_login_form]` shortcode or block. [Learn More.](https://handyplugins.co/magic-login-pro/docs/add-login-form-to-a-page/)


== Screenshots ==

1. Settings Page
2. Login Email
3. Login Block

== Changelog ==

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
