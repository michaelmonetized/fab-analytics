/**
 * @name fab-analytics.js
 * @description A dropin replacement for Google Analytics. Uses php endpoint to write json files for data storage
 * @version 0.1.4-rc
 * @author Hustle Launch https://www.hustlelaunch.com
 * @license GPL
 *
 * @todo
- [x] track form submit, mailto: and tel: clicks as conversions
- [x] send form data to endpoint and save to localStorage as JSON on field onchange events
- [x] load field values from localStorage when a user returns if form was not submit
- [x] send form data to an endpoint when form submits and clear localStorage
- [x] send visit obeject to endpoint when page when script runs.
- [x] set cookie on first visit
 */

const fab__host = "https://www.hustlelaunch.com";

const fab__endpoint = `${fab__host}/fab-analytics/api/post/visit/`;

/**
 * Generates a random session token
 * @returns {string}
 */
function fab__randomSessionToken() {
  const existingToken = window.localStorage.getItem("fab_session_token");

  if (existingToken) return existingToken;

  const chars =
    "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890";

  return Array.from({ length: 16 }, () =>
    chars.charAt(Math.floor(Math.random() * chars.length))
  ).join("");
}

/**
 * Gets the visitor's IP address
 * @returns {Promise<string>}
 * @throws {Error}
 * @todo
 */
async function fab__getVisitorIpAddress() {
  try {
    const response = await fetch("https://api.ipify.org?format=json");

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    return data.ip;
  } catch (error) {
    console.error("Error fetching IP address:", error);
    return null;
  }
}

const fab__ip = await fab__getVisitorIpAddress();

/**
 * Visit object
 * @type {Object}
 * @property {string} domain
 * @property {string} session_token
 * @property {number} session_start
 * @property {number} session_end
 * @property {string} pathname
 * @property {string} ip
 * @property {Array} dimensions
 * @todo
- [x] Add user agent
- [x] Add referrer
- [x] Add browser language
- [x] Add browser timezone
 */
const fab__visit = {
  domain: window.location.hostname,
  session_token: fab__randomSessionToken(),
  session_start: Date.now(),
  session_end: Date.now() + 20 * 60 * 1000, // +20 mins in milliseconds
  pathname: window.location.pathname,
  ip: "null",
  viewport: [window.innerWidth, window.innerHeight],
  user_agent: navigator.userAgent,
  referrer: document.referrer,
  browser_language: navigator.language,
  browser_timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
  device: navigator.userAgentData?.platform || navigator.platform,
  screen: {
    width: window.screen.width,
    height: window.screen.height,
    orientation: window.screen.orientation,
    dpi: window.screen.pixelDepth,
  },
  title: document.title,
};

/**
 * Stores an event in the endpoint
 * @param {Object} data
 * @returns {Promise<Object>}
 */
async function fab_storeEvent(data) {
  try {
    const response = await fetch(fab__endpoint, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    });

    const resultText = await response.text();

    // Attempt to parse the response as JSON
    try {
      const result = JSON.parse(resultText);
      return result;
    } catch (jsonError) {
      throw new Error("Response is not valid JSON");
    }
  } catch (error) {
    throw error;
  }
}

/**
 * Tracks a pageview
 * @returns {Promise<void>}
 */
async function fab__pageview() {
  try {
    if (!fab__visit.ip) {
      fab__visit.ip = fab__ip || fab__getVisitorIpAddress();
    }

    console.log("IP address:", fab__visit.ip);

    return fab_storeEvent({
      ...fab__visit,
      event: "pageview",
      category: "session",
      action: "start",
    });
  } catch (error) {
    console.error("Error in fab__pageview:", error);
  }
}

/**
 * Starts a new user session if the current session has expired
 */
function fab__startUserSession() {
  const sessionExpires = window.localStorage.getItem("fab_session_expires");

  if (!sessionExpires || Number(sessionExpires) < Date.now()) {
    window.localStorage.setItem("fab_session_token", fab__visit.session_token);
    window.localStorage.setItem("fab_session_start", fab__visit.session_start);
    window.localStorage.setItem("fab_session_expires", fab__visit.session_end);

    // Set cookie on first visit
    document.cookie = `fab_session_token=${fab__visit.session_token}; expires=${fab__visit.session_end}; path=/`;
  }

  fab__pageview();
}

fab__startUserSession();

/**
 * Tracks page exits
 */
window.addEventListener("beforeunload", async () => {
  fab__visit.session_end = Date.now();

  window.localStorage.setItem("fab_session_expires", fab__visit.session_end);

  const event = {
    ...fab__visit,
    event: "pageview",
    category: "page",
    action: "exit",
  };

  await fab_storeEvent(event);
});

window.addEventListener("unload", fab__pageview);

/**
 * Tracks click conversions
 */
document
  .querySelectorAll('a[href*="mailto:"], a[href*="tel:"]')
  .forEach((element) => {
    element.addEventListener("click", async (e) => {
      const event = {
        ...fab__visit,
        href: element.href,
        event: "conversion",
        category: element.href.includes("mailto:") ? "Email" : "Call",
        action: "click",
        data: element,
      };

      await fab_storeEvent(event);
    });
  });

/**
 * Tracks form conversions and abandonments
 */
document.querySelectorAll("form").forEach((form) => {
  const submitHandler = async (event, action) => {
    const formData = new FormData(event.target);

    const data = Object.fromEntries(formData.entries());

    const formEvent = {
      ...fab__visit,
      href: form.action,
      event: "conversion",
      category: "form",
      action,
      data,
    };

    await fab_storeEvent(formEvent);

    if (action === "submit") {
      window.localStorage.removeItem(form.action);
    }
  };

  form.addEventListener("submit", (e) => submitHandler(e, "submit"));

  // Handle form abandonment
  const handleFormAbandonment = async (form, action) => {
    const formData = new FormData(form);

    const data = Object.fromEntries(formData.entries());

    const event = {
      ...fab__visit,
      href: form.action,
      event: "abandonment",
      category: "form",
      action,
      data,
    };

    await fab_storeEvent(event);
  };

  /*const abandonmentEvents = [
    "change",
    "blur",
    "focus",
    "beforeunload",
    "unload",
    "mouseleave",
    "visibilitychange",
    "touchmove",
  ];

  abandonmentEvents.forEach((eventType) => {
    form.addEventListener(eventType, (e) =>
      //handleFormAbandonment(form, "presave")
    );
  });*/

  form.querySelectorAll("input, select, textarea, output").forEach((field) => {
    field.addEventListener("change", (e) => {
      window.localStorage.setItem(
        form.action,
        JSON.stringify(new FormData(form))
      );

      //handleFormAbandonment(form, "presave");
    });
  });
});
