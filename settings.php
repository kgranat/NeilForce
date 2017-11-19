<?php

$api_key = 'wouldntyouliketoknow'; // add your api key
$base = 'apprBpJq6EwtnZmXa'; // add your base (spreadsheet_ID)
$table_prototype = rawurlencode('Prototype Tasks'); // add your table name
$table_queue = rawurlencode('Task Queue'); // add your table name
$table_client = rawurlencode('Clients'); // add your table name
$airtable_url =        'https://api.airtable.com/v0/' . $base . '/' . $table_prototype;
$airtable_url_queue =  'https://api.airtable.com/v0/' . $base . '/' . $table_queue;
$airtable_url_client = 'https://api.airtable.com/v0/' . $base . '/' . $table_client;
