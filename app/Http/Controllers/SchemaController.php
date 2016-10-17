<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class SchemaController extends Controller
{
	public function __construct()
	{

	}

	public function index(Request $request)
    {
    	$kinds = \App\GDSHelper::fetchKinds("__kind__");

    	return view('schema.index', compact('kinds'));
    }

    public function kind(Request $request, $name)
    {
    	$page = $request->has('page') && $request->page > 0 ? intval($request->page) : 1;

    	$result = \App\GDSHelper::fetch($name, 20, $page); 

    	$merged_columns = \App\GDSHelper::getMergedColumns($result);

    	return view('schema.kind', compact('name', 'result', 'merged_columns', 'page'));
    }

    public function insert(Request $request, $name = '')
    { 
    	return view('schema.insert', compact('name'));
    }

    public function postInsert(Request $request)
    {
		$this->validate($request, [
			'name' => 'required',
			'keys' => 'required|array',
			'values' => 'required|array',
		]);

		$pairs = [];

		foreach($request->keys as $count => $key){
			if(trim($key) != ""){
				$pairs[$key] = $request->values[$count];	
			} 
		} 

		\App\GDSHelper::insert($request->name, $pairs);
		return redirect("/schemas/" . $request->name);
    }
}
