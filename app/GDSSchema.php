<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GDSSchema extends Model
{
	protected $schemaColumns = [];

	private $schema;
	private $gds_store;
	private $project_id;

    function __construct(array $attributes = [])
    {
    	parent::__construct($attributes);
    	$this->project_id = env("PROJECT_ID");
    	$this->buildSchema();
    }

    private function buildSchema()
    {
    	$this->schema = new \GDS\Schema($this->table);
    	foreach ($this->schemaColumns as $key => $value) {
    		$this->addColumnToSchema($key, $value);
    	} 
    }

    private function addColumnToSchema($name, $type = "string")
    {
    	$type = strtolower(trim($type));
    	switch ($type) {
    		case 'integer':
    			$this->schema->addInteger($name);
    			break;
    		default:
    			$this->schema->addString($name);
    			break;
    	}
    }

    public function getStore(){ 
    	if($this->gds_store == null){
    		$this->gds_store = new \GDS\Store($this->schema, new \GDS\Gateway\RESTv1($this->project_id));	
    	}
    	return $this->gds_store;
    }

    public function save(array $options = [])
    {
    	$entity = $this->getStore()->createEntity($this->getAttributes());
    	$this->getStore()->upsert($entity);
    	return $entity; 
    }
}
