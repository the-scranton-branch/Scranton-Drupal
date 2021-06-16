<?php

require($_SERVER['HOME'] . '/private/scripts/vendor/autoload.php');

use Entrecore\GTMetrixClient\GTMetrixClient;
use Entrecore\GTMetrixClient\GTMetrixTest;

print("\n==== Start GTMetrix Report ====\n");

// Check for Pantheon environment
if (!empty($_ENV['PANTHEON_ENVIRONMENT']) && $_ENV['PANTHEON_ENVIRONMENT'] == 'live') {
  // Render Environment name with link to site, <https://{ENV}-{SITENAME}.pantheon.io|{ENV}>
  $url = 'https://' . $_ENV['PANTHEON_ENVIRONMENT'] . '-' . $_ENV['PANTHEON_SITE_NAME'] . '.pantheonsite.io';

  $client = new GTMetrixClient();
  $client->setUsername('kyle.taylor@pantheon.io');
  $client->setAPIKey('b8d357d48d94870111f7e4d35468e93e');

  $client->getLocations();
  $client->getBrowsers();
  $test = $client->startTest($url);

  //Wait for result
  while (
    $test->getState() != GTMetrixTest::STATE_COMPLETED &&
    $test->getState() != GTMetrixTest::STATE_ERROR
  ) {
    $client->getTestStatus($test);
    sleep(5);
  }

  // Gather data
  $gtmetrix = [
    'reportUrl' => $test->getReportUrl(),
    'pagespeedScore' => $test->getPagespeedScore(),
    'yslowScore' => $test->getYslowScore(),
  ];

  var_dump($gtmetrix);
}


//   $client->attach([
//     'fallback' => 'GTMetrix Page Performance',
//     'text' => 'GTMetrix Page Performance',
//     'color' => 'success',
//     'fields' => [
//       [
//         'title' => 'Pagespeed Score',
//         'value' => $pagespeedScore,
//       ],
//       [
//         'title' => 'YSlow Score',
//         'value' => $yslowScore,
//       ]
//     ]
//   ])->attach([
//     'fallback' => 'Report URL: ' . $reportUrl,
//     'text' => 'Report URL: ' . $reportUrl,
//     'color' => 'success',
//     'mrkdwn_in' => ['text']
//   ])->send('GTMetrix Report: ' . $_ENV['PANTHEON_SITE_NAME']);
// }

print("\n==== End GTMetrix Report ====\n");
