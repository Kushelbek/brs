CREATE TABLE IF NOT EXISTS `cot_banners` (
  `id` INTEGER NOT NULL auto_increment,
  `type` INTEGER DEFAULT '0',
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `category` VARCHAR(255) NOT NULL DEFAULT '',
  `file` VARCHAR(255) DEFAULT '',
  `width` INTEGER DEFAULT '0',
  `height` INTEGER DEFAULT '0',
  `alt` VARCHAR(255) DEFAULT '',
  `customcode` TEXT DEFAULT '',
  `clickurl` VARCHAR(200) DEFAULT '',
  `description` TEXT DEFAULT '',
  `published` TINYINT(1) UNSIGNED DEFAULT 0,
  `sticky` TINYINT(1) UNSIGNED DEFAULT 0,
  `publish_up` DATETIME DEFAULT '1970-01-01 00:00:00',
  `publish_down` DATETIME DEFAULT '1970-01-01 00:00:00',
  `imptotal` INTEGER DEFAULT '0',
  `impressions` INTEGER DEFAULT '0',
  `lastimp` double DEFAULT '0',
  `clicks` INTEGER DEFAULT '0',
  `client` INTEGER DEFAULT '0',
  `track_clicks` TINYINT DEFAULT '-1',
  `track_impressions` TINYINT DEFAULT '-1',
  `purchase_type` TINYINT DEFAULT '-1',
  `sort` INTEGER DEFAULT 0,
  `created` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT '0',
  `updated` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `updated_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  INDEX `idx_published` (`published`),
  INDEX `idx_banner_cat`(`category`)
) ENGINE = MYISAM COMMENT = 'Banners';

CREATE TABLE IF NOT EXISTS `cot_banner_clients` (
  `id` INTEGER NOT NULL auto_increment,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `email` VARCHAR(255) DEFAULT '',
  `extrainfo` TEXT,
  `published` TINYINT(1) UNSIGNED DEFAULT 0,
  `purchase_type` TINYINT NOT NULL DEFAULT '-1',
  `track_clicks` TINYINT NOT NULL DEFAULT '-1',
  `track_impressions` TINYINT NOT NULL DEFAULT '-1',
  PRIMARY KEY  (`id`)
)  ENGINE = MYISAM ;

CREATE TABLE IF NOT EXISTS `cot_banner_tracks` (
  `date` DATETIME NOT NULL,
  `type` INTEGER UNSIGNED NOT NULL,
  `banner` INTEGER UNSIGNED NOT NULL,
  `track_count` INTEGER UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`date`, `type`, `banner`),
  INDEX `idx_track_date` (`date`),
  INDEX `idx_track_type` (`type`),
  INDEX `idx_banner_id` (`banner`)
)  ENGINE = MYISAM ;

-- Default banners categories
-- INSERT INTO `cot_structure` (`structure_area`, `structure_code`, `structure_path`, `structure_tpl`, `structure_title`,
--                             `structure_desc`, `structure_icon`, `structure_locked`, `structure_count`) VALUES
-- ('banners', 'sample', '1', '', 'Sample', 'Sample category', '', 0, 0);
