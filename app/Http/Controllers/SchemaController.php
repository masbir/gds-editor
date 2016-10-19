<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ImportCSVSession;
use App\Http\Requests;

class SchemaController extends Controller
{
	public function __construct()
	{

	}

	public function index(Request $request)
    {
        $kinds = $this->getCachedKinds();

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

    public function importCSV(Request $request)
    {
        $importSession = ImportCSVSession::fromSession($request);
        if($importSession != null){
            return redirect("/kinds/import/map");
        }else{
            $kinds = $this->getCachedKinds();
            return view("schema.importCSV", compact("kinds"));    
        }
        
    }

    public function postImportCSV(Request $request)
    { 

        $this->validate($request, [
            'csvFile' => 'required|file',
            'kind' => 'required',
            'hasHeader' => 'boolean',
        ]);

        $savedRequest = [];
        $importSession = new ImportCSVSession();

        //save csv file
        if ($request->file('csvFile')->isValid()) { 
            $importSession->originalFileName = $request->file('csvFile')->getClientOriginalName(); 
            $csvFile = $request->csvFile->store('imported-csv');
            $importSession->csvFile = $csvFile;
        }

        //persist data in session
        $importSession->kind = $request->kind;
        $importSession->hasHeader = $request->hasHeader; 
        $importSession->saveSession($request);

        //redirect to mapping page
        return redirect("/kinds/import/map");
    }

    public function mapImportedCSV(Request $request)
    {
        //check session data from import exists..
        $importSession = ImportCSVSession::fromSession($request);

        //if not redirect user to import page
        if($importSession == null){
            return redirect("/kinds/import");
        } 

        //get first row
        $sampleRow = \App\CSVHelper::getSampleRow($importSession->full_path_csv, ",", $importSession->hasHeader);
        $importSession->firstRow = $sampleRow["sampleRow"];
        $importSession->header = $sampleRow["header"];
        $importSession->saveSession($request);

        //existing kind properties
        $properties_raw = $this->getCachedProperties($importSession->kind);
        if(count($properties_raw) == 0){
            $properties = ["Property 1"];
        }else{
            $properties = collect($properties_raw)->map(function($item){
                return $item->getData()["property_name"];
            })->all();    
        }
         
        return view("schema.columnMapping", compact('importSession', 'properties')); 
    }

    public function postMapImportedCSV(Request $request)
    {

        $mapped_assoc = [];
        foreach($request->mappedColumns as $key => $column){
            if($key < 0) continue;
            $property = $request->properties[$key];
            $mapped_assoc[$property] = intval($column);
        } 
        //dd($mapped_assoc);

        //retrieve data from session
        $importSession = ImportCSVSession::fromSession($request);  

        $submission_data = [];
         if ( ($handle = fopen($importSession->full_path_csv, "r")) !== FALSE) {
            try{
                $line_counter = 1;
                while (($line = fgetcsv($handle, 3000, ",")) !== false) {
                    if(($importSession->hasHeader && $line_counter > 1) || (!$importSession->hasHeader)){
                        $mapped_line = [];
                        foreach($mapped_assoc as $property_name => $column_number){
                            if(isset($line[$column_number])){
                                $mapped_line[$property_name] = $line[$column_number];    
                            }
                        }
                        $submission_data[] = $mapped_line;
                    }
                    $line_counter++;
                }
            }catch(\Exception $ex){ 
            }finally{
                fclose($handle);    
            }
        }

        \App\GDSHelper::insertMany($importSession->kind, $submission_data);

        //done, remove cache
        $request->session()->forget(ImportCSVSession::$cacheName);

        return redirect("/kinds/" . $importSession->kind);
    }

    public function cancelImport(Request $request)
    {
        $request->session()->forget(ImportCSVSession::$cacheName);
        return redirect("/kinds/import");
    }

    public function getCachedKinds()
    {
        //\Cache::forget("kinds");
        return \Cache::remember("kinds", 60, function(){
            return \App\GDSHelper::fetchExistingKinds();
        });
    }

    public function getCachedProperties($kind)
    {
        //\Cache::forget($kind . ".properties");
        return \Cache::remember($kind . ".properties", 60, function() use ($kind){
            return \App\GDSHelper::fetchProperties($kind);
        });
    }
}
