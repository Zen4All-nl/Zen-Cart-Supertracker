SET @configuration_group_id=0;
SELECT @configuration_group_id:=configuration_group_id
FROM configuration_group
WHERE configuration_group_title= 'Super Tracker'
LIMIT 1;
DELETE FROM configuration WHERE configuration_group_id = @configuration_group_id;
DELETE FROM configuration_group WHERE configuration_group_id = @configuration_group_id;

DROP TABLE IF EXISTS `supertracker`;

/*Zen Cart v1.5.0+ only Below! Skip if using an older version!*/
DELETE FROM admin_pages WHERE page_key = 'configSuperTracker' LIMIT 1;
DELETE FROM admin_pages WHERE page_key = 'reportsSuperTracker' LIMIT 1;