//Based on https://developer.mozilla.org/en-US/docs/Web/API/notification
function notify_grant(){
  if (!('Notification' in window)) {
    console.log("This browser does not support notifications.");
    return;
  }
  // Otherwise, we need to ask the user for permission
  if (Notification.permission !== "granted" && Notification.permission !== "denied") {
    Notification.requestPermission().then(function (permission) {
      // If the user accepts, let's create a notification
      if (permission === "granted") {
        new Notification("Notifications enabled");
      }
    });
  }    
}

function notify(notify_text) {
  if (!('Notification' in window)) {
    console.log("This browser does not support notifications.");
    return;
  }
  // Let's check whether notification permissions have already been granted
  if (Notification.permission === "granted") {
    // If it's okay let's create a notification
    new Notification(notify_text);
  }
  // Otherwise, we need to ask the user for permission
  else if (Notification.permission !== "denied") {
    Notification.requestPermission().then(function (permission) {
      // If the user accepts, let's create a notification
      if (permission === "granted") {
        new Notification(notify_text);
      }
    });
  }
}
