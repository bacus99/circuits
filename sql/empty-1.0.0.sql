DROP TABLE IF EXISTS `glpi_plugin_circuits_circuits`;
CREATE TABLE `glpi_plugin_circuits_circuits` (
  `id`                                                  INT(11)    NOT NULL     AUTO_INCREMENT,
  `entities_id`                                         INT(11)    NOT NULL     DEFAULT '0',
  `is_recursive`                                        TINYINT(1) NOT NULL     DEFAULT '0',
  `name`                                                VARCHAR(255)
                                                        COLLATE utf8_unicode_ci DEFAULT NULL,
  `address`                                             VARCHAR(255)
                                                        COLLATE utf8_unicode_ci DEFAULT NULL,
  `backoffice`                                          VARCHAR(255)
                                                        COLLATE utf8_unicode_ci DEFAULT NULL,
  `plugin_circuits_circuittypes_id`       INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_plugin_circuits_circuittypes (id)',
  `plugin_circuits_circuitservertypes_id` INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_plugin_circuits_circuitservertypes (id)',
  `plugin_circuits_circuittechnics_id`    INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_plugin_circuits_circuittechnics (id)',
  `version`                                             VARCHAR(255)
                                                        COLLATE utf8_unicode_ci DEFAULT NULL,
  `users_id_tech`                                       INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_users (id)',
  `groups_id_tech`                                      INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_groups (id)',
  `suppliers_id`                                        INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_suppliers (id)',
  `manufacturers_id`                                    INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_manufacturers (id)',
  `locations_id`                                        INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_locations (id)',
  `date_mod`                                            DATETIME                DEFAULT NULL,
  `is_helpdesk_visible`                                 INT(11)    NOT NULL     DEFAULT '1',
  `comment`                                             TEXT COLLATE utf8_unicode_ci,
  `is_deleted`                                          TINYINT(1) NOT NULL     DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `entities_id` (`entities_id`),
  KEY `plugin_circuits_circuittypes_id` (`plugin_circuits_circuittypes_id`),
  KEY `plugin_circuits_circuitservertypes_id` (`plugin_circuits_circuitservertypes_id`),
  KEY `plugin_circuits_circuittechnics_id` (`plugin_circuits_circuittechnics_id`),
  KEY `users_id_tech` (`users_id_tech`),
  KEY `groups_id_tech` (`groups_id_tech`),
  KEY `suppliers_id` (`suppliers_id`),
  KEY `manufacturers_id` (`manufacturers_id`),
  KEY `locations_id` (`locations_id`),
  KEY date_mod (date_mod),
  KEY is_helpdesk_visible (is_helpdesk_visible),
  KEY `is_deleted` (`is_deleted`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_circuits_circuittypes`;
CREATE TABLE `glpi_plugin_circuits_circuittypes` (
  `id`          INT(11) NOT NULL        AUTO_INCREMENT,
  `entities_id` INT(11) NOT NULL        DEFAULT '0',
  `name`        VARCHAR(255)
                COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment`     TEXT COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `entities_id` (`entities_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_circuits_circuitservertypes`;
CREATE TABLE `glpi_plugin_circuits_circuitservertypes` (
  `id`      INT(11) NOT NULL        AUTO_INCREMENT,
  `name`    VARCHAR(255)
            COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` TEXT COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

INSERT INTO `glpi_plugin_circuits_circuitservertypes` VALUES ('1', 'Apache', '');
INSERT INTO `glpi_plugin_circuits_circuitservertypes` VALUES ('2', 'IIS', '');
INSERT INTO `glpi_plugin_circuits_circuitservertypes` VALUES ('3', 'Tomcat', '');

DROP TABLE IF EXISTS `glpi_plugin_circuits_circuittechnics`;
CREATE TABLE `glpi_plugin_circuits_circuittechnics` (
  `id`      INT(11) NOT NULL        AUTO_INCREMENT,
  `name`    VARCHAR(255)
            COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` TEXT COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

INSERT INTO `glpi_plugin_circuits_circuittechnics` VALUES ('1', 'Asp', '');
INSERT INTO `glpi_plugin_circuits_circuittechnics` VALUES ('2', 'Cgi', '');
INSERT INTO `glpi_plugin_circuits_circuittechnics` VALUES ('3', 'Java', '');
INSERT INTO `glpi_plugin_circuits_circuittechnics` VALUES ('4', 'Perl', '');
INSERT INTO `glpi_plugin_circuits_circuittechnics` VALUES ('5', 'Php', '');
INSERT INTO `glpi_plugin_circuits_circuittechnics` VALUES ('6', '.Net', '');

DROP TABLE IF EXISTS `glpi_plugin_circuits_circuits_items`;
CREATE TABLE `glpi_plugin_circuits_circuits_items` (
  `id`                                        INT(11)                 NOT NULL AUTO_INCREMENT,
  `plugin_circuits_circuits_id` INT(11)                 NOT NULL DEFAULT '0'
  COMMENT 'RELATION to glpi_plugin_circuits_circuits (id)',
  `items_id`                                  INT(11)                 NOT NULL DEFAULT '0'
  COMMENT 'RELATION to various tables, according to itemtype (id)',
  `itemtype`                                  VARCHAR(100)
                                              COLLATE utf8_unicode_ci NOT NULL
  COMMENT 'see .class.php file',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`plugin_circuits_circuits_id`, `items_id`, `itemtype`),
  KEY `FK_device` (`items_id`, `itemtype`),
  KEY `item` (`itemtype`, `items_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

INSERT INTO `glpi_displaypreferences` VALUES (NULL, 'PluginCircuitsCircuit', '2', '2', '0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL, 'PluginCircuitsCircuit', '3', '4', '0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL, 'PluginCircuitsCircuit', '6', '5', '0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL, 'PluginCircuitsCircuit', '7', '6', '0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL, 'PluginCircuitsCircuit', '8', '7', '0');