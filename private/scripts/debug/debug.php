<?php

/**
 * Debug Quicksilver variables.
 * 
 * $_POST contains data you can use within the Quicksilver process.
 * Other environmental variables can be pulled from $_ENV, such as site ID.
 */

echo "Quicksilver Debugging Output";
echo "\n\n";
echo "\n========= START PAYLOAD ===========\n";
print_r($_POST);
echo "\n========== END PAYLOAD ============\n";


echo "\n------- START ENVIRONMENT ---------\n";
$env = $_ENV;

// Remove sensitive information.
foreach ($env as $key => $value) {
  if (preg_match('#(PASSWORD|SALT|AUTH|SECURE|NONCE|LOGGED_IN)#', $key)) {
    $env[$key] = '[REDACTED]';
  }
}

print_r($env);
echo "\n TESTING SERVER \n";
print_r($_SERVER);

echo "\n-------- END ENVIRONMENT ----------\n";

// Run CLI status command based on Framework
switch ($_ENV['FRAMEWORK']) {
  case 'wordpress':
  case 'wordpress_network':
    echo "\n TESTING WP CLI \n";
    passthru('wp cli info');
    echo "\n END TESTING WP CLI \n";
    break;

  case 'drupal':
  case 'drupal8':
    echo "\n TESTING DRUSH \n";
    passthru('drush status');
    echo "\n END TESTING DRUSH \n";
    break;
}
