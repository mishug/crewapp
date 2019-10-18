<?php
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors',true);
error_reporting(E_ALL);

require_once 'lib.php';
include_once 'config.php';
session_start();

if (isset($_GET['username']) && isset($_GET['password']) && $_GET['crew_id'] && $_GET['crew_password']) {
	$params['username'] = $_GET['username'];
	$params['password'] = $_GET['password'];
	$params['crew_id'] = $_GET['crew_id'];
	$params['crew_password'] = $_GET['crew_password'];
}else {
	echo json_encode(['message'=>'missing parameters','error'=>true]);
	exit;
}
$url="https://portal.aerlingus.com/";
$scrap = new Scrapping($params['username'], $params['password'],$params['crew_id'],md5($params['crew_password']));
$date = date("d-m-Y", strtotime('-1 day'));
$year = date('Y', strtotime($date));

// if ($_SESSION['htmldata']) {
// 	$resp['data'] = $_SESSION['htmldata']['data'];
// //	session_unset($_SESSION['htmldata']);
// }else {
	$resp = $scrap->getMethod($url); // first time visit home page

	$url = $resp['header']['Location'];
	$resp = $scrap->getMethod($url); // location

	$url = 'https://nsauth4.aerlingus.com/vpn/tmindex.html';
	$cookie = explode(';',$resp['header']['Set-Cookie'][0]);
	$header = array(
						'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
						'Accept-Encoding: gzip, deflate, br',
	     'Cookie: '.$cookie[0],
	     'Host: nsauth4.aerlingus.com',
	     'Upgrade-Insecure-Requests: 1',
	     'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36'
	 );
	$resp = $scrap->getMethod($url,$header);

	$url = 'https://nsauth4.aerlingus.com/cgi/login';

	$header = array(
	    'Cookie: '.$cookie[0],
	    'Host: nsauth4.aerlingus.com',
	    'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36',
	    'Origin: https://nsauth4.aerlingus.com',
	    'Referer: https://nsauth4.aerlingus.com/vpn/tmindex.html',
	    'Upgrade-Insecure-Requests: 1',
	    'Content-Type: application/x-www-form-urlencoded'
	);

	$resp = $scrap->postMethod($url,$header,'login');

	$url = $resp['header']['Location'];
	$header = array(
						'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
						'Accept-Encoding: gzip, deflate, br',
	     'Cookie: '.$cookie[0],
	     'Host: portal.aerlingus.com',
	     'Upgrade-Insecure-Requests: 1',
	     'Referer: https://nsauth4.aerlingus.com/vpn/tmindex.html',
	     'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36'
	 );
	$resp = $scrap->getMethod($url,$header);

	$cookie1 = explode(';',$resp['header']['Set-Cookie'][0]);
	$cookie2 = explode(';',$resp['header']['Set-Cookie'][1]);

	$url = 'https://portal.aerlingus.com/web/Login';

	$header = array(
						'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
						'Accept-Encoding: gzip, deflate, br',
	     'Cookie: '.$cookie1[0].'; '.$cookie2[1],
	     'Host: portal.aerlingus.com',
	     'Upgrade-Insecure-Requests: 1',
	     'Referer: https://portal.aerlingus.com/',
	     'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36'
	 );
	$resp = $scrap->getMethod($url,$header);
	$JSESSIONID = explode(';',$resp['header']['Set-Cookie'][0]);


	$url = 'https://portal.aerlingus.com/web/Portal/Operations%20Portal/Cabin%20Crew/Home';
	$header = array(
						'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
						'Accept-Encoding: gzip, deflate, br',
	     'Cookie: '.$cookie1[0].'; '.$cookie2[0].'; '.$JSESSIONID[0],
	     'Host: portal.aerlingus.com',
	     'Upgrade-Insecure-Requests: 1',
	     'Referer: https://portal.aerlingus.com/',
	     'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36'
	 );
	$resp = $scrap->getMethod($url,$header);

	$url = 'https://ecrew.aerlingus.com/wtouch/wtouch.exe/index?MAC=0&VER=1';
	$header = array(
						'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
						'Accept-Encoding: gzip, deflate, br',
	     'Cookie: '.$cookie1[0].'; '.$cookie2[0],
	     'Host: ecrew.aerlingus.com',
	     'Upgrade-Insecure-Requests: 1',
	     'Referer: https://portal.aerlingus.com/',
	     'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36'
	 );
	$resp = $scrap->getMethod($url,$header);

	$url = 'https://ecrew.aerlingus.com/wtouch/wtouch.exe/verify';
	$header =array(
	    'Cookie: '.$cookie1[0].'; '.$cookie2[0],
	    'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36',
	    'Content-Type: application/x-www-form-urlencoded',
	    'Host: ecrew.aerlingus.com',
	    'Origin: https://ecrew.aerlingus.com',
	    'Referer: https://ecrew.aerlingus.com/wtouch/wtouch.exe/index?MAC=0&VER=1',
	    'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
	    'Accept-Language: en-US,en;q=0.9'
	);

	$resp = $scrap->postMethod($url,$header,'verify');
	if (strpos($resp['data'], 'Login was unsuccessful') !== false) {
		echo json_encode(['message'=>'Login failed','error'=>true]);
		exit;
	}

	$cookie3 = explode(';',$resp['header']['Set-Cookie'][0]);
	$cookie4 = explode(';',$resp['header']['Set-Cookie'][1]);
	$cookie5 = explode(';',$resp['header']['Set-Cookie'][2]);
	$cookie6 = explode(';',$resp['header']['Set-Cookie'][3]);
	$cookie7 = explode(';',$resp['header']['Set-Cookie'][4]);


	$url = 'https://ecrew.aerlingus.com/wtouch/perinfo.exe/index';
	$header = array(
						'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
						'Accept-Encoding: gzip, deflate, br',
	     'Cookie: '.$cookie3[0].'; '.$cookie4[0].'; '.$cookie5[0].'; '.$cookie6[0].'; '.$cookie7[0].'; '.$cookie1[0].'; '.$cookie2[0],
	     'Host: ecrew.aerlingus.com',
	     'Upgrade-Insecure-Requests: 1',
	     'Referer: https://portal.aerlingus.com/',
	     'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36'
	 );
	$resp = $scrap->getMethod($url,$header);


	$url = 'https://ecrew.aerlingus.com/wtouch/fltinfo.exe/index';
	$header = array(
						'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
						'Accept-Encoding: gzip, deflate, br',
	     'Cookie: '.$cookie3[0].'; '.$cookie4[0].'; '.$cookie5[0].'; '.$cookie6[0].'; '.$cookie7[0].'; '.$cookie1[0].'; '.$cookie2[0],
	     'Host: ecrew.aerlingus.com',
	     'Upgrade-Insecure-Requests: 1',
	     'Referer: https://portal.aerlingus.com/',
	     'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36'
	 );
	$resp = $scrap->postMethod($url,$header,'flightinfo');


	$url = 'https://ecrew.aerlingus.com/wtouch/fltinfo.exe/AjAction'; // getflight info
	$header = array(
						'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
						'Accept-Encoding: gzip, deflate, br',
	     'Cookie: '.$cookie3[0].'; '.$cookie4[0].'; '.$cookie5[0].'; '.$cookie6[0].'; '.$cookie7[0].'; '.$cookie1[0].'; '.$cookie2[0],
	     'Host: ecrew.aerlingus.com',
	     'Upgrade-Insecure-Requests: 1',
	     'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36'
	 );
	$resp = $scrap->postMethod($url,$header,'whoisonboard',$date);
	$flight_crew_members_api = $scrap->postMethod($url,$header,'flightsdata',$date);
	$flight_crew_members = $flight_crew_members_api['data'];
	$cookiecat = explode(';',$resp['header']['Set-Cookie'][0]);
	$url = 'https://ecrew.aerlingus.com/wtouch/perinfo.exe/crwsche';
	$header = array(
						'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
						'Accept-Encoding: gzip, deflate, br',
	     'Cookie: '.$cookie3[0].'; '.$cookie4[0].'; '.$cookie5[0].'; '.$cookie6[0].'; '.$cookie7[0].'; '.$cookie1[0].'; '.$cookie2[0].'; SCR=1B;',
	     'Host: ecrew.aerlingus.com',
	     'Origin: https://ecrew.aerlingus.com',
	     'Upgrade-Insecure-Requests: 1',
	     'Referer: https://ecrew.aerlingus.com/wtouch/perinfo.exe/oper',
	     'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36'
	 );

	$resp = $scrap->postMethod($url,$header,'getdata',$date);

	$cookie8 = explode(';',$resp['header']['Set-Cookie'][1]);
	$cookie9 = explode(';',$resp['header']['Set-Cookie'][3]);
	$cookie10 = explode(';',$resp['header']['Set-Cookie'][4]);

	$url = 'https://ecrew.aerlingus.com/wtouch/perinfo.exe/waMin1Body';
	$header = array(
						'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
						'Accept-Encoding: gzip, deflate, br',
	     'Cookie: '.$cookie3[0].'; '.$cookie4[0].'; '.$cookie5[0].'; '.$cookie6[0].'; '.$cookie7[0].'; '.$cookie1[0].'; '.$cookie2[0].'; '.$cookie8[0].' SCR=1B; STATUS=0; '.$cookie9[0].'; Report='.$cookie10[0],
	     'Host: ecrew.aerlingus.com',
	     'Upgrade-Insecure-Requests: 1',
	     'Referer: https://portal.aerlingus.com/',
	     'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36'
	 );
	$resp = $scrap->getMethod($url,$header);
	$_SESSION['htmldata'] = $resp;
//}
$dom = new DOMDocument();
@$dom->loadHTML($resp['data']);
echo $resp['data'];
// get the third table
$thirdTable = $dom->getElementsByTagName('table')->item(0);
$node = $thirdTable->firstChild;
$data = [];
$daysoff_cat = ["F",">F","DL","WR","PR","LPR","GL","CAL","BH","WD","PRL","PIW","WTA","DD","SL","SR"];
$standoff_cat = ["APS","RAS","APW","APWA","APWB","SBX","SBXL","SBY","SBYA","SBYB","SBYC","SBYD","SW","SWA","SWB","SH","BSBY","SD"];
$training_cat = [
	"GSP","OSD","SEPR","SEP2","SEP3","SEP4","SEP5","SEP6","SEP7","SEP8","IT","MHT","ICRM","ISEC","FAO","A320","BSBY","SD","TABA","S75A","BCT","ISCC","CST","TSEN","IW"
];
$airport_codes = ["ORD","DUB","LGW","NOC","PRG","LHR","JFK","AMS","BHD","SEA","IAD"];
$month_array = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
$days_array = ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"];
do {
	$child_nodes = $node->childNodes;
	$child_data = [];
	foreach ($child_nodes as $key => $value) {
		$tdtext = $child_nodes->item($key)->nodeValue;
		$child_data[] = $tdtext;
	}
	$data[] = $child_data;

//	echo $node->nodeValue;
} while ($node = $node->nextSibling);

$dataarray = [];
for ($i=0; $i < count($data); $i++) {
	foreach ($data as $key => $value) {
		if ($key > 5 && isset($data[5][$i]) && $data[5][$i] != '') {
			$date_str = $data[5][$i];
		 $date = preg_replace("/[^0-9]/", "", $date_str );
		 if ($date) {
			 $date_arr = explode($date,$date_str);
  		 $month_number = array_search($date_arr[0], $month_array);
  		 $month_number = (int)$month_number + 1;
  		 $date_form = date("Y-m-d",strtotime($year.'-'.$month_number.'-'.$date));
  			@$dataarray[$date_form][] = str_replace("\r\n","",$value[$i]);
		 }

		}

	}
}
	// Save crew data
	// $scrap->dbConnection('localhost','root','root','crewapp');
	// foreach($dataarray as $k=>$datas){
	// 			$date_str = $k;
	// 		 $date = preg_replace("/[^0-9]/", "", $date_str );
	// 		 $date_arr = explode($date,$date_str);
	// 		 $month_number = array_search($date_arr[0], $month_array);
	// 		 $date_form = date("Y-m-d",strtotime($year.'-'.$month_number.'-'.$date));
	//
	// 		$scrap->insertDb($date_form,$crew_id,json_encode($datas));
	// }
$new_arr = [];
//days loop
//echo "<pre>"; print_r($data); exit();
for ($i=0; $i < 31; $i++) {
	//if($i < 29 ) continue;
	$new_arr[$i] = [];
	$filght_count = 0;
	//echo $data[5][$i];
//	for ($j=5; $j < count($data); $j++) {
	$date_str = $data[5][$i];
	$date = preg_replace("/[^0-9]/", "", $date_str );
	$date_arr = explode($date,$date_str);
	$month_number = array_search($date_arr[0], $month_array);
	$month_number = (int)$month_number + 1;
		if (@$data[5] && @$data[5][$i] && $date) {
			$date_string = date("Y-m-d",strtotime($year.'-'.$month_number.'-'.$date));
			@$new_arr[$i]['date'] = $date_string;
		//	$j++;
		}
		if (@$data[6] && @is_numeric($data[6][$i])) {
			$new_arr[$i]['flight'][$filght_count]['number'] = $data[6][$i];
			$new_arr[$i]['roster_type'] = 'flight';
			}elseif (@$data[6] && in_array($data[6][$i],$daysoff_cat)) {
			@$new_arr[$i]['roster_sub_type'] = $data[6][$i];
			@$new_arr[$i]['roster_type'] = 'daysoff';
			continue;
		}elseif (@$data[6] && in_array($data[6][$i],$standoff_cat)) {
			@$new_arr[$i]['roster_sub_type'] = $data[6][$i];
			@$new_arr[$i]['duty_start'] = $data[7][$i];
			@$new_arr[$i]['duty_end'] = $data[8][$i];
			@$new_arr[$i]['roster_type'] = 'standoff';
			continue;
		}elseif (@$data[6] && in_array($data[6][$i],$training_cat)) {
			@$new_arr[$i]['roster_sub_type'] = $data[6][$i];
			@$new_arr[$i]['duty_start'] = $data[7][$i];
			@$new_arr[$i]['flight_start'] = $data[8][$i];
			@$new_arr[$i]['flight_end'] = $data[9][$i];
			@$new_arr[$i]['duty_end'] = $data[10][$i];
			@$new_arr[$i]['roster_type'] = 'training';
			continue;
		}


		elseif ($data[6] == 'PYRD') {
			$new_arr[$i]['roster_type'] = 'flight';
			$new_arr[$i]['roster_sub_type']  = 'PYRD';
		}elseif ($data[6] == 'K') {
			$new_arr[$i]['roster_type'] = 'flight';
			$new_arr[$i]['roster_sub_type']  = 'K';
		}elseif (@$data[6] && in_array($data[6][$i],$airport_codes)) {
			$new_arr[$i]['flight'][$filght_count]['arrival_code'] = $data[6][$i];
			$new_arr[$i]['flight'][$filght_count]['flight_end'] = $data[7][$i];
			$new_arr[$i]['flight'][$filght_count]['duty_end'] = $data[8][$i];
			$new_arr[$i]['flight'][$filght_count]['aircraft_type'] = $data[9][$i];
			$new_arr[$i]['roster_type'] = "flight";
			$new_arr[$i]['roster_sub_type'] = "1 sector (TA inbound arrival)";
			continue;

		}elseif (@$data[6] && strlen($data[6][$i]) == 2) {
			$new_arr[$i]['roster_type'] = 'flight';
			$new_arr[$i]['roster_sub_type']  = "Blank";
			continue;
		}
		else {
			$new_arr[$i]['roster_type'] = 'flight';
			$new_arr[$i]['roster_sub_type']  = $data[6][$i];
		}
		//flight cases
		$new_arr[$i]['flight'][$filght_count]['duty_start'] = $data[7][$i];
		$new_arr[$i]['flight'][$filght_count]['flight_start'] = $data[8][$i];
		$new_arr[$i]['flight'][$filght_count]['departure_code'] = $data[9][$i];

		//case 1 TA outbound only
		if (in_array($data[10][$i],$airport_codes)) {
			$new_arr[$i]['flight'][$filght_count]['arrival_code'] = $data[10][$i];
			$new_arr[$i]['flight'][$filght_count]['flight_end'] = $data[11][$i];

			if (strpos($data[12][$i],":") !== false) {
				$new_arr[$i]['flight'][$filght_count]['duty_end'] = $data[12][$i];
				$new_arr[$i]['flight'][$filght_count]['aircraft_type'] = $data[13][$i];
				$new_arr[$i]['roster_sub_type'] = "1 sector(TA outbound only)";
				//$j++;
				//case3  2 sectors Europe only
			}else {
				// If space
				if (strlen($data[12][$i]) == 2) {
					$new_arr[$i]['flight'][$filght_count]['space'] = true;

					$filght_count++;
					$new_arr[$i]['flight'][$filght_count]['number'] = $data[13][$i];
					$new_arr[$i]['flight'][$filght_count]['flight_start'] = $data[14][$i];
					$new_arr[$i]['flight'][$filght_count]['departure_code'] = $data[15][$i];
					$new_arr[$i]['flight'][$filght_count]['arrival_code'] = $data[16][$i];
					$new_arr[$i]['flight'][$filght_count]['flight_end'] = $data[17][$i];
					$new_arr[$i]['roster_sub_type'] = "2 sectors(Europe only)";
					//case 5 2 sector + K duty
					if (strlen($data[18][$i]) == 2) {

						$new_arr[$i]['flight'][$filght_count]['space'] = true;

						// case 6, 3 sectors LHR only
						if (is_numeric($data[19][$i])) {
							$filght_count++;
							$new_arr[$i]['flight'][$filght_count]['number'] = $data[19][$i];
							$new_arr[$i]['flight'][$filght_count]['flight_start'] = $data[20][$i];
							$new_arr[$i]['flight'][$filght_count]['departure_code'] = $data[21][$i];
							$new_arr[$i]['flight'][$filght_count]['arrival_code'] = $data[22][$i];
							$new_arr[$i]['flight'][$filght_count]['flight_end'] = $data[23][$i];
							$new_arr[$i]['roster_sub_type'] = "3 sectors(LHR only)";
							//case 7, 4 sector
							if (strlen($data[24][$i]) == 2) {
								$new_arr[$i]['flight'][$filght_count]['space'] = true;
								$filght_count++;
								$new_arr[$i]['flight'][$filght_count]['number'] = $data[25][$i];
								$new_arr[$i]['flight'][$filght_count]['flight_start'] = $data[26][$i];
								$new_arr[$i]['flight'][$filght_count]['departure_code'] = $data[27][$i];
								$new_arr[$i]['flight'][$filght_count]['arrival_code'] = $data[28][$i];
								$new_arr[$i]['flight'][$filght_count]['flight_end'] = $data[29][$i];
								$new_arr[$i]['flight'][$filght_count]['space'] = true;
								$filght_count++;
								$new_arr[$i]['flight'][$filght_count]['number'] = $data[31][$i];
								$new_arr[$i]['flight'][$filght_count]['flight_start'] = $data[32][$i];
								$new_arr[$i]['flight'][$filght_count]['departure_code'] = $data[33][$i];
								$new_arr[$i]['flight'][$filght_count]['arrival_code'] = $data[34][$i];
								$new_arr[$i]['flight'][$filght_count]['flight_end'] = $data[35][$i];
								$new_arr[$i]['flight'][$filght_count]['duty_end'] = $data[36][$i];
								$new_arr[$i]['roster_sub_type'] = "4 sectors";
							}else {
								$new_arr[$i]['flight'][$filght_count]['duty_end'] = $data[24][$i];
							//	$j++;

							}

						}else {
							$new_arr[$i]['flight'][$filght_count]['type'] = 'K';
							$new_arr[$i]['flight'][$filght_count]['duty_start'] = $data[20][$i];
							$new_arr[$i]['flight'][$filght_count]['duty_end'] = $data[21][$i];
							$new_arr[$i]['roster_sub_type'] = "2 sectors(K duty only)";
						//	$j++;
						}

					}else {
					$new_arr[$i]['flight'][$filght_count]['duty_end'] = $data[18][$i];
				//	$j++;
					}


				}else {
					//case4  2 sectors Europe only 2nd case
					$new_arr[$i]['flight'][$filght_count]['aircraft_type'] = $data[12][$i];
					$filght_count++;
					$new_arr[$i]['flight'][$filght_count]['space'] = true;
					$new_arr[$i]['flight'][$filght_count]['number'] = $data[14][$i];
					$new_arr[$i]['flight'][$filght_count]['flight_start'] = $data[15][$i];
					$new_arr[$i]['flight'][$filght_count]['departure_code'] = $data[16][$i];
					$new_arr[$i]['flight'][$filght_count]['arrival_code'] = $data[17][$i];
					$new_arr[$i]['flight'][$filght_count]['flight_end'] = $data[18][$i];
					$new_arr[$i]['flight'][$filght_count]['duty_end'] = $data[19][$i];
				//	$j++;
				}


			}


		}else {
				//case2 TA inbound only
				$new_arr[$i]['flight'][$filght_count]['aircraft_type'] = $data[10][$i];
				$new_arr[$i]['roster_sub_type'] = '1 sector (TA inbound only)';


		}
}

//echo "<pre>"; print_r($new_arr);
//die("Hey I am here");
$scrap->dbConnection(DB_HOST,DB_USER,DB_PWD,DB_NAME);
foreach($new_arr as $k=>$datas){
		$datas['jsondata'] = json_encode($dataarray[$datas['date']]);
		if (isset($datas['flight'])) {
			// foreach ($datas['flight'] as $key => $flights) {
			// 	echo "<pre>"; print_r($flights);
			// echo	$flight_number = $flights['number'];
			// 	 $crew_data = $scrap->get_crew_members($flight_crew_members,$flight_number);
			// 	 $datas['crew_data'] = json_encode($crew_data);
			// }

			$datas['flight_info'] = json_encode($datas['flight']);
		}else {
			$datas['flight_info'] = '';
		}
	//	echo "<pre>"; print_r($datas);
		$scrap->insertScheduleData($params['crew_id'],$datas);
}
echo json_encode(['message'=>'Data sync successfully', 'success'=>true]);
