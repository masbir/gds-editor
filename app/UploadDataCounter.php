<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UploadDataCounter extends Model
{
	public $id;
	protected $fillable = ["total", "processed", "file", "kind"];

	public function getPctAttribute()
	{
		$total = intval($this->total);
		if($total <= 0) return 0;
		else return ((intval($this->processed) / $total) * 100);
	}

	public function getCacheName()
	{
		return sprintf("counter.%s", $this->id);
	}

	public function retrieveFromCache()
	{
		if(\Cache::has($this->getCacheName())){
			$this->fill( json_decode(\Cache::get($this->getCacheName()), true) );
			return true;
		}
		return false;
	}

	public function saveCache()
	{  
		\Cache::forever($this->getCacheName(), $this->toJson());
		static::addToProgress($this->id);	 
	}

	public function removeCache()
	{
		\Cache::forget($this->getCacheName());  
		static::removeFromProgress($this->id);
	}

	public static function addToProgress($id)
	{ 
		$existing = static::getAllInProgress();
		$existing[] = $id;
		$existing = array_unique($existing);
		\Cache::forever("progress", json_encode($existing));
	}

	public static function removeFromProgress($id)
	{ 
		$existing = static::getAllInProgress();
		if(($key = array_search($id, $existing)) !== false) {
		    unset($existing[$key]);
		}
		\Cache::forever("progress", json_encode($existing));
	}

	public static function getAllInProgress()
	{
		if(\Cache::has("progress")){
			return json_decode(\Cache::get("progress"), true);
		}else{
			return [];
		}
	}

	public static function getAllInProgressParsed()
	{
		$items = static::getAllInProgress();
		$parsed = [];
		foreach($items as $item){
			$udc = new UploadDataCounter();
			$udc->id = $item;
			$retrieved = $udc->retrieveFromCache();
			if($retrieved){
				$parsed[] = $udc;	
			}
		}
		return $parsed;
	}
}
