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
- [x] Add get api path
- [x] Implement graphs/charts
- [/] Pick ip locate provider (ipify, ipinfo, etc)
- [ ] Only render conversions chart if there are conversions
- [ ] Leads table needs a max height
- [ ] Destory table if no rows in tbody
- [ ] Put abandon visibility/processing behind an option
- [ ] Track page load time
- [ ] Track bounces
- [ ] Add referrers table to dashboard
- [ ] Add sessions/visitor average
- [ ] Add pages/session avg
- [ ] Add session duration
- [ ] make date range filters work
- [ ] make accordion titles toggleable (label with radio?)
- [ ] Clear fuckery out of test dashboard
- [ ] Bundle dash assets
- [ ] Convert host dash to a react/expo and react/nextjs flavor
- [ ] Create wp admin dashoard plugin
- [ ] Create an agency client dashboard
- [ ] Config generator for client + host
- [ ] SEO analysis report
- [ ] Sign up form
- [ ] Pro plan features:
  - [ ] AI SEO suggestions
  - [ ] AI conversion optimization suggestions
  - [ ] Configurable reports?
- [ ] Docs site
- [ ] GH pages
- [ ] Pitch deck + video
- [ ] Landing page

### Enlisting the help of AI

## Dashboard development

### The fetch request is…

```js
fetch(`/fab-analytics/api/get/`)
.then((response) => {
  return response.json();
})
.then((data) => {
  …do something with data and template tags…
});
```

## This response.json data structure

```json
[
  "{{domain_name}}": {
    "pageviews": int,
    "unique_sessions": int,
    "unique_visitors": int,
    "conversions": int,
    "calls": int,
    "emails": int,
    "submissions": int,
    "form_abandonments": int,
    "data": {
      domain: string,
      session_token: string,
      session_start: string,
      session_end: string,
      pathname: string,
      ip: string,
      viewport:string,
      user_agent: string,
      referrer: string,
      browser_language: string,
      browser_timezone: string,
      device: string,
      screen: {
        width: string,
        height: string,
        orientation: string,
        dpi: string,
      },
      title: string,
      event: string,
      category: string,
      action: string,
      data: object,
    }
  },
  "{{domain_name}}":...
]
```

### and something like the template i want to use

```html
<div class="results">
  <h1>Results</h1>

  <div class="result-item" data-domain="{{domain_name}}">
    <div class="accordion-title">
      <div>
        <h2 class="domain text-4xl">{{domain_name}}</h2>
      </div>

      <div>
        <div>
          <input type="date" class="date-picker" name="start" />
          <input type="date" class="date-picker" name="end" />
        </div>

        <div>
          <img src="view.svg" />
        </div>
      </div>
    </div>

    <div class="data">
      <div class="data-summary">
        <div class="summary-item pageviews">
          <div>{{data.pageviews}}</div>

          <div>page views</div>
        </div>

        <div class="summary-item unique-sessions">
          <div>{{data.unique_sessions}}</div>

          <div>sessions</div>
        </div>

        <div class="summary-item unique-visitors">
          <div>{{data.unique_visitors}}</div>

          <div>visitors</div>
        </div>

        <div class="summary-item conversions">
          <div>{{data.conversions}}</div>

          <div>conversions</div>
        </div>

        <div class="summary-stack">
          <div class="summary-item calls">
            <div>{{data.calls}}</div>

            <div>calls</div>
          </div>

          <div class="summary-item emails">
            <div>{{data.emails}}</div>

            <div>emails</div>
          </div>

          <div class="summary-item submissions">
            <div>{{data.submissions}}</div>

            <div>submissions</div>
          </div>

          <div class="summary-item conversion-rate">
            <div>{{data.conversions/data.unique_sessions}}</div>

            <div>conversion rate</div>
          </div>
        </div>
      </div>

      <div cladd="data-leads">
        <div class="accordion-title">
          <div>
            <h3>Leads</h3>
          </div>

          <div>
            <img src="view.svg" />
          </div>
        </div>
        <div class="data-leads-table">
          <table>
            <thead>
              <tr>
                <th>Date</th>
                <th>Page</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Message</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {{foreach lead in data.data where event==='form'}}
              <tr
                data-lead-id="{{data.session_token+data.session_time}}"
                data-status="{{sent|abandoned|replied|archived|deleted|flagged}}">
                <td>{{lead.date}}</td>
                <td>{{lead.docuemnt_title}}</td>
                <td>{{lead.name}}</td>
                <td>{{lead.phone}}</td>
                <td>{{lead.email}}</td>
                <td>{{lead.message}}</td>
                <td>
                  <div class="lead-actions">
                    <button class="lead-action view">
                      <img src="view.svg" />
                    </button>
                    <button class="lead-action reply">
                      <img src="reply.svg" />
                    </button>
                    <button class="lead-action fwd">
                      <img src="fwd.svg" />
                    </button>
                    <button class="lead-action archive">
                      <img src="archive.svg" />
                    </button>
                    <button class="lead-action flag">
                      <img src="flag.svg" />
                    </button>
                    <button class="lead-action delete">
                      <img src="delete.svg" />
                    </button>
                  </div>
                </td>
              </tr>
              {{/foreach lead in data.data where event==='form'}}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
```

### Example design

![image info](https://github.com/michaelmonetized/fab-analytics/blob/v0.1.4-rc/dashboard.png?raw=true)

#### Instructions

For test purposes I'm leaning towards writing this in vanilla browser js and html &lt;template&gt; tags

I could probably get it done in nextjs/react quickly, but i'd like to see what it takes to get the code done more directly.

The handlebars in the template design is just a placeholder i don't want to rely on handlebars.js for this at all.

Write the template tags and js to get the dashboard usable.
