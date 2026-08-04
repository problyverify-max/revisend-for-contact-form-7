# AGENTS.md — Review Before Send for Contact Form 7

## Plugin Info

- **Version:** 0.1.9
- **Requires:** Contact Form 7 (active), WordPress ≥ 6.7, PHP ≥ 7.4
- **GitHub:** https://github.com/slobostep/review-before-send-for-contact-form-7
- **License:** GPL-2.0-or-later
- **Text Domain:** `review-before-send-for-contact-form-7`

## Project Structure

```
cf7-review-before-send/
  AGENTS.md                                 ← This file
  review-before-send-for-contact-form-7.php ← Main plugin file (bootstrap, hooks)
  readme.txt                                ← WordPress.org readme
  uninstall.php                             ← Cleanup on uninstall
  index.php                                 ← Silence
  includes/
    class-settings.php                      ← CF7RB_Settings — per-form toggle UI
    class-session.php                       ← CF7RB_Session — transient-based token + temp files
    class-renderer.php                      ← CF7RB_Renderer — review-summary HTML builder
    class-ajax.php                          ← CF7RB_Ajax — review endpoint, submit gate, spam checks
  assets/
    css/cf7rb.css                           ← Plugin styles (mobile, desktop)
    js/cf7rb.js                             ← Plugin JS (review flow, fetch, edit/confirm)
  languages/                                ← .po/.mo translation files
```

## Available Hooks

| Hook | Type | Description |
|------|------|-------------|
| `cf7rb_review_labels` | filter | Override button/text labels (`heading`, `intro`, `edit`, `confirm`, `files`, `error`, `accepted`, `declined`, `fileNote`) |
| `cf7rb_min_submit_seconds` | filter | Minimum time in seconds before form can be submitted (default `1.0`) |

## Key Classes

- **`CF7RB_Settings`** — registers the meta box in CF7 editor, saves per-form toggle
- **`CF7RB_Session`** — creates/consumes/cleans up one-time tokens and temp uploads
- **`CF7RB_Renderer`** — builds the review summary HTML (`labels()`, `build_rows()`, `render_summary()`)
- **`CF7RB_Ajax`** — handles AJAX review action, gates confirmed submissions, attaches files, spam checks

## Git Rules

- **NEVER** commit or push directly to `main`
- Always create a new branch from latest `main` before any work: `feature/xxx`, `fix/xxx`, `docs/xxx`
- Run `git pull origin main` before branching off
- Always run `git status` and `git diff` first to show what will be committed
- Wait for explicit approval before `git add` + `git commit`
- Before push, suggest a PR title and description for the user to copy-paste
- Wait for explicit approval before pushing
- Only the repository owner merges into `main`
- No force-push

## Build / Deploy

- No build step — pure PHP/JS/CSS
- ZIP for WordPress.org is created from the `cf7-review-before-send/` folder
- Update version in:
  - `review-before-send-for-contact-form-7.php` (header + `CF7RB_VERSION`)
  - `readme.txt` (`Stable tag:`)
- After version bump, create ZIP: `zip -r review-before-send-for-contact-form-7-{version}.zip cf7-review-before-send/`

## Conventions

- No jQuery — vanilla JS only
- All user-facing strings go through `__()` / `_e()` with text domain `review-before-send-for-contact-form-7`
- No unnecessary comments in code
- English for code, comments, commit messages
