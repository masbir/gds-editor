<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ImportCSVSession;
use App\Http\Requests;
use App\CSVHelper;

class SchemaController extends Controller
{
	public function __construct()
	{

	}

	public function index(Request $request)
    {
        $kinds = $this->getCachedKinds();

        $processing = \App\UploadDataCounter::getAllInProgressParsed();
        //dd($processing);

    	return view('schema.index', compact('kinds', 'processing'));
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

            //normalize line endings
            file_put_contents( $importSession->full_path_csv, preg_replace('~\R~u', "\r\n", file_get_contents($importSession->full_path_csv)) );
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
            if($importSession->hasHeader){
                $properties = $importSession->header;
            }else{
                $properties = ["Property 1"];
            }
            
        }else{
            $properties = collect($properties_raw)->map(function($item){
                return $item->getData()["property_name"];
            })->all();    
        }
         
        return view("schema.columnMapping", compact('importSession', 'properties')); 
    }
        //prepare mapping
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

        //get mapped data
        $submission_data = [];
        CSVHelper::readFile($importSession->full_path_csv, function($number, $line) use ($importSession, &$submission_data, $mapped_assoc) {
            if(($importSession->hasHeader && $number > 1) || (!$importSession->hasHeader)){
                $mapped_line = [];
                foreach($mapped_assoc as $property_name => $column_number){
                    if(isset($line[$column_number])){
                        $mapped_line[$property_name] = $line[$column_number];    
                    }
                } 
                $submission_data[] = $mapped_line;
            }
        });  

        //since there's a limit of 500 entities per insert,
        //let's divide it into 200 entities per insert
        $chunks = collect($submission_data)->chunk(200);
        $job_id = time();

        foreach($chunks as $key => $chunk){
            $job = (new \App\Jobs\UploadData($importSession->kind, $chunk, $job_id))->onQueue('importing');
            dispatch($job);
        }

        //save counter to cache
        $counter = new \App\UploadDataCounter();
        $counter->id = $job_id;
        $counter->total = count($chunks);
        $counter->processed = 0;
        $counter->file = $importSession->csvFile;
        $counter->kind = $importSession->kind;
        $counter->saveCache();

        //done, remove cache
        $request->session()->forget(ImportCSVSession::$cacheName);

        return redirect("/");
    }

    public function cancelImport(Request $request)
    {

        $importSession = ImportCSVSession::fromSession($request);  
        if($importSession != null && $importSession->csvFileExists()){
            $importSession->deleteCsvFile();
        }
        $request->session()->forget(ImportCSVSession::$cacheName);
        return redirect("/kinds/import");
    }

    public function getCachedKinds()
    {
        //\Cache::forget("kinds");
        //return \Cache::remember("kinds", 60, function(){
            return \App\GDSHelper::fetchExistingKinds();
        //});
    }

    public function getCachedProperties($kind)
    {
        //\Cache::forget($kind . ".properties");
        //return \Cache::remember($kind . ".properties", 60, function() use ($kind){
            return \App\GDSHelper::fetchProperties($kind);
        //});
    }
}
