<?php
/*-------------------------------------------------------+
| SYSTOPIA EXTENSIBLE EXPORT EXTENSION                   |
| Copyright (C) 2018 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

use CRM_Xportx_ExtensionUtil as E;

/**
 * Represents one Export process
 */
class CRM_Xportx_Export {

  protected $configuration;
  protected $modules;
  protected $exporter;

  /**
   * Create an export object with the given configuration data. Format is:
   * 'modules'  => [{
   *   'class'  => 'CRM_Xportx_Module_ContactBase',
   *   'prefix' => '',
   *   'config' => {},
   *  }, {...}],
   * 'exporter' => {
   *   'class'  => 'CRM_Xportx_Exporter_CSV',
   *   'config' => {},
   *  },
   */
  public function __construct($config) {
    // set configuration
    if (!isset($config['configuration']) || !is_array($config['configuration'])) {
      throw new Exception("XPortX: Export configuration has no 'configuration' section.");
    }
    $this->configuration = $config['configuration'];

    // get modules
    if (!isset($config['modules']) || !is_array($config['modules'])) {
      throw new Exception("XPortX: Export configuration has no 'modules' section.");
    }
    $this->modules = array();
    foreach ($config['modules'] as $module_spec) {
      $module = $this->getInstance($module_spec['class'], $module_spec['config']);
      if ($module) {
        $this->modules[] = $module;
      }
    }
    if (empty($this->modules)) {
      throw new Exception("XPortX: No modules selected.");
    }

    // get exporter
    if (!isset($config['exporter']) || !is_array($config['exporter'])) {
      throw new Exception("XPortX: Export configuration has no 'exporter' section.");
    }
    $this->exporter = $this->getInstance($config['exporter']['class'], $config['exporter']['config']);
    if (empty($this->exporter)) {
      throw new Exception("XPortX: No exporter selected.");
    }
  }

  /**
   * Run the export and write the result to the PHP out stream
   */
  public function writeToStream() {
    // WRITE HTML download header
    header('Content-Type: ' . $this->exporter->getMimeType());
    header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    $isIE = strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE');
    if ($isIE) {
      header("Content-Disposition: inline; filename=" . $this->exporter->getFileName());
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Pragma: public');
    } else {
      header("Content-Disposition: attachment; filename=" . $this->exporter->getFileName());
      header('Pragma: no-cache');
    }

    // get the data
    $data = $export->getData();

    // make the exporter write it to the stream
    $this->exporter->writeToFile($data, "php://output");

    // and end.
    CRM_Utils_System::civiExit();
  }

  /**
   * Get a module/exporter instance
   */
  protected function getInstance($class_name, $configuration) {
    if (class_exists($class_name)) {
      $instance = new $class_name();
      $instance->init($configuration);
      return $instance;
    } else {
      return NULL;
    }
  }





  /**
   * Create an export object using a stored configuration
   */
  public static function createByStoredConfig($config_name) {
    // TODO
  }
}
