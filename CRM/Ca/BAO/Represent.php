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
      "first_name",
      "last_name",
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
    $repsSorted = $unSorted = $unsortedReps = $emptyReps = array();
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
      "DeputyMayor",
      "RegionalCouncillor",
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

    // Now construct the unsorted array.
    foreach ($officeValues as $key => $val) {
      $unSorted[] = $$val;
    }
    foreach ($unSorted as $key => $unsort) {
      foreach ($unsort as $k => $v) {
        $unsortedReps[] = $v;
      }
    }

    // Merge the unsorted array.
    $repsSorted = array_merge($repsSorted, $unsortedReps);
    // Merge the rest which do not have elected offices.
    $repsSorted = array_merge($repsSorted, $emptyReps);

    $repsSorted = self::dupeCheck($repsSorted);

    return $repsSorted;
  }

  function dupeCheck($repsSorted) {
    // Remove duplicates based on name, email.
    foreach ($repsSorted as $key => $repres) {
      $dupes[$key] = array('name' => $repres['display_name'], 'email' => $repres['email']);
    }
    $dupes = array_map("unserialize", array_unique(array_map("serialize", $dupes)));
    // Now remove the dupes from the master array.
    foreach ($repsSorted as $key => $values) {
      if (!array_key_exists($key, $dupes)) {
        unset($repsSorted[$key]);
      }
    }
    return $repsSorted;
  }

  public static function createTarget($targets) {
    $targets = json_decode($targets);
    $repContact = $reps = array();
    foreach ($targets as $key => $value) {
      $repContact['first_name'] = $value->first_name;
      $repContact['last_name'] = $value->last_name;
      $repContact['email'] = $value->email;
      $repContact['contact_type'] = 'Individual';
      $repContact['version'] = 3;
      $dedupeParams = CRM_Dedupe_Finder::formatParams($repContact, 'Individual');
      $dedupeParams['check_permission'] = FALSE;
      $dupes = CRM_Dedupe_Finder::dupesByParams($dedupeParams, 'Individual');
      if (count($dupes) > 0) {
        $repContact['contact_id'] = $dupes[0];
      }
      if (isset($value->district_name)) {
        $repContact[CON] = $value->district_name;
      }
      if (isset($value->party_name)) {
        $repContact[AFFN] = $value->party_name;
      }
      if (isset($value->elected_office)) {
        $repContact[TITLE] = $value->elected_office;
      }
      $contact = civicrm_api3('Contact', 'create', $repContact);
      $reps[$contact['id']] = CRM_Contact_BAO_Contact::displayName($contact['id']);
    }
    return $reps;
  }
  
}