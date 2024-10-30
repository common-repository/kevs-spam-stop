=== Kev's Spam Stop ===
Contributors: kevp75
Donate link: https://paypal.me/kevinpirnie
Tags: block spam, spam, stop spam, kill spam, spam stop
Requires at least: 6.0
Tested up to: 6.3
Requires PHP: 7.4
Stable tag: 0.1.18
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Plugin to attempt to stop spam form submissions.

== Description ==

This is yet another plugin that attempts to block spam form posts. We attempt to do so using some online list resources and block based on what we find throughout.  We then mark the post as spam, and prevent any email from being sent from the site.

As of right now, this only checks the comment forms.  We are working to build the checks into the major form builders.

== Features ==

Checks form content against a massive list of potential spam words, checks form poster email addresses against a massive list of known spam email domains, checks the form posters IP address against a massive list of known spam IP addresses, and then checks the online DNSBL's for listings.

== Installation ==

1. Download the plugin, unzip it, and upload to your sites `/wp-content/plugins/` directory
    1. You can also upload it directly to your Plugins admin
	2. Or install it from the Wordpress Plugin Repository
2. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 0.1.18 =
* Implement: Local data stores
* Fix: Sanitization

= 0.1.11 =
* Implement: Spam word content checks for comment forms
    * if found, marks the comment as spam
* Implement: Spam domain checks for comment forms
    * if found, marks the comment as spam
* Implement: Spam IP checks for comment forms
    * if found, marks the comment as spam
* Implement: Spam IP DNSBL checks for comment forms
    * if found, marks the comment as spam

= 0.0.01 =
* INITIAL BUILD
