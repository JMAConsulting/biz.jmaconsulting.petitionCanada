<?php

define('ENDPOINT', 'https://represent.opennorth.ca/');

require_once 'ca.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function ca_civicrm_config(&$config) {
  _ca_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function ca_civicrm_xmlMenu(&$files) {
  _ca_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function ca_civicrm_install() {
  _ca_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function ca_civicrm_uninstall() {
  _ca_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function ca_civicrm_enable() {
  _ca_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function ca_civicrm_disable() {
  _ca_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function ca_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _ca_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function ca_civicrm_managed(&$entities) {
  _ca_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function ca_civicrm_caseTypes(&$caseTypes) {
  _ca_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function ca_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _ca_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook_civicrm_buildForm
 *
 */
function ca_civicrm_buildForm($formName, &$form) {
  if ($formName == "CRM_Campaign_Form_Petition_Signature") {
    CRM_Core_Region::instance('form-body')->add(array(
      'template' => 'Ca/Represent.tpl',
    ));

    $form->add('hidden', 'representative_emails', NULL, array('readonly' => TRUE), FALSE);
    $form->add('wysiwyg', 'draft_email', ts('Email'), NULL);
  }
}

/**
 * Implementation of hook_civicrm_postProcess
 *
 */
function ca_civicrm_postProcess($formName, &$form) {
  if ($formName == "CRM_Campaign_Form_Petition_Signature") {
    if ($emails = CRM_Utils_Array::value('representative_emails', $form->_submitValues)) {
      $result = civicrm_api3('UFField', 'get', array(
        'sequential' => 1,
        'return' => array("help_pre"),
        'uf_group_id' => "Petition_Profile_17",
        'field_type' => "Formatting",
        'label' => "Representative Email",
      ));

      $message = $result['values'][0]['help_pre'];

      if (CRM_Utils_Array::value('draft_email', $form->_submitValues)) {
        $message .= "<br/>";
        $message .= $form->_submitValues['draft_email'];
      }
      // Send the email.
      $params = array(
        'toEmail' => $form->_submitValues['representative_emails'],
        'from' => $form->_submitValues['email-Primary'],
        'subject' => $form->petition['title'] . ' - Email',
        'html' => $message,
      );

      $sent = CRM_Utils_Mail::send($params);
      if ($sent) {
        $message .= "<br/>
          <b>Email also sent to representatives:</b> {$form->_submitValues['representative_emails']}";
        // Create activity
        $activityParams = array(
          'activity_name' => 'Email',
          'subject' => $form->petition['title'] . ' - Email',
          'status_id' => 'Completed',
          'activity_date_time' => date('YmdHis'),
          'source_contact_id' => $form->_contactId,
          'target_contact_id' => $form->_contactId,
          'assignee_contact_id' => $form->_contactId,
          'details' => $message,
          'version' => 3,
        );
        civicrm_api3('Activity', 'create', $activityParams);
      }
    }
  }
}
