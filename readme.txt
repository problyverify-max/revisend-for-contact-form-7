=== CF7 Review Before Send ===
Contributors: slobostep
Tags: contact form 7, confirm, review, review before send, confirm step, multi-step, spam protection
Requires at least: 6.7
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.1.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a review-and-confirm step to Contact Form 7 forms. Visitors see a summary of what they typed and confirm before the mail is sent.

== Description ==

Contact Form 7 is the most popular form plugin on WordPress, but it sends the
mail the moment the visitor clicks the submit button. CF7 Review Before Send
adds an optional confirmation step: after the form passes Contact Form 7's
native validation, the visitor sees a summary of what they typed and must
click "Confirm & Send" before the form is actually submitted. If they spot a
mistake, they can go back with "Edit".

= Why use it? =

* **Reduce mistakes** — visitors can review their input before it is sent.
* **Native CF7 validation** — required-field and format messages are shown
  exactly as Contact Form 7 normally shows them, before the review step.
* **Per-form toggle** — enable the review step only on the forms you want.
* **One-time token** — prevents double submissions and bypass attempts.
* **File uploads supported** — files are preserved between the review step
  and the confirmed submit, then attached to the mail.
* **Built-in spam protection** — honeypot field and minimum-fill-time check.
* **Lightweight** — no jQuery, no external services, translation-ready.

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

= Can I change the labels (Edit / Confirm & Send)? =

Yes. The labels are translatable and can also be customized with the
`cf7rb_review_labels` filter.

== Changelog ==

= 0.1.6 =
* Change: on screens narrower than 480px the Edit and Confirm buttons are
  stacked full-width for easier tapping.

= 0.1.5 =
* Change: the review step now runs after Contact Form 7's native validation.
  Invalid forms show CF7's own per-field messages; the preview is shown only
  when the form is valid. No custom validation messages are used.
* Fix: the minimum submit time is reduced to 1 second, future timestamps are
  accepted (clock skew), and the maximum page-open time limit is removed, so
  legitimate submissions are never rejected.

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
