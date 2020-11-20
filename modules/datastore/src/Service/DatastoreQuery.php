<?php

namespace Drupal\datastore\Service;

use RootedData\RootedJsonData;

/**
 * DatastoreQuery.
 */
class DatastoreQuery extends RootedJsonData {

  public function __construct(string $json) {
    $schema = file_get_contents(__DIR__ . "/../../docs/query.json");
    parent::__construct($json, $schema);
    $this->populateDefaults();
  }

  private function populateDefaults() {
    $schemaJson = new RootedJsonData($this->getSchema());
    $properties = $schemaJson->{"$.properties"};
    foreach ($properties as $key => $property) {
      if (isset($property['default'])) {
        $this->{"$.$key"} = $property['default'];
      }
    }
  }

}