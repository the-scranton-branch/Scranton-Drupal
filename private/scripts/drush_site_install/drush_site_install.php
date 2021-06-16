<?php

// Enable Redis on site creation
if (isset($_POST['environment'])) {
  $req = pantheon_curl('https://api.live.getpantheon.com/sites/self/settings', NULL, 8443);
  $meta = json_decode($req['body'], true);

  // Enable Redis
  if ($meta['allow_cacheserver'] != 1) {
    $req = pantheon_curl('https://api.live.getpantheon.com/sites/self/settings', '{"allow_cacheserver":true}', 8443, 'PUT');
  }
}

// Get Pantheon site metadata
$req = pantheon_curl('https://api.live.getpantheon.com/sites/self/attributes', NULL, 8443);
$meta = json_decode($req['body'], true);
$title = $meta['label'];
$email = $_POST['user_email'];

// Install from profile.
echo "Installing default profile...\n";
passthru('drush site:install demo_umami --account-mail ' . $email . ' --site-name ' . $title . ' --account-name superuser -y');
echo "Import of configuration complete.\n";

// Clear all cache
echo "Rebuilding cache.\n";
passthru('drush cr');
echo "Rebuilding cache complete.\n";
