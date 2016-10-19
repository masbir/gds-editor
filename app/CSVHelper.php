<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CSVHelper extends Model
{
    public static function readFile($filePath, $closure, $delimeter = ',')
    {
        if ( ($handle = fopen($filePath, "r")) !== FALSE) {
            try{
                $line_counter = 1;
                while (($line = fgetcsv($handle, 3000, $delimeter)) !== false) {
                    $closure_result = $closure($line_counter, $line);
                    if($closure_result === false){
                        break;
                    }
                    $line_counter++;
                }
                return true;
            }catch(\Exception $ex){ 
            }finally{
                fclose($handle);    
            }
            return false;
        }else{
            return false;
        }
    }

    public static function getSampleRow($file, $delimiter = ',', $hasHeader = true)
    {
        $sampleRow = null;
        $header = null;
        static::readFile($file, function($line_counter, $line) use ($hasHeader, &$sampleRow, &$header) {
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
                return false;
            }
        }, $delimiter);

        if($sampleRow != null) $sampleRow = array_map('trim', $sampleRow);
        if($header != null) $header = array_map('trim', $header); 
   		return compact('sampleRow', 'header');
    }
}
