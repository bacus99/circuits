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
 * Class PluginCircuitsCircuitPDF
 */
class PluginCircuitsCircuitPDF extends PluginPdfCommon {


   /**
    * PluginCircuitsCircuitPDF constructor.
    *
    * @param CommonGLPI|NULL $obj
    */
   function __construct(CommonGLPI $obj = NULL) {

      $this->obj = ($obj ? $obj : new  PluginCircuitsCircuit());
   }

   /**
    * @param array $options
    *
    * @return mixed
    */
   function defineAllTabs($options = array()) {

      $onglets = parent::defineAllTabs($options);
      unset($onglets['Item_Problem$1']); // TODO add method to print linked Problems
      return $onglets;
   }

   /**
    * @param PluginPdfSimplePDF $pdf
    * @param CommonGLPI         $item
    * @param                    $tab
    *
    * @return bool
    */
   static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {

      switch ($tab) {
         case '_main_' :
            $item->show_PDF($pdf);
            break;

         default :
            return false;
      }
      return true;
   }
}