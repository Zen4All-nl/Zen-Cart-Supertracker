<?php
  $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '1.2' WHERE configuration_key = 'SUPERTRACKER_MODULE_VERSION' LIMIT 1;");