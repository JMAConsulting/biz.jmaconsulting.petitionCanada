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
class CRM_Ca_Page_Representatives extends CRM_Core_Page {

  function run() {
    $geocode = $_POST['geocode'];
    $representatives = $targets = $reps = $repObjects = array();
    if (array_key_exists('postal_code', $geocode)) {
      $url = ENDPOINT . "/postcodes/" . strtoupper(str_replace(' ', '', $geocode['postal_code']));
      $isPostal = TRUE;
    }
    else {
      $url = ENDPOINT . "/representativespostcodes/?point=" . $geocode[0] . "," . $geocode[1];
      $isPostal = FALSE;
    }

    // Get fixed group targets.
    $fixed = civicrm_api3('GroupContact', 'get', array(
      'sequential' => 1,
      'return' => array("contact_id"),
      'group_id' => "Petition_Targets",
      'status' => "Added",
    ));

    if ($fixed['count'] > 0) {
      foreach ($fixed['values'] as $contact) {
        $targets[] = CRM_Ca_BAO_Represent::getContactDetails($contact['contact_id']);
      }
    }

    $representatives = CRM_Ca_BAO_Represent::getInfo($url);
    $validDistricts = array(
      "Ajax",
      "Markham",
      "Pickering",
      "Uxbridge",
      "Whitby",
      "Whitchurch-Stouffville",
    );
    if ($isPostal) {
      if (!empty($representatives)) {
        $repObjects = array_merge($representatives->representatives_centroid, $representatives->representatives_concordance);
      }
    }
    else {
      if (!empty($representatives) && $representatives->meta->total_count > 0) {
        $repObjects = $representatives->objects;
      }
    }
    foreach ($repObjects as $key => $values) {
      if (array_search($values->district_name, $validDistricts)) {
        $reps[] = array(
          'first_name' => $values->first_name,
          'last_name' => $values->last_name,
          'display_name' => $values->name,
          'url' => $values->url,
          'district_name' => $values->district_name,
          'party_name' => $values->party_name,
          'elected_office' => $values->elected_office,
          'email' => $values->email,
        );
      }
    }
    // Add fixed targets to local representatives.
    $master =  array_merge($targets, $reps);

    // Sort by elected office.
    $master = CRM_Ca_BAO_Represent::sort($master);
    if (!empty($master)) {
      echo json_encode($master);
    }
    else {
      echo 0;
    }
    CRM_Utils_System::civiExit();
  }

}
