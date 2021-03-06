=== Magic Login ===
Contributors:      handyplugins,m_uysl
Tags:              login,one-time-login,passwordless-login,magic-login,magic-link
Requires at least: 5.0
Tested up to:      5.8
Requires PHP:      5.6
Stable tag:        1.1.2
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

Passwordless login for WordPress.

== Description ==

Easy, secure, and passwordless authentication for WordPress.

__Plugin Website__: [https://handyplugins.co/magic-login-pro/](https://handyplugins.co/magic-login-pro/)

= How does it work? 🪄 =
Magic login uses a technique called "magic links". The magic link is a unique link sent directly to your email inbox which allows to authenticate you once.

= PRO Features 🎩 =

Here are the premium features comes with Magic Login Pro:

- __CLI Command:__ Use WP-CLI to create login links.
- __Brute Force Protection:__ Limit rate of login attempts and block IP temporarily.
- __Login request throttling:__ Limit login link generation for certain time of period.
- __IP Check:__ Enhance the security by restricting users to login from the same IP address that requested the link.
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

Passwordless authentication is an authentication method in which a user can log in to a computer system without the entering (and remembering) a password.

= Is the magic links are secure? =

Yes! In fact, we thought this is more secure than the regular login due to most of the users are using weak passwords. Since magic login generates a random token for a limited time frame it makes the links quite strong and secure. Also, tokens can be used only once.

= When does login links expire? =

It expires in 5 minutes by default. You can change it to anywhere between 1-60 minutes under the "Token Lifespan" on settings page.

= Why am I not getting login links? =

Magic Login uses WordPress built-in mail functions. So, it depends on your configuration. We highly recommend to use a SMTP services for better email delivery.

= How can I use a passwordless login form on any page? =

You can use `[magic_login_form]` shortcode.


== Screenshots ==

1. Settings Page
2. Login Email

== Changelog ==

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

= 1.0 =
First Release
