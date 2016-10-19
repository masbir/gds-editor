<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UploadData implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    private $kind;
    private $data;
    private $id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($kind, $data, $id)
    {
        $this->kind = $kind;
        $this->data = $data;
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        sleep(10);
        //\App\GDSHelper::insertMany($this->kind, $this->data);

        $counter = new \App\UploadDataCounter();
        $counter->id = $this->id; 
        $counter->retrieveFromCache();
        $counter->processed = intval($counter->processed) + 1;
        if(intval($counter->total) == intval($counter->processed)){
            $counter->removeCache();
        }else{
            $counter->saveCache();    
        }
        
    }
}
