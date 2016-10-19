<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GDSHelper extends Model
{   

    public static function fetchExistingKinds()
    { 
        $kinds = static::fetchKinds("__kind__");
        //filter system "kinds"
        return collect($kinds)->filter(function($kind){
            return !starts_with($kind->getKeyName(), "__");
        })->all();
    }

    public static function fetchProperties($kind)
    { 
        $store = static::createDefaultStore("__Stat_PropertyType_PropertyName_Kind__");     
        return $store->query("SELECT * FROM __Stat_PropertyType_PropertyName_Kind__ WHERE kind_name = '" . $kind . "'")->fetchAll();
    }

    public static function insert($name, $properties, $store = null)
    {
    	if($store == null){
    		$store = static::createDefaultStore($name);		
    	}

    	$entity = $store->createEntity($properties);
    	$store->upsert($entity);
    	return $entity;
    }

    public static function insertMany($name, $entitiesProps, $store = null)
    {
    	if($store == null){
    		$store = static::createDefaultStore($name);		
    	}

    	$entities = [];
    	foreach($entitiesProps as $entityProps){
    		$entities[] = $store->createEntity($entityProps);	
    	}
    	
    	$store->upsert($entities);
    	return $entities;
    }

    public static function fetch($name, $perPage = 10, $page = 1, $store = null)
    {
    	if($store == null){
    		$store = static::createDefaultStore($name);		
    	} 
    	$offset = ($page - 1) * $perPage;
    	return $store->fetchPage($perPage, $offset); 
    }

    public static function fetchKinds()
    {
        $store = static::createDefaultStore("__kind__");     
        return $store->fetchAll();
    }

	public static function createDefaultStore($name)
	{
		return new \GDS\Store($name, new \GDS\Gateway\RESTv1(env("PROJECT_ID")));		
	}

    public static function getMergedColumns($entities)
    {
    	$merged_columns = [];
    	foreach($entities as $item){
    		$merged_columns = array_unique(array_merge($merged_columns, array_keys($item->getData())));
    	}
    	return $merged_columns;
    }
}
