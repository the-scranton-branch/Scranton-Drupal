<?php

/**
 * Send a simple request to the current site to wake it up and initiate traffic.
 */

// Render Environment name with link to site, <https://{ENV}-{SITENAME}.pantheon.io|{ENV}>
$url = 'https://' . $_ENV['PANTHEON_ENVIRONMENT'] . '-' . $_ENV['PANTHEON_SITE_NAME'] . '.pantheonsite.io';

// Initialize CURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_NOBODY,1);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

// Watch for messages with `terminus workflows watch --site=SITENAME`
print("\n==== RING A DING DING ====\n");
$result = curl_exec($ch);
print("RESULT: $result");
print("\n===== Have a great day! =====\n");
curl_close($ch);