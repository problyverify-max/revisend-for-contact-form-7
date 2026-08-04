=== Review Before Send for Contact Form 7 ===
Contributors: slobostep
Tags: contact form 7, confirmation, review, multi-step, spam protection
Requires at least: 6.7
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.1.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a review-and-confirm step to Contact Form 7 forms. Visitors see a summary of what they typed and confirm before the mail is sent.

== Description ==

Contact Form 7 is the most popular form plugin on WordPress, but it sends the
mail the moment the visitor clicks the submit button. Review Before Send for Contact Form 7
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

1. Upload the `review-before-send-for-contact-form-7` folder to `/wp-content/plugins/`.
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
`cf7rb_review_labels` filter. Example for your theme's functions.php:

```
add_filter( 'cf7rb_review_labels', function( $labels ) {
    $labels['confirm'] = 'Send Now';
    $labels['edit']    = 'Go Back';
    $labels['heading'] = 'Review your message';
    return $labels;
} );
```

Available labels: `heading`, `intro`, `edit`, `confirm`,
`error`, `accepted`, `declined`.

== Screenshots ==

1. Contact Form 7 editor with the Review Before Send tab.
2. Review Before Send per-form settings.
3. A standard Contact Form 7 form before submission.
4. Native Contact Form 7 validation messages.
5. A completed form before submission.
6. The desktop review step with Edit and Confirm & Send buttons.
7. The mobile review step with stacked buttons.
8. The native Contact Form 7 success message after sending.

== Changelog ==

= 0.1.9 =
* Change: more compact mobile layout — form font scales down to 0.9em,
  smaller input padding, tighter label and field spacing.

= 0.1.8 =
* Change: on screens narrower than 480px, forms with the review step enabled
  get mobile-friendly styling: full-width inputs and submit button, slightly
  smaller labels, and input font-size of 16px to prevent iOS auto-zoom.

= 0.1.7 =
* Fix: long values (emails, URLs) wrap inside the review box instead of
  overflowing the screen on narrow devices.

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
