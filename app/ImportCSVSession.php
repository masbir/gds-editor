<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ImportCSVSession extends Model
{
	public static $cacheName = "importCSVSession";
	protected $fillable = ["csvFile" , "kind", "hasHeader", "originalFileName"];
    function __construct()
    {

    } 

    public function saveSession($request)
    {
    	$request->session()->put(static::$cacheName, $this->toJson());
    }

    public function getFullPathCsvAttribute()
    {
    	return storage_path("app/" . $this->csvFile);
    }

    public function csvFileExists()
    {
        return file_exists($this->full_path_csv);
    }

    public function deleteCsvFile()
    {
        return unlink($this->full_path_csv);
    }

    public static function fromSession($request)
    {
    	$name = static::$cacheName;
    	if($request->session()->has($name)){
    		$fromSession = json_decode($request->session()->get($name), true);
    		$data = new ImportCSVSession();
    		$data->fill($fromSession);
    		return $data;
    	}else{
    		return null;
    	}
    }
}
