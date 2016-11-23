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
class CRM_Ca_BAO_Represent {

  function getInfo($url) {
    $ch = curl_init($url);
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);

    return json_decode($response);
  }

  public static function getContactDetails($cid) {
    if (empty($cid)) {
      return;
    }
    $returnProperties = array(
      "display_name",
      "email",
      CON,
      AFFN,
      TITLE,
    );
    $result = civicrm_api3('Contact', 'get', array(
      'sequential' => 1,
      'return' => $returnProperties,
      'id' => $cid,
    ));
    if ($result['count'] > 0) {
      foreach ($result['values'] as $contact) {
        foreach ($contact as $key => $values) {
          if ($key == CON) {
            $details['district_name'] = $values;
          }
          elseif ($key == AFFN) {
            $details['party_name'] = $values;
          }
          elseif ($key == TITLE) {
            $details['elected_office'] = $values;
          }
          else {
            $details[$key] = $values;
          }
        }
      }
    }
    return $details;
  }

}