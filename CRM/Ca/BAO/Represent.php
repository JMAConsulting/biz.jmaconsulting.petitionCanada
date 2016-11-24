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

  public static function sort($reps) {
    $repsSorted = $unSorted = array();
    foreach ($reps as $keys => $values) {
      if (!empty($values['elected_office'])) {
        $office = str_replace(' ', '', $values['elected_office']);
        if (empty($$office)) {
          $$office = array($values);
        }
        else {
          array_push($$office, $values);
        }
      }
      else {
        $emptyReps[] = $values;
      }
      $officeValues[] = $office;
    }
    $officeValues = array_map("unserialize", array_unique(array_map("serialize", $officeValues)));
    $sortOrder = array(
      "PrimeMinister", 
      "FederalMinister", 
      "MP", 
      "Premier", 
      "ProvincialMinister", 
      "MPP", 
      "Mayor", 
      "Councillor",
    );
    $flip = array_flip($officeValues);
    foreach ($sortOrder as $elected) {
      unset($flip[$elected]);
      if (!empty($$elected)) {
        $repsSorted = array_merge($repsSorted, $$elected);
      }
      else {
        $unSortedReps[] = '';
      }
    }
    $officeValues = array_flip($flip);
    foreach ($officeValues as $key => $val) {
      $unSorted[] = $$val;
    }
    // Merge the rest which do not have elected offices.
    $repsSorted = array_merge($repsSorted, $emptyReps);
    // Remove duplicates.
    $repsSorted = array_map("unserialize", array_unique(array_map("serialize", $repsSorted)));
    return $repsSorted;
  }
  
}