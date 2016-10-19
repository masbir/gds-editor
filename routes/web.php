<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

/*Route::get('/', function () {
    return view('welcome');
});
Route::get('/test', 'HomeController@index');*/
Route::get('/', 'SchemaController@index');
Route::get('/kinds/insert', 'SchemaController@insert');
Route::post('/kinds/insert', 'SchemaController@postInsert');

Route::get('/kinds/import', 'SchemaController@importCSV');
Route::post('/kinds/import', 'SchemaController@postImportCSV');
Route::get('/kinds/import/map', 'SchemaController@mapImportedCSV');
Route::post('/kinds/import/map', 'SchemaController@postMapImportedCSV');
Route::get('/kinds/import/cancel', 'SchemaController@cancelImport');

Route::get('/kinds/{name}', 'SchemaController@kind'); 
Route::get('/kinds/{name}/insert', 'SchemaController@insert');
Route::post('/kinds/{name}/insert', 'SchemaController@postInsert');
