=== Form Guardian for Contact Form 7 ===  
Contributors: sandeepkamp  
Tags: spam, contact form 7, security, honeypot  
Requires at least: 6.0  
Tested up to: 7.0  
Requires PHP: 7.4  
Stable tag: 1.1  
License: GPLv2 or later

Protect Contact Form 7 forms from spam with a honeypot, rate limiting, duplicate submission prevention, keyword filtering, disposable email blocking, and submission logs.

== Description ==

Form Guardian for Contact Form 7 adds an extra layer of spam protection to your CF7 forms without changing the front-end experience for legitimate users.

Features include:

*   Honeypot field protection
*   **NEW:** Duplicate submission protection within a configurable time period
*   Per-IP rate limiting for repeated submissions
*   Keyword filtering for suspicious text
*   Disposable email domain blocking
*   Submission logging for review and analysis
*   IP address blocking feature to ban persistent spammers

The plugin adds a simple settings screen under the WordPress admin where you can configure the protection rules.

== Installation ==

1.  Upload the plugin files to the /wp-content/plugins/form-guardian-for-contact-form-7/ directory.
2.  Activate the plugin through the 'Plugins' screen in WordPress.
3.  Go to Form Guardian > Settings to configure the protection options.

== Frequently Asked Questions ==

= Does this plugin work with Contact Form 7? =  
Yes. This plugin is built specifically for Contact Form 7.

= Does it require any extra setup? =  
No. After activation, it starts protecting forms immediately and you can adjust the settings from the plugin settings page.

== Screenshots ==

1. Detailed activity logs to inspect blocked and passed submission data.
2. Settings panel where you can configure security restrictions and IP blocklists.
3. Contact Form 7 Guardian main dashboard showing submission trends, blocked counts, and recent activity logs.

== Changelog ==

= 1.1 =

*   New: Implemented duplicate submission protection using temporary fingerprints.
*   New: Added SQL logging optimization and suppressed false-positive database queries.
*   Update: Confirmed compatibility strings for version 1.1.

= 1.0.1 =

*   New: Added IP address blocking feature to prevent spam submissions.

= 1.0.0 =

*   Initial public release