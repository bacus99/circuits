<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 circuits plugin for GLPI
 Copyright (C) 2009-2016 by the circuits Development Team.

 https://github.com/InfotelGLPI/circuits
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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginCircuitsCircuit_Item
 */
class PluginCircuitsCircuit_Item extends CommonDBRelation {

   // From CommonDBRelation
   static public $itemtype_1    = 'PluginCircuitsCircuit';
   static public $items_id_1    = 'plugin_circuits_circuits_id';
   static public $take_entity_1 = false;

   static public $itemtype_2    = 'itemtype';
   static public $items_id_2    = 'items_id';
   static public $take_entity_2 = true;

   static $rightname = "plugin_circuits";

   /**
    * @param CommonDBTM $item
    */
   static function cleanForItem(CommonDBTM $item) {

      $temp = new self();
      $temp->deleteByCriteria(
         array('itemtype' => $item->getType(),
               'items_id' => $item->getField('id'))
      );
   }

   /**
    * @param CommonGLPI $item
    * @param int        $withtemplate
    *
    * @return array|string|translated
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$withtemplate) {
         if ($item->getType() == 'PluginCircuitsCircuit'
             && count(PluginCircuitsCircuit::getTypes(false))) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(_n('Associated item', 'Associated items', 2), self::countForCircuit($item));
            }
            return _n('Associated item', 'Associated items', 2);

         } else if (in_array($item->getType(), PluginCircuitsCircuit::getTypes(true))
                    && Session::haveRight("plugin_circuits", READ)) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(PluginCircuitsCircuit::getTypeName(2), self::countForItem($item));
            }
            return PluginCircuitsCircuit::getTypeName(2);
         }
      }
      return '';
   }


   /**
    * @param CommonGLPI $item
    * @param int        $tabnum
    * @param int        $withtemplate
    *
    * @return bool
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == 'PluginCircuitsCircuit') {

         self::showForCircuit($item);

      } else if (in_array($item->getType(), PluginCircuitsCircuit::getTypes(true))) {

         self::showForItem($item);

      }
      return true;
   }

   /**
    * @param PluginCircuitsCircuit $item
    *
    * @return int
    */
   static function countForCircuit(PluginCircuitsCircuit $item) {
      $dbu   = new DbUtils();
      $types = implode("','", $item->getTypes());
      if (empty($types)) {
         return 0;
      }
      return $dbu->countElementsInTable('glpi_plugin_circuits_circuits_items',
                                        "`itemtype` IN ('$types')
                                         AND `plugin_circuits_circuits_id` = '" . $item->getID() . "'");
   }


   /**
    * @param CommonDBTM $item
    *
    * @return int
    */
   static function countForItem(CommonDBTM $item) {
      $dbu = new DbUtils();
      return $dbu->countElementsInTable('glpi_plugin_circuits_circuits_items',
                                        "`itemtype`='" . $item->getType() . "'
                                          AND `items_id` = '" . $item->getID() . "'");
   }

   /**
    * @param $plugin_circuits_circuits_id
    * @param $items_id
    * @param $itemtype
    *
    * @return bool
    */
   function getFromDBbyCircuitsAndItem($plugin_circuits_circuits_id,
                                              $items_id, $itemtype) {
      global $DB;

      $query = "SELECT *
                FROM `" . $this->getTable() . "`
                WHERE `plugin_circuits_circuits_id`
                           = '" . $plugin_circuits_circuits_id . "'
                      AND `itemtype` = '" . $itemtype . "'
                      AND `items_id` = '" . $items_id . "'";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         }
      }
      return false;
   }

   /**
    * @param $values
    */
   function addItem($values) {

      $this->add(array('plugin_circuits_circuits_id'
                                  => $values["plugin_circuits_circuits_id"],
                       'items_id' => $values["items_id"],
                       'itemtype' => $values["itemtype"]));

   }


   /**
    * @param $plugin_circuits_circuits_id
    * @param $items_id
    * @param $itemtype
    */
   function deleteItemByCircuitsAndItem($plugin_circuits_circuits_id,
                                               $items_id, $itemtype) {

      if ($this->getFromDBbyCircuitsAndItem($plugin_circuits_circuits_id,
                                                   $items_id, $itemtype)) {
         $this->delete(array('id' => $this->fields["id"]));
      }
   }


   /**
    * Show items links to a circuit
    *
    * @since version 0.84
    *
    * @param PluginCircuitsCircuit $circuit
    *
    * @return nothing
    * @internal param PluginCircuitsCircuit $PluginCircuitsCircuit object
    *
    */
   static function showForCircuit(PluginCircuitsCircuit $circuit) {
      global $DB;

      $instID = $circuit->fields['id'];
      if (!$circuit->can($instID, READ)) {
         return false;
      }
      $canedit = $circuit->can($instID, UPDATE);

      $query = "SELECT DISTINCT `itemtype`
         FROM `glpi_plugin_circuits_circuits_items`
         WHERE `plugin_circuits_circuits_id` = '$instID'
         ORDER BY `itemtype` ";

      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $rand   = mt_rand();

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='circuititem_form$rand' id='circuititem_form$rand' method='post'
               action='" . Toolbox::getItemTypeFormURL("PluginCircuitsCircuit") . "'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='2'>" . __('Add an item') . "</th></tr>";

         echo "<tr class='tab_bg_1'><td class='right'>";
         Dropdown::showSelectItemFromItemtypes(array('items_id_name' => 'items_id',
                                                     'itemtypes'     => PluginCircuitsCircuit::getTypes(true),
                                                     'entity_restrict'
                                                                     => ($circuit->fields['is_recursive']
                                                        ? getSonsOf('glpi_entities',
                                                                    $circuit->fields['entities_id'])
                                                        : $circuit->fields['entities_id']),
                                                     'checkright'
                                                                     => true,
                                               ));
         echo "</td><td class='center'>";
         echo "<input type='submit' name='additem' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
         echo "<input type='hidden' name='plugin_circuits_circuits_id' value='$instID'>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }

      echo "<div class='spaced'>";
      if ($canedit && $number) {
         Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
         $massiveactionparams = array();
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";

      if ($canedit && $number) {
         echo "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
      }

      echo "<th>" . __('Type') . "</th>";
      echo "<th>" . __('Name') . "</th>";
      echo "<th>" . __('Entity') . "</th>";
      echo "<th>" . __('Serial number') . "</th>";
      echo "<th>" . __('Inventory number') . "</th>";
      echo "</tr>";

      for ($i = 0; $i < $number; $i++) {
         $itemtype = $DB->result($result, $i, "itemtype");
         if (!($item = getItemForItemtype($itemtype))) {
            continue;
         }

         if ($item->canView()) {
            $column = "name";
            if ($itemtype == 'Ticket') {
               $column = "id";
            }

            $itemtable = getTableForItemType($itemtype);
            $query     = "SELECT `$itemtable`.*,
                                 `glpi_plugin_circuits_circuits_items`.`id` AS IDD, ";

            if ($itemtype == 'KnowbaseItem') {
               $query .= "-1 AS entity
                          FROM `glpi_plugin_circuits_circuits_items`, `$itemtable`
                          " . KnowbaseItem::addVisibilityJoins() . "
                          WHERE `$itemtable`.`id` = `glpi_plugin_circuits_circuits_items`.`items_id`
                                AND ";
            } else {
               $query .= "`glpi_entities`.`id` AS entity
                          FROM `glpi_plugin_circuits_circuits_items`, `$itemtable` ";

               if ($itemtype != 'Entity') {
                  $query .= "LEFT JOIN `glpi_entities`
                              ON (`glpi_entities`.`id` = `$itemtable`.`entities_id`) ";
               }
               $query .= "WHERE `$itemtable`.`id` = `glpi_plugin_circuits_circuits_items`.`items_id`
                                AND ";
            }
            $query .= "`glpi_plugin_circuits_circuits_items`.`itemtype` = '$itemtype'
                       AND `glpi_plugin_circuits_circuits_items`.`plugin_circuits_circuits_id` = '$instID' ";

            if ($itemtype == 'KnowbaseItem') {
               if (Session::getLoginUserID()) {
                  $where = "AND " . KnowbaseItem::addVisibilityRestrict();
               } else {
                  // Anonymous access
                  if (Session::isMultiEntitiesMode()) {
                     $where = " AND (`glpi_entities_knowbaseitems`.`entities_id` = '0'
                                     AND `glpi_entities_knowbaseitems`.`is_recursive` = '1')";
                  }
               }
            } else {
               $query .= getEntitiesRestrictRequest(" AND ", $itemtable, '', '',
                                                    $item->maybeRecursive());
            }

            if ($item->maybeTemplate()) {
               $query .= " AND `$itemtable`.`is_template` = '0'";
            }

            if ($itemtype == 'KnowbaseItem') {
               $query .= " ORDER BY `$itemtable`.`$column`";
            } else {
               $query .= " ORDER BY `glpi_entities`.`completename`, `$itemtable`.`$column`";
            }

            if ($itemtype == 'SoftwareLicense') {
               $soft = new Software();
            }

            if ($result_linked = $DB->query($query)) {
               if ($DB->numrows($result_linked)) {

                  while ($data = $DB->fetch_assoc($result_linked)) {

                     if ($itemtype == 'Ticket') {
                        $data["name"] = sprintf(__('%1$s: %2$s'), __('Ticket'), $data["id"]);
                     }

                     if ($itemtype == 'SoftwareLicense') {
                        $soft->getFromDB($data['softwares_id']);
                        $data["name"] = sprintf(__('%1$s - %2$s'), $data["name"],
                                                $soft->fields['name']);
                     }
                     $linkname = $data["name"];
                     if ($_SESSION["glpiis_ids_visible"]
                         || empty($data["name"])) {
                        $linkname = sprintf(__('%1$s (%2$s)'), $linkname, $data["id"]);
                     }

                     $link = Toolbox::getItemTypeFormURL($itemtype);
                     $name = "<a href=\"" . $link . "?id=" . $data["id"] . "\">" . $linkname . "</a>";

                     echo "<tr class='tab_bg_1'>";

                     if ($canedit) {
                        echo "<td width='10'>";
                        Html::showMassiveActionCheckBox(__CLASS__, $data["IDD"]);
                        echo "</td>";
                     }
                     echo "<td class='center'>" . $item->getTypeName(1) . "</td>";
                     echo "<td " .
                          (isset($data['is_deleted']) && $data['is_deleted'] ? "class='tab_bg_2_2'" : "") .
                          ">" . $name . "</td>";
                     echo "<td class='center'>" . Dropdown::getDropdownName("glpi_entities",
                                                                            $data['entity']);
                     echo "</td>";
                     echo "<td class='center'>" .
                          (isset($data["serial"]) ? "" . $data["serial"] . "" : "-") . "</td>";
                     echo "<td class='center'>" .
                          (isset($data["otherserial"]) ? "" . $data["otherserial"] . "" : "-") . "</td>";
                     echo "</tr>";
                  }
               }
            }
         }
      }
      echo "</table>";
      if ($canedit && $number) {
         $paramsma['ontop'] = false;
         Html::showMassiveActions($paramsma);
         Html::closeForm();
      }
      echo "</div>";

   }

   /**
    * Show circuits associated to an item
    *
    * @since version 0.84
    *
    * @param $item            CommonDBTM object for which associated circuits must be displayed
    * @param $withtemplate (default '')
    *
    * @return bool
    */
   static function showForItem(CommonDBTM $item, $withtemplate = '') {
      global $DB;

      $ID = $item->getField('id');

      if ($item->isNewID($ID)) {
         return false;
      }
      if (!Session::haveRight("plugin_circuits", READ)) {
         return false;
      }

      if (!$item->can($item->fields['id'], READ)) {
         return false;
      }

      if (empty($withtemplate)) {
         $withtemplate = 0;
      }

      $canedit      = $item->canAddItem('PluginCircuitsCircuit');
      $rand         = mt_rand();
      $is_recursive = $item->isRecursive();

      $query = "SELECT `glpi_plugin_circuits_circuits_items`.`id` AS assocID,
                       `glpi_entities`.`id` AS entity,
                       `glpi_plugin_circuits_circuits`.`name` AS assocName,
                       `glpi_plugin_circuits_circuits`.*
                FROM `glpi_plugin_circuits_circuits_items`
                LEFT JOIN `glpi_plugin_circuits_circuits`
                 ON (`glpi_plugin_circuits_circuits_items`.`plugin_circuits_circuits_id`=`glpi_plugin_circuits_circuits`.`id`)
                LEFT JOIN `glpi_entities` ON (`glpi_plugin_circuits_circuits`.`entities_id`=`glpi_entities`.`id`)
                WHERE `glpi_plugin_circuits_circuits_items`.`items_id` = '$ID'
                      AND `glpi_plugin_circuits_circuits_items`.`itemtype` = '" . $item->getType() . "' ";

      $query .= getEntitiesRestrictRequest(" AND", "glpi_plugin_circuits_circuits", '', '', true);

      $query .= " ORDER BY `assocName`";

      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $i      = 0;

      $webs = array();
      $web  = new PluginCircuitsCircuit();
      $used = array();
      if ($numrows = $DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $webs[$data['assocID']] = $data;
            $used[$data['id']]      = $data['id'];
         }
      }

      if ($canedit && $withtemplate < 2) {
         // Restrict entity for knowbase
         $entities = "";
         $entity   = $_SESSION["glpiactive_entity"];

         if ($item->isEntityAssign()) {
            /// Case of personal items : entity = -1 : create on active entity (Reminder case))
            if ($item->getEntityID() >= 0) {
               $entity = $item->getEntityID();
            }

            if ($item->isRecursive()) {
               $entities = getSonsOf('glpi_entities', $entity);
            } else {
               $entities = $entity;
            }
         }
         $limit = getEntitiesRestrictRequest(" AND ", "glpi_plugin_circuits_circuits", '', $entities, true);
         $q     = "SELECT COUNT(*)
               FROM `glpi_plugin_circuits_circuits`
               WHERE `is_deleted` = '0'
               $limit";

         $result = $DB->query($q);
         $nb     = $DB->result($result, 0, 0);

         echo "<div class='firstbloc'>";


         if (Session::haveRight("plugin_circuits", READ)
             && ($nb > count($used))) {
            echo "<form name='circuit_form$rand' id='circuit_form$rand' method='post'
                   action='" . Toolbox::getItemTypeFormURL('PluginCircuitsCircuit') . "'>";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='4' class='center'>";
            echo "<input type='hidden' name='entities_id' value='$entity'>";
            echo "<input type='hidden' name='is_recursive' value='$is_recursive'>";
            echo "<input type='hidden' name='itemtype' value='" . $item->getType() . "'>";
            echo "<input type='hidden' name='items_id' value='$ID'>";
            if ($item->getType() == 'Ticket') {
               echo "<input type='hidden' name='tickets_id' value='$ID'>";
            }

            PluginCircuitsCircuit::dropdownCircuit(array('entity' => $entities,
                                                                              'used'   => $used));
            echo "</td><td class='center' width='20%'>";
            echo "<input type='submit' name='additem' value=\"" .
                 __s('Associate a TC circuit', 'circuits') . "\" class='submit'>";
            echo "</td>";
            echo "</tr>";
            echo "</table>";
            Html::closeForm();
         }

         echo "</div>";
      }

      echo "<div class='spaced'>";
      if ($canedit && $number && ($withtemplate < 2)) {
         Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
         $massiveactionparams = array('num_displayed' => $number);
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr>";
      if ($canedit && $number && ($withtemplate < 2)) {
         echo "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
      }
      echo "<th>" . __('Name') . "</th>";
      if (Session::isMultiEntitiesMode()) {
         echo "<th>" . __('Entity') . "</th>";
      }
      echo "<th>" . PluginCircuitsCircuitType::getTypeName(1) . "</th>";
      echo "<th>" . __('URL') . "</th>";
      echo "<th>" . __('Server') . "</th>";
      echo "<th>" . __('Language') . "</th>";
      echo "<th>" . __('Version') . "</th>";
      echo "<th>" . __('Comments') . "</th>";
      echo "</tr>";
      $used = array();

      if ($number) {

         Session::initNavigateListItems('PluginCircuitsCircuit',
            //TRANS : %1$s is the itemtype name,
            //        %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'),
                                                $item->getTypeName(1), $item->getName()));


         foreach ($webs as $data) {
            $webID = $data["id"];
            $link  = NOT_AVAILABLE;

            if ($web->getFromDB($webID)) {
               $link = $web->getLink();
            }

            Session::addToNavigateListItems('PluginCircuitsCircuit', $webID);

            $used[$webID] = $webID;
            $assocID      = $data["assocID"];

            echo "<tr class='tab_bg_1" . ($data["is_deleted"] ? "_2" : "") . "'>";
            if ($canedit && ($withtemplate < 2)) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $data["assocID"]);
               echo "</td>";
            }
            echo "<td class='center'>$link</td>";
            if (Session::isMultiEntitiesMode()) {
               echo "<td class='center'>" . Dropdown::getDropdownName("glpi_entities", $data['entities_id']) .
                    "</td>";
            }
            echo "<td>" . Dropdown::getDropdownName("glpi_plugin_circuits_circuittypes",
                                                    $data["plugin_circuits_circuittypes_id"]) .
                 "</td>";

            $link = Toolbox::substr($data["address"], 0, 30) . "...";
            echo "<td class='center'>" .
                 "<a href=\"" . str_replace("&", "&amp;", $data["address"]) . "\" target=\"_blank\">" .
                 "<u>" . $link . "</u></a></td>";

            echo "<td>" . Dropdown::getDropdownName("glpi_plugin_circuits_circuitservertypes",
                                                    $data["plugin_circuits_circuitservertypes_id"]) .
                 "</td>";
            echo "<td>" . Dropdown::getDropdownName("glpi_plugin_circuits_circuittechnics",
                                                    $data["plugin_circuits_circuittechnics_id"]) .
                 "</td>";
            echo "<td>" . $data["version"] . "</td>";
            echo "<td>" . $data["comment"] . "</td>";
            echo "</tr>";
            $i++;
         }
      }


      echo "</table>";
      if ($canedit && $number && ($withtemplate < 2)) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";
   }

   /**
    * @since version 0.84
    **/
   function getForbiddenStandardMassiveAction() {

      $forbidden   = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }

   /**
    * Show circuits associated to an item
    *
    * @since version 0.84
    *
    * @param $item            Supplier object for which associated circuits must be displayed
    * @param $withtemplate (default '')
    *
    * @return bool
    */
   static function showForSupplier(Supplier $item, $withtemplate = '') {
      global $DB;

      $ID = $item->getField('id');

      if ($item->isNewID($ID)) {
         return false;
      }
      if (!Session::haveRight("plugin_circuits", READ)) {
         return false;
      }

      if (!$item->can($item->fields['id'], READ)) {
         return false;
      }

      if (empty($withtemplate)) {
         $withtemplate = 0;
      }

      $rand         = mt_rand();
      $is_recursive = $item->isRecursive();

      $query = "SELECT `glpi_entities`.`id` AS entity,
                        `glpi_plugin_circuits_circuits`.`id` AS assocID,
                       `glpi_plugin_circuits_circuits`.`name` AS assocName,
                       `glpi_plugin_circuits_circuits`.*
                FROM `glpi_plugin_circuits_circuits`
                LEFT JOIN `glpi_entities` ON (`glpi_plugin_circuits_circuits`.`entities_id`=`glpi_entities`.`id`)
                WHERE `glpi_plugin_circuits_circuits`.`suppliers_id` = '$ID' ";

      $query .= getEntitiesRestrictRequest(" AND", "glpi_plugin_circuits_circuits", '', '', true);

      $query .= " ORDER BY `assocName`";

      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $i      = 0;

      $webs = array();
      if ($numrows = $DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $webs[$data['assocID']] = $data;
         }
      }

      echo "<div class='spaced'>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr>";
      echo "<th>" . __('Name') . "</th>";
      if (Session::isMultiEntitiesMode())
         echo "<th>" . __('Entity') . "</th>";
      echo "<th>" . PluginCircuitsCircuitType::getTypeName(1) . "</th>";
      echo "<th>" . __('URL') . "</th>";
      echo "<th>" . __('Server') . "</th>";
      echo "<th>" . __('Language') . "</th>";
      echo "<th>" . __('Version') . "</th>";
      echo "<th>" . __('Comments') . "</th>";
      echo "</tr>";
      $used = array();

      if ($number) {

         Session::initNavigateListItems('PluginCircuitsCircuit',
            //TRANS : %1$s is the itemtype name,
            //        %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'),
                                                $item->getTypeName(1), $item->getName()));

         $web = new PluginCircuitsCircuit();

         foreach ($webs as $data) {
            $webID = $data["id"];
            $link  = NOT_AVAILABLE;

            if ($web->getFromDB($webID)) {
               $link = $web->getLink();
            }

            Session::addToNavigateListItems('PluginCircuitsCircuit', $webID);

            echo "<tr class='tab_bg_1" . ($data["is_deleted"] ? "_2" : "") . "'>";
            echo "<td class='center'>$link</td>";
            if (Session::isMultiEntitiesMode()) {
               echo "<td class='center'>" . Dropdown::getDropdownName("glpi_entities", $data['entities_id']) .
                    "</td>";
            }
            echo "<td>" . Dropdown::getDropdownName("glpi_plugin_circuits_circuittypes",
                                                    $data["plugin_circuits_circuittypes_id"]) .
                 "</td>";

            $link = Toolbox::substr($data["address"], 0, 30) . "...";
            echo "<td class='center'>" .
                 "<a href=\"" . str_replace("&", "&amp;", $data["address"]) . "\" target=\"_blank\">" .
                 "<u>" . $link . "</u></a></td>";

            echo "<td>" . Dropdown::getDropdownName("glpi_plugin_circuits_circuitservertypes",
                                                    $data["plugin_circuits_circuitservertypes_id"]) .
                 "</td>";
            echo "<td>" . Dropdown::getDropdownName("glpi_plugin_circuits_circuittechnics",
                                                    $data["plugin_circuits_circuittechnics_id"]) .
                 "</td>";
            echo "<td>" . $data["version"] . "</td>";
            echo "<td>" . $data["comment"] . "</td>";
            echo "</tr>";
            $i++;
         }
      }


      echo "</table>";
      echo "</div>";
   }


   /**
    * @param PluginPdfSimplePDF                  $pdf
    * @param PluginCircuitsCircuit $item
    *
    * @return bool
    */
   static function ItemsPdf(PluginPdfSimplePDF $pdf, PluginCircuitsCircuit $item) {
      global $DB;

      $ID = $item->getField('id');

      if (!$item->can($ID, READ)) {
         return false;
      }

      if (!Session::haveRight("plugin_circuits", READ)) {
         return false;
      }

      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>' . _n('Associated item', 'Associated items', 2) . '</b>');

      $query  = "SELECT DISTINCT `itemtype`
                FROM `glpi_plugin_circuits_circuits_items`
                WHERE `plugin_circuits_circuits_id` = '$ID'
                ORDER BY `itemtype`";
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if (Session::isMultiEntitiesMode()) {
         $pdf->setColumnsSize(12, 27, 25, 18, 18);

         $pdf->displayTitle('<b><i>' . __('Type'),
                            __('Name'),
                            __('Entity'),
                            __('Serial number'),
                            __('Inventory number') . '</i></b>');
      } else {
         $pdf->setColumnsSize(25, 31, 22, 22);
         $pdf->displayTitle('<b><i>' . __('Type'),
                            __('Name'),
                            __('Serial number'),
                            __('Inventory number') . '</i></b>');
      }

      if (!$number) {
         $pdf->displayLine(__('No item found'));
      } else {
         for ($i = 0; $i < $number; $i++) {
            $type = $DB->result($result, $i, "itemtype");
            if (!class_exists($type)) {
               continue;
            }
            if ($item->canView()) {
               $column = "name";
               $table  = getTableForItemType($type);
               $items  = new $type();

               $query = "SELECT `" . $table . "`.*, `glpi_entities`.`id` AS entity "
                        . " FROM `glpi_plugin_circuits_circuits_items`, `" . $table
                        . "` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `" . $table . "`.`entities_id`) "
                        . " WHERE `" . $table . "`.`id` = `glpi_plugin_circuits_circuits_items`.`items_id` 
                  AND `glpi_plugin_circuits_circuits_items`.`itemtype` = '$type' 
                  AND `glpi_plugin_circuits_circuits_items`.`plugin_circuits_circuits_id` = '$ID' ";
               if ($type != 'User')
                  $query .= getEntitiesRestrictRequest(" AND ", $table, '', '', $items->maybeRecursive());

               if ($items->maybeTemplate()) {
                  $query .= " AND `" . $table . "`.`is_template` = '0'";
               }
               $query .= " ORDER BY `glpi_entities`.`completename`, `" . $table . "`.`$column`";

               if ($result_linked = $DB->query($query))
                  if ($DB->numrows($result_linked)) {

                     while ($data = $DB->fetch_assoc($result_linked)) {
                        if (!$items->getFromDB($data["id"])) {
                           continue;
                        }
                        $items_id_display = "";

                        if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) $items_id_display = " (" . $data["id"] . ")";
                        if ($type == 'User')
                           $name = Html::clean(getUserName($data["id"])) . $items_id_display;
                        else
                           $name = $data["name"] . $items_id_display;

                        if ($type != 'User') {
                           $entity = Html::clean(Dropdown::getDropdownName("glpi_entities", $data['entity']));
                        } else {
                           $entity = "-";
                        }

                        if (Session::isMultiEntitiesMode()) {
                           $pdf->setColumnsSize(12, 27, 25, 18, 18);
                           $pdf->displayLine(
                              $items->getTypeName(),
                              $name,
                              $entity,
                              (isset($data["serial"]) ? "" . $data["serial"] . "" : "-"),
                              (isset($data["otherserial"]) ? "" . $data["otherserial"] . "" : "-")
                           );
                        } else {
                           $pdf->setColumnsSize(25, 31, 22, 22);
                           $pdf->displayTitle(
                              $items->getTypeName(),
                              $name,
                              (isset($data["serial"]) ? "" . $data["serial"] . "" : "-"),
                              (isset($data["otherserial"]) ? "" . $data["otherserial"] . "" : "-")
                           );
                        }
                     } // Each device
                  } // numrows device
            } // type right
         } // each type
      } // numrows type
   }


   /**
    * show for PDF the circuits associated with a device
    *
    * @param $pdf
    * @param $item
    *
    **/
   static function PdfFromItems(PluginPdfSimplePDF $pdf, CommonGLPI $item) {
      global $DB;

      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>' . _n('Associated TC circuit', 'Associated TC circuits', 2, 'circuits') . '</b>');

      $ID       = $item->getField('id');
      $itemtype = get_class($item);

      $web = new PluginCircuitsCircuit();

      $query = "SELECT `glpi_plugin_circuits_circuits`.* "
               . " FROM `glpi_plugin_circuits_circuits_items`,`glpi_plugin_circuits_circuits` "
               . " LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_circuits_circuits`.`entities_id`) "
               . " WHERE `glpi_plugin_circuits_circuits_items`.`items_id` = '" . $ID . "' 
         AND `glpi_plugin_circuits_circuits_items`.`itemtype` = '" . $itemtype . "' 
         AND `glpi_plugin_circuits_circuits_items`.`plugin_circuits_circuits_id` = `glpi_plugin_circuits_circuits`.`id` "
               . getEntitiesRestrictRequest(" AND ", "glpi_plugin_circuits_circuits", '', '', $web->maybeRecursive());

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if (!$number) {
         $pdf->displayLine(__('No item found'));
      } else {
         if (Session::isMultiEntitiesMode()) {
            $pdf->setColumnsSize(25, 25, 15, 15, 20);
            $pdf->displayTitle('<b><i>' . __('Name'),
                               __('Entity'),
                               __('Technician in charge of the hardware'),
                               __('Group in charge of the hardware'),
                               PluginCircuitsCircuitType::getTypeName(1) . '</i></b>');
         } else {
            $pdf->setColumnsSize(30, 30, 20, 20);
            $pdf->displayTitle('<b><i>' . __('Name'),
                               __('Technician in charge of the hardware'),
                               __('Group in charge of the hardware'),
                               PluginCircuitsCircuitType::getTypeName(1) . '</i></b>');
         }
         while ($data = $DB->fetch_array($result)) {

            if (Session::isMultiEntitiesMode()) {
               $pdf->setColumnsSize(25, 25, 15, 15, 20);
               $pdf->displayLine($data["name"],
                                 Html::clean(Dropdown::getDropdownName("glpi_entities",
                                                                       $data['entities_id'])),
                                 Html::clean(getUserName("glpi_users", $data["users_id_tech"])),
                                 Html::clean(Dropdown::getDropdownName("glpi_groups",
                                                                       $data["groups_id_tech"])),
                                 Html::clean(Dropdown::getDropdownName("glpi_plugin_circuits_circuittypes",
                                                                       $data["plugin_circuits_circuittypes_id"])));
            } else {
               $pdf->setColumnsSize(50, 25, 25);
               $pdf->displayLine(
                  $data["name"],
                  Html::clean(getUserName("glpi_users", $data["users_id_tech"])),
                  Html::clean(Dropdown::getDropdownName("glpi_groups", $data["groups_id_tech"])),
                  Html::clean(Dropdown::getDropdownName("glpi_plugin_circuits_circuittypes",
                                                        $data["plugin_circuits_circuittypes_id"])));
            }
         }
      }
   }

   /**
    * @param PluginPdfSimplePDF $pdf
    * @param CommonGLPI         $item
    * @param                    $tab
    *
    * @return bool
    */
   static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {

      if ($item->getType() == 'PluginCircuitsCircuit') {
         self::ItemsPdf($pdf, $item);
      } else if (in_array($item->getType(), PluginCircuitsCircuit::getTypes(true))) {
         self::PdfFromItems($pdf, $item);
      } else {
         return false;
      }
      return true;
   }

}