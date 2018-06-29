<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z $
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

// Init the hooks of the plugins -Needed
function plugin_init_circuits() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['circuits'] = true;
   //load changeprofile function
   $PLUGIN_HOOKS['change_profile']['circuits']   = array('PluginCircuitsProfile',
                                                                'initProfile');
   $PLUGIN_HOOKS['assign_to_ticket']['circuits'] = true;

   if (class_exists('PluginCircuitsCircuit_Item')) { // only if plugin activated
      $PLUGIN_HOOKS['plugin_datainjection_populate']['circuits']
         = 'plugin_datainjection_populate_circuits';
   }

   // Params : plugin name - string type - number - class - table - form page
   Plugin::registerClass('PluginCircuitsCircuit',
                         array('linkgroup_tech_types'   => true,
                               'linkuser_tech_types'    => true,
                               'document_types'         => true,
                               'contract_types'         => true,
                               'ticket_types'           => true,
                               'helpdesk_visible_types' => true,
                               'link_types'             => true,
                               'addtabon'               => 'Supplier'));

   if (class_exists('PluginCircuitsCircuit')) {
      Link::registerTag(PluginCircuitsCircuit::$tags);
   }
   Plugin::registerClass('PluginCircuitsProfile', array('addtabon' => array('Profile')));

   if (class_exists('PluginAccountsAccount')) {
      PluginAccountsAccount::registerType('PluginCircuitsCircuit');
   }

   if (class_exists('PluginCertificatesCertificate')) {
      PluginCertificatesCertificate::registerType('PluginCircuitsCircuit');
   }

   //if glpi is loaded
   if (Session::getLoginUserID()) {

      //if environment plugin is installed
      $plugin = new Plugin();
      if (!$plugin->isActivated('environment')
          && Session::haveRight("plugin_circuits", READ)) {

         $PLUGIN_HOOKS['menu_toadd']['circuits'] = array('assets' => 'PluginCircuitsMenu');
      }

      if (Session::haveRight("plugin_circuits", UPDATE)) {
         $PLUGIN_HOOKS['use_massive_action']['circuits'] = 1;
      }

      if (Session::haveRight("plugin_circuits", READ)
          || Session::haveRight("config", UPDATE)) {
      }

      // Import from Data_Injection plugin
      //      $PLUGIN_HOOKS['migratetypes']['circuits']
      //                                   = 'plugin_datainjection_migratetypes_circuits';
      $PLUGIN_HOOKS['plugin_pdf']['PluginCircuitsCircuit']
         = 'PluginCircuitsCircuitPDF';
   }

   // End init, when all types are registered
   $PLUGIN_HOOKS['post_init']['circuits'] = 'plugin_circuits_postinit';
}


/**
 * Get the name and the version of the plugin - Needed
 *
 * @return array
 */
function plugin_version_circuits() {

   return array('name'           => _n('Circuit', 'Circuits', 2, 'circuits'),
                'version'        => '1.0.0',
                'license'        => 'GPLv2+',
                'author'         => "",
                'homepage'       => '',
                'minGlpiVersion' => '9.2');
}


/**
 * Optional : check prerequisites before install : may print errors or add to message after redirect
 *
 * @return bool
 */
function plugin_circuits_check_prerequisites() {

   if (version_compare(GLPI_VERSION, '9.2', 'lt') || version_compare(GLPI_VERSION, '9.3', 'ge')) {
      echo __('This plugin requires GLPI >= 9.2');
      return false;
   }
   return true;
}


/**
 * Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
 *
 * @return bool
 */
function plugin_circuits_check_config() {
   return true;
}

/**
 * @param $types
 *
 * @return mixed
 */
function plugin_datainjection_migratetypes_circuits($types) {

   $types[1300] = 'PluginCircuitsCircuit';
   return $types;
}
