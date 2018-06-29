<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 circuits plugin for GLPI

 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of circuits.

 circuits is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 circuits is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with circuits. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * @return bool
 */
function plugin_circuits_install() {
   global $DB;

   include_once(GLPI_ROOT . "/plugins/circuits/inc/profile.class.php");

   $update = false;
   if (!$DB->tableExists("glpi_application")
       && !$DB->tableExists("glpi_plugin_appweb")
       && !$DB->tableExists("glpi_plugin_circuits_circuits")) {

      $DB->runFile(GLPI_ROOT . "/plugins/circuits/sql/empty-1.0.0.sql");

   } 

   PluginCircuitsProfile::initProfile();
   PluginCircuitsProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   $migration = new Migration("2.2.0");
   $migration->dropTable('glpi_plugin_circuits_profiles');

   return true;
}


/**
 * @return bool
 */
function plugin_circuits_uninstall() {
   global $DB;

   include_once(GLPI_ROOT . "/plugins/circuits/inc/profile.class.php");
   include_once(GLPI_ROOT . "/plugins/circuits/inc/menu.class.php");

   $tables = array("glpi_plugin_circuits_circuits",
                   "glpi_plugin_circuits_circuittypes",
                   "glpi_plugin_circuits_circuitservertypes",
                   "glpi_plugin_circuits_circuittechnics",
                   "glpi_plugin_circuits_circuits_items");

   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }

   $tables_glpi = array("glpi_displaypreferences",
                        "glpi_documents_items",
                        "glpi_savedsearches",
                        "glpi_logs",
                        "glpi_items_tickets",
                        "glpi_notepads",
                        "glpi_dropdowntranslations");

   foreach ($tables_glpi as $table_glpi) {
      $DB->query("DELETE
                  FROM `$table_glpi`
                  WHERE `itemtype` LIKE 'PluginCircuits%'");
   }

   if (class_exists('PluginDatainjectionModel')) {
      PluginDatainjectionModel::clean(array('itemtype' => 'PluginCircuitsCircuit'));
   }

   //Delete rights associated with the plugin
   $profileRight = new ProfileRight();
   foreach (PluginCircuitsProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(array('name' => $right['field']));
   }
   PluginCircuitsMenu::removeRightsFromSession();
   PluginCircuitsProfile::removeRightsFromSession();

   return true;
}


// Define dropdown relations
/**
 * @return array
 */
function plugin_circuits_getDatabaseRelations() {

   $plugin = new Plugin();

   if ($plugin->isActivated("circuits")) {
      return array("glpi_plugin_circuits_circuittypes"
                   => array("glpi_plugin_circuits_circuits"
                            => "plugin_circuits_circuittypes_id"),
                   "glpi_plugin_circuits_circuitservertypes"
                   => array("glpi_plugin_circuits_circuits"
                            => "plugin_circuits_circuitservertypes_id"),
                   "glpi_plugin_circuits_circuittechnics"
                   => array("glpi_plugin_circuits_circuits"
                            => "plugin_circuits_circuittechnics_id"),
                   "glpi_users"
                   => array("glpi_plugin_circuits_circuits" => "users_id_tech"),
                   "glpi_groups"
                   => array("glpi_plugin_circuits_circuits" => "groups_id_tech"),
                   "glpi_suppliers"
                   => array("glpi_plugin_circuits_circuits" => "suppliers_id"),
                   "glpi_manufacturers"
                   => array("glpi_plugin_circuits_circuits" => "manufacturers_id"),
                   "glpi_locations"
                   => array("glpi_plugin_circuits_circuits" => "locations_id"),
                   "glpi_plugin_circuits_circuits"
                   => array("glpi_plugin_circuits_circuits_items"
                            => "plugin_circuits_circuits_id"),
                   "glpi_entities"
                   => array("glpi_plugin_circuits_circuits"     => "entities_id",
                            "glpi_plugin_circuits_circuittypes" => "entities_id"));
   }
   return array();
}


// Define Dropdown tables to be manage in GLPI :
/**
 * @return array
 */
function plugin_circuits_getDropdown() {

   $plugin = new Plugin();

   if ($plugin->isActivated("circuits")) {
      return array('PluginCircuitsCircuitType'
                   => PluginCircuitsCircuitType::getTypeName(2),
                   'PluginCircuitsCircuitServerType'
                   => PluginCircuitsCircuitServerType::getTypeName(2),
                   'PluginCircuitsCircuitTechnic'
                   => PluginCircuitsCircuitTechnic::getTypeName(2));
   }
   return array();
}


/**
 * @param $types
 *
 * @return mixed
 */
function plugin_circuits_AssignToTicket($types) {

   if (Session::haveRight("plugin_circuits_open_ticket", "1")) {
      $types['PluginCircuitsCircuit'] = PluginCircuitsCircuit::getTypeName(2);
   }
   return $types;
}


////// SEARCH FUNCTIONS ///////() {

/**
 * @param $itemtype
 *
 * @return array
 */
function plugin_circuits_getAddSearchOptions($itemtype) {

   $sopt = array();

   if (in_array($itemtype, PluginCircuitsCircuit::getTypes(true))) {

      if (Session::haveRight("plugin_circuits", READ)) {
         $sopt[1310]['table']         = 'glpi_plugin_circuits_circuits';
         $sopt[1310]['field']         = 'name';
         $sopt[1310]['name']          = PluginCircuitsCircuit::getTypeName(2) . " - " .
                                        __('Name');
         $sopt[1310]['forcegroupby']  = true;
         $sopt[1310]['datatype']      = 'itemlink';
         $sopt[1310]['massiveaction'] = false;
         $sopt[1310]['itemlink_type'] = 'PluginCircuitsCircuit';
         $sopt[1310]['joinparams']    = array('beforejoin'
                                              => array('table'      => 'glpi_plugin_circuits_circuits_items',
                                                       'joinparams' => array('jointype' => 'itemtype_item')));

         $sopt[1311]['table']         = 'glpi_plugin_circuits_circuittypes';
         $sopt[1311]['field']         = 'name';
         $sopt[1311]['name']          = PluginCircuitsCircuit::getTypeName(2) . " - " .
                                        PluginCircuitsCircuitType::getTypeName(1);
         $sopt[1311]['forcegroupby']  = true;
         $sopt[1311]['datatype']      = 'dropdown';
         $sopt[1311]['massiveaction'] = false;
         $sopt[1311]['joinparams']    = array('beforejoin' => array(
            array('table'      => 'glpi_plugin_circuits_circuits',
                  'joinparams' => $sopt[1310]['joinparams'])));
      }
   }

   return $sopt;
}

//display custom fields in the search
/**
 * @param $type
 * @param $ID
 * @param $data
 * @param $num
 *
 * @return string
 */
function plugin_circuits_giveItem($type, $ID, $data, $num) {
   global $DB;

   $searchopt = &Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

   switch ($table . '.' . $field) {
      //display associated items with circuits
      case "glpi_plugin_circuits_circuits_items.items_id" :
         $query_device    = "SELECT DISTINCT `itemtype`
                              FROM `glpi_plugin_circuits_circuits_items`
                              WHERE `plugin_circuits_circuits_id` = '" . $data['id'] . "'
                              ORDER BY `itemtype`";
         $result_device   = $DB->query($query_device);
         $number_device   = $DB->numrows($result_device);
         $out             = '';
         $circuits = $data['id'];
         if ($number_device > 0) {
            for ($i = 0; $i < $number_device; $i++) {
               $column   = "name";
               $itemtype = $DB->result($result_device, $i, "itemtype");
               if (!class_exists($itemtype)) {
                  continue;
               }
               $item = new $itemtype();
               if ($item->canView()) {
                  $table_item = getTableForItemType($itemtype);

                  if ($itemtype != 'Entity') {
                     $query = "SELECT `" . $table_item . "`.*,
                                      `glpi_plugin_circuits_circuits_items`.`id` AS table_items_id,
                                      `glpi_entities`.`id` AS entity
                               FROM `glpi_plugin_circuits_circuits_items`,
                                    `" . $table_item . "`
                               LEFT JOIN `glpi_entities`
                                 ON (`glpi_entities`.`id` = `" . $table_item . "`.`entities_id`)
                               WHERE `" . $table_item . "`.`id` = `glpi_plugin_circuits_circuits_items`.`items_id`
                                     AND `glpi_plugin_circuits_circuits_items`.`itemtype` = '$itemtype'
                                     AND `glpi_plugin_circuits_circuits_items`.`plugin_circuits_circuits_id` = '" . $circuits . "' "
                              . getEntitiesRestrictRequest(" AND ", $table_item, '', '',
                                                           $item->maybeRecursive());

                     if ($item->maybeTemplate()) {
                        $query .= " AND " . $table_item . ".is_template = '0'";
                     }
                     $query .= " ORDER BY `glpi_entities`.`completename`,
                                          `" . $table_item . "`.`$column` ";

                  } else {
                     $query = "SELECT `" . $table_item . "`.*,
                                      `glpi_plugin_circuits_circuits_items`.`id` AS table_items_id,
                                      `glpi_entities`.`id` AS entity
                               FROM `glpi_plugin_circuits_circuits_items`, `" . $table_item . "`
                               WHERE `" . $table_item . "`.`id` = `glpi_plugin_circuits_circuits_items`.`items_id`
                                     AND `glpi_plugin_circuits_circuits_items`.`itemtype` = '$itemtype'
                                     AND `glpi_plugin_circuits_circuits_items`.`plugin_circuits_circuits_id` = '" . $circuits . "' "
                              . getEntitiesRestrictRequest(" AND ", $table_item, '', '',
                                                           $item->maybeRecursive());

                     if ($item->maybeTemplate()) {
                        $query .= " AND " . $table_item . ".is_template = '0'";
                     }
                     $query .= " ORDER BY `glpi_entities`.`completename`,
                                          `" . $table_item . "`.`$column` ";
                  }

                  if ($result_linked = $DB->query($query)) {
                     if ($DB->numrows($result_linked)) {
                        $item = new $itemtype();
                        while ($datal = $DB->fetch_assoc($result_linked)) {
                           if ($item->getFromDB($datal['id'])) {
                              $out .= $item->getTypeName() . " - " . $item->getLink() . "<br>";
                           }
                        }
                     } else {
                        $out .= ' ';
                     }
                  } else {
                     $out .= ' ';
                  }
               } else {
                  $out .= ' ';
               }
            }
         }
         return $out;
   }
   return "";
}


////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

/**
 * @param $type
 *
 * @return array
 */
function plugin_circuits_MassiveActions($type) {

   if (in_array($type, PluginCircuitsCircuit::getTypes(true))) {
      return array('PluginCircuitsCircuit' . MassiveAction::CLASS_ACTION_SEPARATOR . 'plugin_circuits_add_item' =>
                      __('Associate a TC circuit', 'circuits'));
   }
   return array();
}

function plugin_circuits_postinit() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['item_purge']['circuits'] = array();

   foreach (PluginCircuitsCircuit::getTypes(true) as $type) {

      $PLUGIN_HOOKS['item_purge']['circuits'][$type]
         = array('PluginCircuitsCircuit_Item', 'cleanForItem');

      CommonGLPI::registerStandardTab($type, 'PluginCircuitsCircuit_Item');
   }
}

function plugin_datainjection_populate_circuits() {
   global $INJECTABLE_TYPES;

   $INJECTABLE_TYPES['PluginCircuitsCircuitInjection'] = 'circuits';
}