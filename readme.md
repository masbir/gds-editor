# Google Cloud Datastore Web Editor and CSV Importer
 

## Web Example

http://dns.lancerastaging.com/ 

## Set-up

Add the following lines to your .env file : 

```
PROJECT_ID=GOOGLE_PROJECT_ID
GDS_KEY_FILE=PATH_TO_JSON_KEY_FILE_UNDER_STORAGE
```

## CSV Importer Command

In the project folder run ` php artisan datastore:import /path/to/csv datastore-kind-name --skip=0 --take=20`

 - replace `/path/to/csv` with the path to csv file. 
 - replace `datastore-kind-name` with datastore [kind](https://cloud.google.com/appengine/docs/python/datastore/entities#Python_Kinds_and_identifiers) name
 - `skip` (optional) - file reading starts on line. The default is 0 which means file be read from the beginning.
 - `take` (optional) - number of lines to read after `skip`. default is null which means all lines after `skip` will be read.