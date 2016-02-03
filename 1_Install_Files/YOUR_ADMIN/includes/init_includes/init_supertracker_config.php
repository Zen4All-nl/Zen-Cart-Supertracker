<?php
  if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
  }
  $zc150 = (PROJECT_VERSION_MAJOR > 1 || (PROJECT_VERSION_MAJOR == 1 && substr(PROJECT_VERSION_MINOR, 0, 3) >= 5));
  // add upgrade script
  if (defined('SUPERTRACKER_MODULE_VERSION')) { // does not exist prior to v1.1
    $gm_version = SUPERTRACKER_MODULE_VERSION;
    while ($st_version != '1.1') {
      switch($st_version) {
        default:
          $st_version = '1.1';
          // break all the loops
          break 2;      
      }
    }
  } else {
    // begin update to version 1.1
    // do a new install
    if (file_exists(DIR_WS_INCLUDES . 'installers/supertracker/new_install.php')) {
      include_once(DIR_WS_INCLUDES . 'installers/supertracker/new_install.php');
      $messageStack->add('Added Supertracker Configuration', 'success');
    } else {
      $messageStack->add('New installation file missing, please make sure you have uploaded all files in the package.', 'error');
    }
  }
  
  if ($zc150) { // continue Zen Cart 1.5.0
    // add configuration menu
    if (!zen_page_key_exists('configSuperTracker')) {
      $configuration = $db->Execute("SELECT configuration_group_id FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'SUPERTRACKER_MODULE_VERSION' LIMIT 1;");
      $configuration_group_id = $configuration->fields['configuration_group_id'];
      if ((int)$configuration_group_id > 0) {
        zen_register_admin_page('configSuperTracker',
                                'BOX_CONFIGURATION_SUPERTRACKER', 
                                'FILENAME_CONFIGURATION',
                                'gID=' . $configuration_group_id, 
                                'configuration', 
                                'Y',
                                $configuration_group_id);
        zen_register_admin_page('reportsSuperTracker',
                                'BOX_REPORTS_SUPERTRACKER',
                                'FILENAME_SUPERTRACKER',
                                '',
                                'reports',
                                'Y',
                                $configuration_group_id);
          
        $messageStack->add('Enabled Supertracker Configuration menu.', 'success');
      }
    }
  }