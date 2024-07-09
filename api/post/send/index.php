<?php

if (isset($data)) {
  $message = "";

  foreach ($data->data as $key => $value) {
    $message .= $key . ": " . $value . "\n";
  }

  $message .= "\n\n" . $data->ip;
  $message .= "\n\n" . $data->pathname;

  mail('success@hustlelaunch.com', 'New Lead', $data);
}
