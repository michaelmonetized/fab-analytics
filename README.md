# fab-analytics

First-party analytics drop-in: a browser script (`fab-analytics.js`) posts visit and conversion events to a PHP endpoint that writes JSON files to disk under `logs/{domain}/`.

Version noted in source: **0.1.3-rc**. License intended as GPL (see header comments; `LICENCE.md` is currently empty).

This is **not** Google Analytics and not a hosted SaaS dashboard — it is a small self-hosted collector.

## What it tracks

Client script (`fab-analytics.js`):

- Pageview / session start (cookie + `localStorage` session token, ~20 minute expiry)
- Page exit (`beforeunload`)
- Clicks on `mailto:` and `tel:` links as conversions
- Form submit as conversion; field `change` / abandonment-style events as `presave`
- Form field snapshots into `localStorage` keyed by form `action`
- Visitor metadata: hostname, pathname, IP (via ipify), viewport, UA, referrer, language, timezone, screen

Server (`api/post/visit/index.php`):

- Accepts POST JSON with at least `domain` and `session_token`
- Writes `{token}-{microtime}.json` under `logs/{domain}/`
- CORS `Access-Control-Allow-Origin: *`
- Optional include of `api/post/send/index.php` when domain contains `oxstu` and `category === 'form'`

## Install / deploy

1. Put this repo (or at least `fab-analytics.js` + the `api/` tree) on a PHP host with write access for `logs/`.
2. Point the client endpoint constant at your install. Today the script hard-codes:

```js
const fab__endpoint =
  "https://www.hustlelaunch.com/fab-analytics/api/post/visit/";
```

Change that URL to your own `…/api/post/visit/` before using on another site.

3. Ensure the web server can create `logs/{domain}/` (the visit endpoint `mkdir`s as needed with `0755`).

## Use on a page

```html
<script src="/path/to/fab-analytics.js"></script>
```

There is no package manager install and no build step. `test.html` is a local Tailwind CDN page with sample `tel:` / `mailto:` links and a form for manual checks (it references `/fab.js` in one script tag — adjust the path to `fab-analytics.js` when testing locally).

Root `index.php` currently just `die()`s — the live collector is under `api/post/visit/`.

## Run / smoke-test locally

You need PHP (and a way to serve static JS):

```bash
# from repo root — serves HTML/JS; POST target must still reach a PHP-capable URL
php -S localhost:8080
```

Then open `http://localhost:8080/test.html` after fixing the script `src`, or POST JSON to `/api/post/visit/` with `domain` + `session_token`.

## Privacy / ops notes

- Events may include IP, form field values, and other PII. Treat `logs/` as sensitive.
- Endpoint is open CORS and lightly validated — harden auth, rate limits, and path safety before public production use.
- Client depends on `https://api.ipify.org` for IP lookup.

## Files

| Path | Role |
|------|------|
| `fab-analytics.js` | Browser collector |
| `api/post/visit/index.php` | JSON → disk writer |
| `api/post/send/index.php` | Optional form-forward hook |
| `test.html` | Manual test page |
| `LICENCE.md` | Placeholder (empty) |

## License

Headers say GPL; fill in `LICENCE.md` when you publish formally.
