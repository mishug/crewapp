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
                    }elseif($getdata == 'whoisonboard'){
                              $postdata = 'AjaxOperation=2&cal1=21/06/2019&Airport=&ACRegistration=&Deps=0&Flight=&times_format=1';
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
                if (@($data && $data['daysoff'])) {
                  $sql = "INSERT INTO rosters(`crew_id`,`daysoff`,`date_from`,`date_to`,`flight_info`,`data`,`roster_type`) VALUES('".$crew_id."','".$data["daysoff"]."','".$data["date"]."','".$data["date"]."','{}','".$data["jsondata"]."', '".$data["type"]."')";
                  $res = $this->conn->query($sql);

                }elseif(@($data && $data['flight'])) {
                  $sql = "INSERT INTO rosters(`crew_id`,`daysoff`,`date_from`,`date_to`,`flight_info`,`data`,`roster_type`) VALUES('".$crew_id."','','".$data["date"]."','".$data["date"]."','".$data["flight"]."','".$data["jsondata"]."','".$data["type"]."')";
                  $res = $this->conn->query($sql);
                }

                // if($res === true)
                // {
                //        echo "Data Inserted";
                // }else {
                //   echo "<pre>"; print_r($res);
                // }
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
}
