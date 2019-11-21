<?php
class Scrapping
{
    private $username;
    private $password;
    private $response;
    private $conn;
    private $crewid;
    private $crewpassword;

    public function __construct($username,$password,$crewid,$crewpass)
    {
                    $this->username = $username;
                    $this->password = $password;
                    $this->crewid = $crewid;
                    $this->crewpassword = $crewpass;
    }

    public function getMethod($url, $headerdata = false)
    {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_HEADER, true);
                    if($headerdata)
                    {
                                 curl_setopt($ch, CURLOPT_HTTPHEADER,$headerdata);
                    }
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST ,"GET");
                    $this->response = curl_exec($ch);
                    curl_close($ch);
                    $header = $this->parseHeader();
                    return ['data' => $this->response , 'header' => $header ];
    }

    public function postMethod($url,$headerdata = false,$getdata = false,$date = false)
    {
              if($getdata == 'getdata'){
                    $postdata = 'CHK=&CR=&UNO=&nDays=31&times_format=1&EXITBTN=&VER=&Published=0&nDaysmobile=31&times_formatmobile=1&cal1='.urlencode($date).'&nDaysnormal=31&times_formatnormal=1';
              }elseif($getdata == 'verify'){
                            $postdata = "Crew_Psw=NaN&Crew_Id=".urlencode(base64_encode($this->crewid))."&CHK=&CR=&UNO=&EXITBTN=&VER=&LANG_CODE=&LANG_ISO=&UserAgent=Chrome&AD=&Crm=".$this->crewpassword."&Ids=";
                    }elseif($getdata == 'flightinfo'){
                           $postdata = 'hScreen=&SCR=&_flagy=&DoVac=0&Oper=1&CHK=&CR=&UNO=&EXITBTN=&VER=&MAC=&LCODE=&eReferrer=ecrew.aerlingus.com&hCat=&gen_dec_rep=&gen_dec_day=&gen_dec_route=&eCrewIsLockedDuetoPendingNotifs=0';
                    }elseif ($getdata == 'flightsdata') {
                      $date = str_replace('"', '', $date);
                      $postdata = 'AjaxOperation=2&cal1='.$date.'&Airport=&ACRegistration=&Deps=0&Flight=&times_format=1';
                    }
                    elseif($getdata == 'whoisonboard'){
                      $date = str_replace('"', '', $date);
                              $postdata = 'AjaxOperation=2&cal1='.$date.'&Airport=&ACRegistration=&Deps=0&Flight=&times_format=1';
                    }else{
                            $postdata = "login=".$this->username."&passwd=".$this->password;
                    }
                 $ch = curl_init();
                 curl_setopt($ch, CURLOPT_HEADER, true);
                 if($headerdata)
                    {
                                 curl_setopt($ch, CURLOPT_HTTPHEADER,$headerdata);
                    }
                 curl_setopt($ch, CURLOPT_URL, $url);
                 curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                 curl_setopt($ch, CURLOPT_POST ,1);
                 curl_setopt($ch, CURLOPT_POSTFIELDS,$postdata);
                    $this->response = curl_exec($ch);
                //  print_r(curl_getinfo($ch));
                    curl_close($ch);
                    $header = $this->parseHeader();
                    return ['data' => $this->response , 'header' => $header ];
    }

    public function parseHeader()
    {
                 $headers = array();
        $header_text = substr($this->response, 0, strpos($this->response, "\r\n\r\n"));
        foreach (explode("\r\n", $header_text) as $i => $line)
if ($i === 0)
    $headers['http_code'] = $line;
else
{
    list ($key, $value) = explode(': ', $line);
    if($key == 'Set-Cookie')
    {
                    $headers[$key][] = $value;
    }
    else
    {
                $headers[$key] = $value;
    }
}
        return $headers;
    }

    public function dbConnection($servername,$username,$password,$dbname)
    {
                $this->conn = new mysqli($servername, $username, $password,$dbname);
                // Check connection
       if ($this->conn->connect_error) {
           die("Connection failed: " . $this->conn->connect_error);
          }
    }
    public function insertDb($data,$crew_id,$json)
    {
                try {
                    $res = $this->conn->query("INSERT INTO crew(`crew_id`, `date`,`jsondata`) VALUES('".$crew_id."','".$data."','".$json."') ");
                } catch (\Exception $e) {
                  echo $e->getMessage();
                }


                // if($res === true)
                // {
                //        echo "Data Inserted";
                // }
    }
    public function insertScheduleData($crew_id, $data)
    {
      $recordType = '';
      try {
        $query = "SELECT * FROM `rosters` WHERE `is_updated`=0 and `roster_date`='".$data['date']."'";
        $result = $this->conn->query($query);
        $currentRecord = $result->fetch_assoc();
        // if row exists
        if($result->num_rows > 0){
          $recordType = 'update';
        }else{
          //insert new row
          //if ($data['roster_type'] == 'daysoff') {
          if ($data['roster_type'] == 'Days Off' || $data['roster_sub_type'] == 'BLANK') {
            $sql = "INSERT INTO rosters(`crew_id`,`roster_sub_type`,`roster_date`,`flight_info`,`data`,`roster_type`) VALUES('".$crew_id."','".$data["roster_sub_type"]."','".$data["date"]."','".$data["flight_info"]."','".$data["jsondata"]."', '".$data["roster_type"]."')";
          }else {
            $sql = "INSERT INTO rosters(`crew_id`,`roster_sub_type`,`roster_date`,`flight_info`,`data`,`roster_type`) VALUES('".$crew_id."','".$data["roster_sub_type"]."','".$data["date"]."','".$data["flight_info"]."','".$data["jsondata"]."', '".$data["roster_type"]."')";
          }
          $res = $this->conn->query($sql);
          $recordType = 'insert';
        }

        if($recordType=='update'){
          //Check if there is any change in old data
          if($currentRecord['crew_id']==$crew_id && $currentRecord['roster_sub_type']==$data["roster_sub_type"] && $currentRecord['roster_type']==$data["roster_type"])
          {
            //Check change in flight info data
            $oldFlightData =  json_decode($currentRecord['flight_info'],TRUE);
            $newFlightData =  json_decode($data['flight_info'],TRUE);
            $i=0;
            $change = 0;
            foreach($oldFlightData as $flight){
              $diff = array_diff($flight,$newFlightData[$i]);
              if(!empty($diff)){
                $change = 1;
              }
              $i++;
            }
            if($change==1){
              $affectedRows = 1;  
            }else{
              $affectedRows = 0;  
            }
          }else{
            $affectedRows = 1;
          }
          // if data is changed than old
          if($affectedRows > 0){
            $rosterId = $currentRecord['id'];
            $sql = "UPDATE `rosters` SET `is_updated`=1 WHERE `id`=".$currentRecord['id'];
            $res = $this->conn->query($sql);
            // Get All Swap Request of this roster
            $sql = "SELECT * FROM `swap_requests` WHERE `roster_id`=".$rosterId;
            $requestData = $this->conn->query($sql);
            while($row = $requestData->fetch_assoc()) {
              $request_id = $row["id"];
              //Delete message of this swap request id
              $sql = "UPDATE `messages` SET `is_deleted`=1 WHERE `swap_request_id`=".$request_id;
              $this->conn->query($sql);
            }
            $sql = "UPDATE `swap_request_exchanges` SET `is_deleted`=1 WHERE `roster_id`=".$rosterId;
            $this->conn->query($sql);
            $sql = "UPDATE `swap_requests` SET `is_deleted`=1 WHERE `roster_id`=".$rosterId;
            $this->conn->query($sql);
            //Insert new record of updated roster
            if ($data['roster_type'] == 'Days Off' || $data['roster_sub_type'] == 'BLANK') {
              $sql = "INSERT INTO rosters(`crew_id`,`roster_sub_type`,`roster_date`,`flight_info`,`data`,`roster_type`) VALUES('".$crew_id."','".$data["roster_sub_type"]."','".$data["date"]."','".$data["flight_info"]."','".$data["jsondata"]."', '".$data["roster_type"]."')";
            }else {
              $sql = "INSERT INTO rosters(`crew_id`,`roster_sub_type`,`roster_date`,`flight_info`,`data`,`roster_type`) VALUES('".$crew_id."','".$data["roster_sub_type"]."','".$data["date"]."','".$data["flight_info"]."','".$data["jsondata"]."', '".$data["roster_type"]."')";
            }
            $this->conn->query($sql);
          }
        }
      } catch (\Exception $e) {
        echo $e->getMessage(); die("Hi error here");
      }
    }
    public function insertMemebersQuery($insData)
    {
      try {
        $value = implode(", ",array_values($insData));
        $sql = "INSERT INTO crew_members(`flight_number`,`flight_date`,`member_id`,`name`,`base`,`ac`,`pos`,`py`,`status`) VALUES(
          '".$insData["flight_number"]."','".$insData["flight_date"]."','".$insData["member_id"]."','".$insData["name"]."','".$insData["base"]."','".$insData["ac"]."','".$insData["pos"]."','".$insData["py"]."','".$insData["status"]."')";
        $res = $this->conn->query($sql);
      } catch (\Exception $e) {
        echo $e->getMessage(); die("Hi error here");
      }

    }
    public function insertcsv($data)
    {
        $sql = "INSERT INTO flights(`flight_number`,`departure_code`,`arrival_code`,`departure_time`,`landing_time`,`category`,`status`) VALUES('".$data[0]."','".$data[1]."','".$data[2]."','".$data[3]."','".$data[4]."','".$data[5]."','1') ";
                $res = $this->conn->query($sql);
    }
    public function getData($table,$fk,$val)
    {
        $sql = "SELECT * from `".$table."` where `".$fk."` = '".$val."'";
        try {
          $res = $this->conn->query($sql);
        } catch (\Exception $e) {
          return $e;
        }
        if ($res->num_rows > 0) {
          $result = [];
          while($row=mysqli_fetch_assoc($res))
          {
            $result[]=$row;
          }
          return $result;
        }
        $this->$conn->close();
    }

    /*
    Function to get crew members data

    */
    public function get_crew_members($table_data,$id)
    {
      $dom = new DOMDocument();
      @$dom->loadHTML($table_data);
      // get the third table
      $table = $dom->getElementsByTagName('table')->item(0);
      $trs = $table->childNodes;
      foreach ($trs as $key => $tr) {
        $tds = $tr->childNodes;
        $flight_number =$tds->item(0)->nodeValue;
        $flight_number = htmlentities($flight_number, null, 'utf-8');
        $flight_number = str_replace("&nbsp;", "", $flight_number);
        $flight_number = html_entity_decode($flight_number);
      //  echo "FLight Number: ".$flight_number."\n"."Id: ".$id;
        //echo $id;
        if (trim($flight_number) == (string)$id) {
          echo $flight_number;
          foreach ($tds as $key => $td) {
            if ($key > 18) {
              /*
              Codes for getting the crew members data
              19: Route Number,
              20: route_day
              22: displayed_date
              21 : SROW

              */
              // $td->nodeValue;
              $flight['route_no'] = $tds->item(19)->nodeValue;
              $flight['route_day'] = $tds->item(21)->nodeValue;
              $flight['SROW'] = $tds->item(23)->nodeValue;
              $flight['displayed_date'] = $tds->item(25)->nodeValue;
            }

          }
          return $flight;

        }

      //  echo $tds->item(0)->nodeValue."/n";
      }
      //echo "<pre>"; print_r($first_row);

    }

    public function validateDate($date)
    {
    $tempDate = explode('-', $date);
    // checkdate(month, day, year)
    return checkdate($tempDate[1], $tempDate[2], $tempDate[0]);
    }
}