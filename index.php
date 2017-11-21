<?php
    include 'settings.php'; //api key, table names, base id

    /*********
     * update_airtable - updates airtable with...
     *  $url = defines which base/tabele will be adjusted
     *  $type = add (POST) update (PATCH) or remove (DELETE)
     *  $json = key/value data
     *  $headers = headers and api authorization
     ***********/

    function update_airtable($url, $type, $json, $headers) 
    {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 10);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      $output = curl_exec($ch);
      //echo "OUTPUT:".$output."<BR />"; //return data
      curl_close($ch);
    }


    /*********
     * enqueTask - add a new entry to the task queue
     *  $name = task name
     *  $client = if for databse link to the client databse
     *  $notes = preloaded notes from prorotype queue
     *  
     ***********/

    function enqueTask($name, $client, $notes)
    {
        include 'settings.php'; //I suck at scope, why isn't this working without embedding this in each function?

        $url = $airtable_url_queue; //holds base an table name
        $type = 'POST';             //POST to add a row
        $headers = array('Authorization: Bearer ' . $api_key, 'Content-type: application/json'); //headers  api key auth
        $clientArray = array($client); //airtable support multiple liks in a table, so we need to put the client in an array
        $key = array('Name','Client', 'Notes'); //keys for columns to add data to
        $data = array("fields" => array( $key[0] => $name, $key[1] => $clientArray, $key[2] => $notes ) ); //data for columns
        $json = json_encode($data); //encdoe data to send via CURL
        update_airtable($url, $type, $json, $headers);
        usleep(201000); //we can only do 5 entries per second, 201000 micro seconds = 201 milliseconds, a little under 5 per second. TODO: use a rolling timer to mimiize wait time on this  i.e. keep track of the last time something was sent and wait AT MOST 200ms, since some time has already passed

    }

    function updateProtoTaskDate($id)
    {
        include 'settings.php'; //I suck at scope, why isn't this working without embedding this in each function?
        $key = 'Last Queued';   //we're going to update the last time the prototype task was queued
        $value = date("Y-m-d"); //update prototype task to current datw
        $url = $airtable_url . '/' . $id;   //url to send data to
        $type = 'PATCH';//patch = update
        $headers = array('Authorization: Bearer ' . $api_key, 'Content-type: application/json');//headers  api key auth
        $data = array("fields" => array( $key => $value ) ); //data to update
        $json = json_encode($data); //encdoe data to send via CURL
        update_airtable($url, $type, $json, $headers);
        usleep(201000); //we can only do 5 entries per second, 201000 micro seconds = 201 milliseconds, a little under 5 per second. TODO: use a rolling timer to mimiize wait time on this  i.e. keep track of the last time something was sent and wait AT MOST 200ms, since some time has already passed

    }

    //delete
    // function updateTaskQueueDate($id)
    // {
    //     include 'settings.php'; //I suck at scope, why isn't this working without embedding this in each function?
    //     $key = 'Snooze Date';   //we're going to update the last time the prototype task was queued
    //     $value = date("Y-m-d"); //update prototype task to current datw
    //     $url = $airtable_url_queue . '/' . $id;   //url to send data to
    //     $type = 'PATCH';//patch = update
    //     $headers = array('Authorization: Bearer ' . $api_key, 'Content-type: application/json');//headers  api key auth
    //     $data = array("fields" => array( $key => $value ) ); //data to update
    //     $json = json_encode($data); //encdoe data to send via CURL
    //     update_airtable($url, $type, $json, $headers);
    //     usleep(201000); //we can only do 5 entries per second, 201000 micro seconds = 201 milliseconds, a little under 5 per second. TODO: use a rolling timer to mimiize wait time on this  i.e. keep track of the last time something was sent and wait AT MOST 200ms, since some time has already passed

    // }

    function unsnoozeTask($id)
    {
        include 'settings.php'; //I suck at scope, why isn't this working without embedding this in each function?
        $key = 'Snooze Date';   //we're going to update the last time the prototype task was queued
        $value; //value is empty, we're removing the date
        $url = $airtable_url_queue . '/' . $id;   //url to send data to
        $type = 'PATCH';//patch = update
        $headers = array('Authorization: Bearer ' . $api_key, 'Content-type: application/json');//headers  api key auth
        $data = array("fields" => array( $key => $value ) ); //data to update
        $json = json_encode($data); //encdoe data to send via CURL
        update_airtable($url, $type, $json, $headers);
        usleep(201000); //we can only do 5 entries per second, 201000 micro seconds = 201 milliseconds, a little under 5 per second. TODO: use a rolling timer to mimiize wait time on this  i.e. keep track of the last time something was sent and wait AT MOST 200ms, since some time has already passed

    }





?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>NeilForce</title>
</head>
<body>

    <p>
        Script home for NeilForce. This script will be run via CRON regularly. We will

    </p>

    <ul>
        <li>Read all the entries from the prorotype task queue
        <li>Based on the entry's marked frequency, set a time interval (daily/weekly/ etc)
        <li>If enough time has passed since the last time the prototype task was triggered, make a copy over in the Task Queue
        <li>Update the prototype task entry to show we've copied over an entry

    </ul>

    <ul>
        Also once a week we need to 'unsnooze' 
    </ul>
    <p>

        In this script we need to...
        <br />
        Get data from prototype data
        <br /> Check math to see if its been long enough to add task to queued task
        <br /> add new task to queue
        <br/> Daily / hourly - move tasks to queue


    </p>   

    <p>
        TODO:
        <ul>
            <li>Clean up code + comment
			<li> Only copy prototypes that have a name frequency (and date?)
            <li>Clean up / delete task queue database and make a note in the client table of last date
                <ul>

                    <li>Get all tasks that are marked as complete
                    <li>look at task client
                    <li>Get last updated date of client
                    <li>if client date is older than task, update client
                    <li>Mark task as processed and or delete/move task
                    <li>OR Delete tasks that are older than X date or if there are more than Y tasks

                </ul>
            <li>delete old history after x(500-1000) tasks / recods because of airtables limits
            <li>we can only get 100 records at a time (in one call), need to figure out a good way to check if there are more than 100 prototype tasks and then do multiple calls. We might also be able to use views + filters to segment the calls
            <li>Move function calls to another php file / library
            <li>URL calls are a little inconsitent, need to standarize 
            <li>Move code block that gets prorotype task to a function (getProtoTask)
        </ul>
    </p>


    <?php


    echo 'Start processing <br/>';


    //move this block to a getProtoTask function
        $view =rawurlencode('App View'); //we need to define the view for the table
        $url = 'https://api.airtable.com/v0/' . $base . '/' . $table_prototype . '?maxRecords=1200&view='. $view;  //put the url together

        $headers = array(
            'Authorization: Bearer ' . $api_key
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        $entries = curl_exec($ch);
        curl_close($ch);
        $airtable_response = json_decode($entries, TRUE);


        $prototype_records = $airtable_response['records'];

        $offset = $airtable_response['offset'] ;

        while(!empty($offset ))
        {
             $view =rawurlencode('App View'); //we need to define the view for the table
            $url = 'https://api.airtable.com/v0/' . $base . '/' . $table_prototype . '?maxRecords=1200&view='. $view . '&offset='.$offset;  //put the url together

            $headers = array(
                'Authorization: Bearer ' . $api_key
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $url);
            $entries = curl_exec($ch);
            curl_close($ch);
            $airtable_response = json_decode($entries, TRUE);
            $offset = $airtable_response['offset'] ;

            $prototype_records= array_merge($prototype_records,$airtable_response[records]);

        }



    //move this block to a getQueueTask function
        $view =rawurlencode('App View'); //we need to define the view for the table
        $url = 'https://api.airtable.com/v0/' . $base . '/' . $table_queue . '?maxRecords=1200&view='. $view ;  //put the url together
        $headers = array(
            'Authorization: Bearer ' . $api_key
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        $entries = curl_exec($ch);
        curl_close($ch);
        $airtable_response_task_queue = json_decode($entries, TRUE);

        $task_queue_records = $airtable_response_task_queue['records'];


        unset($offset);
        $offset = $airtable_response_task_queue['offset'] ;

        while(!empty( $offset))
        {
            $view =rawurlencode('App View'); //we need to define the view for the table
            $url = 'https://api.airtable.com/v0/' . $base . '/' . $table_queue . '?maxRecords=1200&view='. $view . '&offset='.$offset;  //put the url together

            $headers = array(
                'Authorization: Bearer ' . $api_key
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $url);
            $entries = curl_exec($ch);
            curl_close($ch);
            $airtable_response_task_queue = json_decode($entries, TRUE);
            $offset = $airtable_response_task_queue['offset'] ;

            $task_queue_records= array_merge($task_queue_records,$airtable_response_task_queue[records]);
        }



    // //air table records response prototype concatenated
    // echo '<pre>';
    // print_r($prototype_records);
    // echo '</pre>';

    //air table records task response prototype concatenated
    // echo '<pre>';
    // print_r($task_queue_records);
    // echo '</pre>';

    // echo '<pre>';
    // print_r($airtable_response);
    // echo '</pre>';

    // echo '<pre>';
    // print_r($airtable_response_task_queue);
    // echo '</pre>';


    //travese through task queue to look for unsnoozers
    foreach($task_queue_records as $key => $value) 
    {
        $idTask = $value['id'];  //entry id for the record
        $nameTask = $value['fields']['Name'];        //name of task
        $snoozedDate = $value['fields']['Snooze Date']; //if this is snoozed or not;


            //date time objects that we'll use for difference
            $lastSnoozeTime = new DateTime($snoozedDate); 
            $currentTime = new DateTime(date('Y-m-d', time())); 

            $interval = $lastSnoozeTime->diff($currentTime); //difference between protoype queue task + now


            $dateAhead = $interval-> d;


        //echo $nameTask . ' ' .$snoozed . ' ' . $snoozedDate . ' ' . $dateAhead. '<br />';

            if($dateAhead >= 1)
            {
                unsnoozeTask($idTask);
            }





    }


    //travese through protoype task 
    foreach($prototype_records as $key => $value) 
    {

        $idToQueue = $value['id'];  //entry id for the record. Need this to update the prorotype task record later
        $protoTaskFreq = $value['fields']['Frequency']; //text of the frequnecy to repopulate task
        $nameToQueue = $value['fields']['Name'];        //name of task
        $clientToQueue = $value['fields']['Client'][0]; //record id of client associated with task
        $notesToQueue = $value['fields']['Notes'];      //pre-populate notes
        $lastQueueTime = $value['fields']['Last Queued']; //yyyy-mm-dd time of last time we copied this entry to queue
        $currentTime = time();                          //current yyyy-mm-dd 



        //date time objects that we'll use for difference
        $lastQueueDateTime = new DateTime($lastQueueTime); 
        $currenTime = new DateTime(date('Y-m-d', time())); 

        $interval = $lastQueueDateTime->diff($currenTime); //difference between protoype queue task + now
        // echo '<pre>';
        // print_r($interval);
        // echo '</pre>';
        
        if(!empty($nameToQueue))
        {
            if ($protoTaskFreq == "Daily")
            {
                echo 'test';
             

                $dateAhead = $interval -> d;
               
                if ($dateAhead >= 1 || !$lastQueueTime )
                {   
                    echo 'Processing and copying '. $protoTaskFreq. ' task '.  $nameToQueue . '<br />';
                    enqueTask($nameToQueue, $clientToQueue, $notesToQueue);
                    updateProtoTaskDate($idToQueue);
                }


                
            }
            elseif($protoTaskFreq == "Weekly")
            {

                $dateAhead = $interval-> d;
               
                if ($dateAhead >= 7 || !$lastQueueTime )
                {
                    echo 'Processing and copying '. $protoTaskFreq. ' task '.  $nameToQueue . '<br />';
                    enqueTask($nameToQueue, $clientToQueue, $notesToQueue);
                    updateProtoTaskDate($idToQueue);
                }

            }
            elseif($protoTaskFreq == "Every 2 Weeks")
            {
                $dateAhead = $interval-> d;
               
                if ($dateAhead >= 14 || !$lastQueueTime )
                {
                    echo 'Processing and copying '. $protoTaskFreq. ' task '.  $nameToQueue . '<br />';
                    enqueTask($nameToQueue, $clientToQueue, $notesToQueue);
                    updateProtoTaskDate($idToQueue);
                }

            }
            elseif($protoTaskFreq == "Monthly")
            {
                $dateAhead = $interval-> m;
               
                if ($dateAhead >= 1 || !$lastQueueTime )
                {
                    echo 'Processing and copying '. $protoTaskFreq. ' task '.  $nameToQueue . '<br />';
                    enqueTask($nameToQueue, $clientToQueue, $notesToQueue);
                    updateProtoTaskDate($idToQueue);
                }

            }
            elseif($protoTaskFreq == "Quarterly")
            {
                $dateAhead = $interval-> m;
               
                if ($dateAhead >= 3 || !$lastQueueTime )
                {
                    echo 'Processing and copying '. $protoTaskFreq. ' task '.  $nameToQueue . '<br />';
                    enqueTask($nameToQueue, $clientToQueue, $notesToQueue);
                    updateProtoTaskDate($idToQueue);
                }

            }
            elseif($protoTaskFreq == "Yearly")
            {
                $dateAhead = $interval-> m + (12 * $interval-> y);
               
                if ($dateAhead >= 12 || !$lastQueueTime )
                {
                    echo 'Processing and copying '. $protoTaskFreq. ' task '.  $nameToQueue . '<br />';
                    enqueTask($nameToQueue, $clientToQueue, $notesToQueue);
                    updateProtoTaskDate($idToQueue);
                }

            }
        }
    }

    echo 'Processed';

   ?>


</body>
</html>
