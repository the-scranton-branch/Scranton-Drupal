<?php
// No need to log this script operation in New Relic's stats.
// PROTIP: you might also want to use this snippet if you have PHP code handling
// very fast things like redirects or the like.
if (extension_loaded('newrelic')) {
  newrelic_ignore_transaction();
}

// Fetch metadata from Pantheon's internal API.
$req = pantheon_curl('https://api.live.getpantheon.com/sites/self/environments/self/bindings?type=newrelic', null, 8443);
$meta = json_decode($req['body'], true);

$nr = false;
if (!empty($meta) && $meta['api_key']) {
  $data = $meta;
  $req = newrelic_request($url, $data);
}
// Fail fast if we're not going to be able to call New Relic.
if ($nr == false) {
  echo "\n\nALERT! No New Relic metadata could be found.\n\n";
  exit();
}

// This is one example that handles code pushes, dashboard 
// commits, and deploys between environments. To make sure we 
// have good deploy markers, we gather data differently depending
// on the context.

if ($_POST['wf_type'] == 'sync_code') {
  // commit 'subject'
  $description = trim(`git log --pretty=format:"%s" -1`);
  $revision = trim(`git log --pretty=format:"%h" -1`);
  if ($_POST['user_role'] == 'super') {
    // This indicates an in-dashboard SFTP commit.
    $user = trim(`git log --pretty=format:"%ae" -1`);
    $changelog = trim(`git log --pretty=format:"%b" -1`);
    $changelog .= "\n\n" . '(Commit made via Pantheon dashboard.)';
  } else {
    $user = $_POST['user_email'];
    $changelog = trim(`git log --pretty=format:"%b" -1`);
    $changelog .= "\n\n" . '(Triggered by remote git push.)';
  }
} elseif ($_POST['wf_type'] == 'deploy') {
  // Topline description:
  $description = 'Deploy to environment triggered via Pantheon';
  // Find out if there's a deploy tag:
  $revision = `git describe --tags --abbrev=0`;
  // Get the annotation:
  $changelog = `git tag -l -n99 $revision`;
  $user = $_POST['user_email'];
}

// Use New Relic's v2 API.
$url = "https://api.newrelic.com/v2/applications/{$application_id}/deployments.json";
$data = [
  'deployment' => [
    "revision" => $revision,
    "changelog" => $changelog,
    "description" => 'Deploy to environment triggered via Pantheon',
    "user" => $user
  ]
];

echo "Logging deployment in New Relic...\n";
$req = newrelic_request($url, $data);
echo "Done!";

// The below can be helpful debugging.
// echo "\n\nRequesting... \n\n";
// echo "\n\n$url\n\n";
// print_r($data);
// echo "\n\n";
// print_r($req);

/**
 * Send an API request to New Relic.
 *
 * @param string $url
 * @param array $data
 * @param string $method
 * @return void
 */
function newrelic_request($url, $data = [], $method = 'GET')
{
  $content = http_build_query($data, '', '&');
  // Create a stream
  $opts = [
    'http' => [
      'method' => $method,
      'header' => "x-api-key: {$meta['api_key']}",
      'content' => $content
    ]
  ];
  $context = stream_context_create($opts);
  return file_get_contents($url, false, $context);
}
