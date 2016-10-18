<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportCSV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'datastore:import 
                            {csv_file : Path to csv file you want to import} 
                            {kind : save data extracted from csv_file into this kind} 
                            {--skip= : number of lines to skip from csv_file content} 
                            {--take= : number of lines to take from csv_file content}
                            {--delimiter=}';

    private $csv_file;
    private $kind;
    private $skip;
    private $take;
    private $delimiter;
    private $columns;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import csv file to google cloud datastore';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setProperties();    

        if(!$this->csvFileExists()){
            $this->error($this->csv_file . " can't be found");
            return;
        }

        $this->info("Reading " . $this->csv_file);

        $file_content = [];
        try{
            $file_content = $this->readCsv($this->skip, $this->take);
            $this->info(count($file_content) . " read");
        }catch(\Exception $ex){
            $this->error($ex->getMessage());
        }

        if(count($file_content > 0)){
            $this->uploadLines($file_content);    
        }
        
        

    }

    private function readCsv($skip = 0, $take = null)
    {  
        $extracted_lines = [];

        if ( ($handle = fopen($this->csv_file, "r")) !== FALSE) {
            $line_counter = 1;
            $line_taken = 0;
            $has_exception = false;

            try{
                while (($line = fgetcsv($handle, 3000, $this->delimiter)) !== false) {
                    //first line as column
                    if($line_counter == 1){
                        $this->columns = $line;
                    } else{
                        //break if there's limit on take
                        if($take != null && $line_taken >= $take){
                            break;
                        }

                        //save line
                        if($line_counter > ($skip + 1)){
                            $extracted_lines[] = $line;
                            $line_taken++;
                        }
                    }
                    $line_counter++;
                }
            }catch(\Exception $ex){
                $has_exception = true;
            }finally{
                fclose($handle);    
            }

            if($has_exception){
                throw new \Exception("Unexpected error.");    
            }

        } else {
            // error opening the file.
            throw new \Exception("Unable to open csv file.");
        } 

        return $extracted_lines;
    }

    private function uploadLines($arrayofLines)
    {
        $columnized = [];
        foreach($arrayofLines as $line){
            $columnizedRow = [];
            foreach($this->columns as $key => $column){
                if(array_key_exists($key, $line) && $line[$key] != null && $line[$key] != ""){
                    $columnizedRow[$column] = $line[$key];    
                }
            }
            $columnized[] = $columnizedRow;
        }
        $this->info("Uploading " . count($columnized) . " rows"); 

        /*foreach($columnized as $key => $value){
            \Log::info($value);
            \App\GDSHelper::insertMany($this->kind, $value);
        }
        return true;*/ 

        $chunks = collect($columnized)->chunk(400);
        foreach($chunks as $key => $chunk){
            //\Log::info($chunk[0]);
            $this->info("Chunk " . ($key + 1) . "/" . count($chunks) . "..");
            \App\GDSHelper::insertMany($this->kind, $chunk->all());
        }
        $this->info("Uploaded");
        return true;
    }

    private function setProperties()
    {
        $this->csv_file = $this->argument('csv_file');
        $this->kind = $this->argument('kind');
        $this->skip = $this->option('skip') == null ? 0 : intval($this->option('skip'));
        $this->take = $this->option('take') == null ? null : intval($this->option('take'));
        $this->delimiter = $this->option('delimiter') == null ? "," : $this->option("delimiter");
    }

    private function csvFileExists()
    {
        return file_exists($this->csv_file);
    }


}

