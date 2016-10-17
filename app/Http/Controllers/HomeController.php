<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class HomeController extends Controller
{
    public function index()
    { 
    	$prop1 = ["Name" => "John Doe"];
        $prop2 = ["Name" => "Peter Parker", "PseudoName" => "Spiderman"];
        $entities = \App\GDSHelper::insertMany("SampleSchema", [$prop1, $prop2]);
        dd($entities);
    }
}
