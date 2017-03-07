<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2016                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2016
 * $Id$
 *
 */
class CRM_Ca_Page_Geocode extends CRM_Core_Page {

  function run() {
    $data = $_POST;
    if (isset($data['address'])) {
      if (CRM_Utils_Array::value('country', $data['address'])) {
        $data['address']['country'] = CRM_Core_PseudoConstant::country($data['address']['country']);
      }
      else {
        // Set default country to Canada if not selected.
        $data['address']['country'] = "Canada";
      }
      CRM_Utils_Geocode_Google::format($data['address']);
      if (isset($data['address']['geo_code_1']) && isset($data['address']['geo_code_2'])) {
        $json = array($data['address']['geo_code_1'], $data['address']['geo_code_2']);
        echo json_encode($json);
        exit;
      }
      else {
        echo 0;
        exit;
      }
    }
    else {
      echo 0;
      exit;
    }
  }

}
