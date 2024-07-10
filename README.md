# fab-analytics.js

## v0.1.4-rc

A dropin replacement for Google Analytics. Uses php endpoint to write json files for data storage

## Self Hosted Install

```bash
git clone https://github.com/michaelmonetized/fab-analytics.js.git
```

### Congigure

change the fab\_\_host in `fab-analytics.js` on line 17

```js
const fab__host = "https://www.hustlelaunch.com";
```

## Usage

```html
<script src="//yourdomain.com/fab-analytics/fab-analytics.js"></script>
```

## Roadmap

- [ deferred ] Change storage file naming conevention to include event type
- [-] Add get api path
- [ ] Implement graphs/charts
- [ ] Pick ip locate provider (ipify, ipinfo, etc)
