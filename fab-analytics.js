/**
 * @name fab-analytics.js
 * @description A dropin replacement for Google Analytics. Uses php endpoint to write json files for data storage
 * @version 0.1.3-rc
 * @author Hustle Launch https://www.hustlelaunch.com
 * @license GPL
 *
 * @todo
 * - [ ] track form submit, mailto: and tel: clicks as conversions
 * - [ ] send form data to endpoint and save to localStorage as JSON on field onchange events
 * - [ ] load field values from localStorage when a user returns if form was not submit
 * - [ ] send form data to an endpoint when form submits and clear localStorage
 * - [ ] send visit obeject to endpoint when page when script runs.
 */

const fab__endpoint = "https://www.hustlelaunch.com/api/post/visits/";

function fab__randomSessionToken() {
  if (window.localStorage.getItem("fab_session_token")) {
    return window.localStorage.getItem("fab_session_token");
  }

  const chars =
    "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890";

  let output = "";

  for (let i = 0; i < 16; i++) {
    output += chars.charAt(Math.floor(Math.random() * chars.length));
  }

  return output;
}

async function fab__getVisitorIpAddress() {
  const ip = await fetch("https://api.ipify.org?format=json");

  ip.then((response) => {
    return response.json();
  }).catch((error) => {
    console.error("Error:", error);
  });

  return ip;
}

const fab__visit = {
  domain: window.location.hostname,
  session_token: fab__randomSessionToken(),
  session_start: new Date.now(),
  session_end: new Date.now() + 20 * 60 * 60, // +20 mins
  pathname: window.location.pathname,
  ip: fab__getVisitorIpAddress(),
  dimensions: [window.innerWidth, window.innerHeight],
};

async function fab_storeEvent(data) {
  const res = await fetch(fab__endpoint, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  });

  res.then((response) => response.json());

  res.catch((error) => {
    console.error("Error:", error);
  });

  res.then((data) => {
    console.log("Success:", data);
  });

  return res;
}

async function fab__pageview() {
  const res = await fab_storeEvent(fab__visit);
}

function fab__startUserSession() {
  if (
    !(
      window.localStorage.getItem(fab_session_expire).getTime() >
      new Date.getTime()
    )
  ) {
    window.localStorage.setItem("fab_session_token", fab__visit.session_token);

    window.localStorage.setItem(
      "fab_session_started",
      fab__visit.session_start
    );

    window.localStorage.setItem(
      "fab_session_expires",
      fab__visit.session_end.getTime()
    );
  }

  fab__pageview();
}

fab__startUserSession();

window.addEventListener("beforeunload", function () {
  let event = fab__visit;

  event.session_end = new Date.now();

  this.window.localStorage.setItem(
    "fab_session_expires",
    event.session_end.getTime()
  );

  event = {
    ...event,
    event: "pageview",
    category: "page",
    action: "exit",
  };

  const save = async () => {
    const res = await fab_storeEvent(event);

    res.then((data) => {
      console.log("Success:", data);
    });

    res.catch((error) => {
      console.error("Error:", error);
    });

    return res;
  };

  return save();
});

window.addEventListener("unload", function () {
  fab__pageview();
});

document
  .querySelectorAll('a[href*="mailto:"], a[href*="tel:"]')
  .forEach(function (element) {
    let event = fab__visit;

    element.addEventListener("click", function (e) {
      event = {
        ...event,
        href: element.href,
        event: "conversion",
        category: element.href.includes("mailto:") ? "Email" : "Call",
        action: "click",
        data: element,
      };

      const save = async () => {
        const res = await fab_storeEvent(event);

        res.then((data) => {
          console.log("Success:", data);
        });

        res.catch((error) => {
          console.error("Error:", error);
        });

        return res;
      };

      return save();
    });
  });

document.querySelectorAll("form").forEach(function (element) {
  let event = fab__visit;

  const action = async (e) => {
    event = {
      ...event,
      href: element.action,
      event: "conversion",
      category: "form",
      action: "submit",
      data: e.formData,
    };

    const save = async () => {
      const res = await fab_storeEvent(event);

      res.then((data) => {
        console.log("Success:", data);
      });

      res.catch((error) => {
        console.error("Error:", error);
      });

      return res;
    };

    // remove from localStorage by form action as key
    window.localStorage.removeItem(element.action);

    return save();
  };

  element.addEventListener("formdata", function (e) {
    return action(e);
  });

  element.addEventListener("submit", function (e) {
    return action(e);
  });

  element.addEventListener("change", function (e) {
    return action(e);
  });

  element
    .querySelectorAll("input, select, textarea, output")
    .forEach(function (field) {
      const presave_action = async (e) => {
        event = {
          ...event,
          href: element.action,
          event: "abandonment",
          category: "form",
          action: "presave",
          data: e.formData,
        };

        const presave = async () => {
          const res = await fab_storeEvent(event);

          res.then((data) => {
            console.log("Success:", data);
          });

          res.catch((error) => {
            console.error("Error:", error);
          });

          return res;
        };

        window.localStorage.setItem(element.action, JSON.stringify(event.data));

        return presave();
      };

      field.addEventListener("change", function (e) {
        return presave_action(e);
      });

      field.addEventListener("blur", function (e) {
        return presave_action(e);
      });

      field.addEventListener("focus", function (e) {
        return presave_action(e);
      });

      window.addEventListener("blur", function (e) {
        return presave_action(e);
      });

      window.addEventListener("beforeunload", function (e) {
        return presave_action(e);
      });

      document.addEventListener("unload", function (e) {
        console.log("unload");
        return presave_action(e);
      });

      window.addEventListener("mouseleave", function (e) {
        if (e.relatedTarget) {
          return presave_action(e);
        }
      });

      document.addEventListener("visibilitychange", function (e) {
        return presave_action(e);
      });

      window.addEventListener("touchmove", function (e) {
        return presave_action(e);
      });
    });
});
