<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CSVHelper extends Model
{
    public static function getSampleRow($file, $delimiter = ',', $hasHeader = true)
    {
    	$sampleRow = null;
        $header = null;
    	if ( ($handle = fopen($file, "r")) !== FALSE) { 

    		 try{
    		 	$line_counter = 0;
                while (($line = fgetcsv($handle, 3000, $delimiter)) !== false) { 
                	$line_counter++;
                    if($hasHeader){
                        if($line_counter == 1){
                            $header = $line;
                        }else{
                            $sampleRow = $line;
                        }
                    }else{
                        if($line_counter == 1){
                            $header = $line;
                            $sampleRow = $line;
                        }
                    }


                	//stop once we have sampleRow
                	if($sampleRow != null){
                		break;
                	}
                }

                //trim
                $sampleRow = array_map('trim', $sampleRow);
                $header = array_map('trim', $header);
            }catch(\Exception $ex){

            }finally{
                fclose($handle);    
            }
    	}
   		return compact('sampleRow', 'header');
    }
}
