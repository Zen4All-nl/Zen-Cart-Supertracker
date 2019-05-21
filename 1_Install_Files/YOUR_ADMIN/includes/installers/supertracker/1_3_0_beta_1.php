<?php
  $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '1.3' WHERE configuration_key = 'SUPERTRACKER_MODULE_VERSION' LIMIT 1;");
  $db->Execute("ALTER TABLE" . TABLE_SUPER_TRACKER . " ADD `country_region` VARCHAR(100) NOT NULL DEFAULT '' AFTER `country_name`, ADD `country_city` VARCHAR(100) NOT NULL DEFAULT '' AFTER `country_region`;");