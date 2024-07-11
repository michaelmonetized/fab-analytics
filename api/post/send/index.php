<?php

if (isset($data)) {
  $mail = [
    'to' => 'sales@hustlelaunch.com',
    'subject' => 'New Lead',
    'body' => "",
    'headers' => [
      'From: success@hustlelaunch.com',
      'Content-Type: text/html; charset=UTF-8',
    ],
  ];

  foreach ($data->data as $key => $value) {
    $mail['body'] .= $key . ": " . $value . "\n";
  }

  $mail['body'] .= "\n\n" . $data->pathname;

  mail($mail['to'], $mail['subject'], $mail['body'], $mail['headers']);
}
