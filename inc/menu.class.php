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

/**
 * Class PluginCircuitsMenu
 */
class PluginCircuitsMenu extends CommonGLPI {
   static $rightname = 'plugin_circuits';

   /**
    * @return translated
    */
   static function getMenuName() {
      return _n('TC Circuit', 'TC Circuits', 2, 'circuits');
   }

   /**
    * @return array
    */
   static function getMenuContent() {

      $menu                    = array();
      $menu['title']           = self::getMenuName();
      $menu['page']            = "/plugins/circuits/front/circuit.php";
      $menu['links']['search'] = PluginCircuitsCircuit::getSearchURL(false);
      if (PluginCircuitsCircuit::canCreate()) {
         $menu['links']['add'] = PluginCircuitsCircuit::getFormURL(false);
      }

      return $menu;
   }

   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['assets']['types']['PluginCircuitsMenu'])) {
         unset($_SESSION['glpimenu']['assets']['types']['PluginCircuitsMenu']);
      }
      if (isset($_SESSION['glpimenu']['assets']['content']['plugincircuitsmenu'])) {
         unset($_SESSION['glpimenu']['assets']['content']['plugincircuitsmenu']);
      }
   }
}