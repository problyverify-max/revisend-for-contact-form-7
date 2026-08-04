=== CF7 Review Before Send ===
Contributors: slobostep
Tags: contact form 7, confirm, review, multi-step, spam
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 0.1.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a review-and-confirm step to Contact Form 7 forms before the mail is sent.

== Description ==

Contact Form 7 is the most popular form plugin on WordPress, but it sends the
mail the moment the visitor clicks the submit button. CF7 Review Before Send
adds an optional confirmation step: the visitor sees a summary of what they
typed and must confirm before the form is actually submitted.

* Per-form toggle: enable the review step only on the forms you want.
* One-time token prevents double submissions.
* File uploads are preserved between the review step and the final submit.
* Built-in spam protection: honeypot field + minimum-fill-time check.
* No jQuery, no external services, translation-ready.

== Installation ==

1. Upload the `cf7-review-before-send` folder to `/wp-content/plugins/`.
2. Activate the plugin through the Plugins screen.
3. Edit a Contact Form 7 form and check "Show a review step before this form is sent".
4. Done. The form now requires confirmation before sending.

== Frequently Asked Questions ==

= Does it work with file upload fields? =

Yes. Files are validated and temporarily stored on the server between the
review step and the confirmed submit, then attached to the mail.

= What happens if JavaScript is disabled? =

The confirmation step requires JavaScript. For forms with the step enabled,
submissions without a confirmation token are blocked.

= Does it work with third-party Contact Form 7 add-ons? =

Anything that hooks into the standard Contact Form 7 submission flow works
with the confirm step. Add-ons that hijack the submit button itself may
interfere with the review step.

== Changelog ==

= 0.1.4 =
* Change: the review step is always shown. Missing required fields appear as
  "—" in the preview and the confirm button stays disabled until the form is
  valid. Contact Form 7's native validation is used on confirmation — no
  custom validation messages are shown.

= 0.1.3 =
* Fix: temporary upload folders are now fully removed after the mail is sent.

= 0.1.2 =
* Fix: empty required fields no longer reach the review step. The form is
  validated (client and server side) before the preview is shown.
* Fix: file upload rules are now correctly disabled on the confirm step.

= 0.1.1 =
* Fix: form could be submitted again without refresh after a successful send.
* Fix: clicking Confirm after Edit could submit the form twice.

= 0.1.0 =
* Initial release.
