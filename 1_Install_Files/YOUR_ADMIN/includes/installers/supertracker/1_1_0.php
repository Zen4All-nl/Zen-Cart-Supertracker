<?php
$configuration = $db->Execute("SELECT configuration_group_id FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_title = 'Super Tracker' OR configuration_group_title = 'Supertracker' ORDER BY configuration_group_id ASC;");
if ($configuration->RecordCount() > 0) {
  while (!$configuration->EOF) {
    $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_group_id = " . $configuration->fields['configuration_group_id'] . ";");
    $db->Execute("DELETE FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_id = " . $configuration->fields['configuration_group_id'] . ";");
    $configuration->MoveNext();
  }
}
#$db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_group_id = 0;");
$db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = '';");

$db->Execute("INSERT INTO " . TABLE_CONFIGURATION_GROUP . " (configuration_group_title, configuration_group_description, sort_order, visible) VALUES ('Super Tracker', 'Super Tracker Configuration', '1', '1');");
$configuration_group_id = $db->Insert_ID();

$db->Execute("UPDATE " . TABLE_CONFIGURATION_GROUP . " SET sort_order = " . $configuration_group_id . " WHERE configuration_group_id = " . $configuration_group_id . ";");

$db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES
  ('Module version', 'SUPERTRACKER_MODULE_VERSION', '1.1', 'Updated by Zen4All, original mod by Andrew Berezin', " . $configuration_group_id . ", 10, NOW(), NOW(), NULL, 'trim('),
  ('Excluding IP\'s', 'XTRACKING_EXCLUDED_IPS', '127.0.0.1', 'Comma Separate List of IPs which should not be recorded, for instance, your own PCs IP address, or that of your server if you are using Cron Jobs, etc', " . $configuration_group_id . ", 1, NOW(), NOW(), NULL, NULL),
  ('Excluding UserAgent\'s', 'XTRACKING_EXCLUDED_UA', 'ServiceUptime.robot', 'Comma Separate List of UserAgent\'s substring which should not be recorded', " . $configuration_group_id . ", 2, NOW(), NOW(), NULL, NULL),
  ('Excluding bot\'s', 'XTRACKING_EXCLUDE_BOTS', 'true', 'IP\'s Excluding from logging', " . $configuration_group_id . ", 3, NOW(), NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');");


$db->Execute("CREATE TABLE IF NOT EXISTS " . TABLE_SUPERTRACKER . " (
  tracking_id bigint(32) NOT NULL AUTO_INCREMENT,
  ip_address varchar(15) NOT NULL DEFAULT '',
  browser_string varchar(255) NOT NULL DEFAULT '',
  country_code char(2) NOT NULL DEFAULT '',
  country_name varchar(100) NOT NULL DEFAULT '',
  country_region varchar(100) NOT NULL DEFAULT '',
  country_city varchar(100) NOT NULL DEFAULT '',
  customer_id int(11) NOT NULL DEFAULT '0',
  order_id int(11) NOT NULL DEFAULT '0',
  referrer varchar(255) NOT NULL DEFAULT '',
  referrer_query_string varchar(255) NOT NULL DEFAULT '',
  landing_page varchar(255) NOT NULL DEFAULT '',
  landing_page_name varchar(255) NOT NULL,
  exit_page varchar(255) DEFAULT NULL,
  exit_page_name varchar(255) NOT NULL,
  time_arrived datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  last_click datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  num_clicks int(11) NOT NULL DEFAULT '1',
  added_cart varchar(5) NOT NULL DEFAULT 'false',
  completed_purchase varchar(5) NOT NULL DEFAULT 'false',
  categories_viewed varchar(255) NOT NULL DEFAULT '',
  products_viewed varchar(255) NOT NULL DEFAULT '',
  cart_contents mediumtext NOT NULL,
  cart_total int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (tracking_id),
  KEY ip_address (ip_address),
  KEY last_click (last_click),
  KEY customer_id (customer_id),
  KEY browser_string (browser_string),
  KEY cart_total (cart_total)
  )");