<?php
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors',true);
error_reporting(E_ALL);

require_once 'lib.php';
require_once 'config.php';
session_start();

if (isset($_GET['username']) && isset($_GET['password']) && $_GET['crew_id'] && $_GET['crew_password'] && $_GET['date'] && $_GET['flight_number']) {
	$params['username'] = $_GET['username'];
	$params['password'] = $_GET['password'];
	$params['crew_id'] = $_GET['crew_id'];
	$params['crew_password'] = $_GET['crew_password'];
	$params['date'] = $_GET['date'];
	$params['flight_number'] = $_GET['flight_number'];
}else {
	echo json_encode(['message'=>'missing parameters','error'=>true]);
	exit;
}
$scrap = new Scrapping($params['username'], $params['password'],$params['crew_id'],md5($params['crew_password']));
if (isset($_SESSION[$params['username']])) {
	// code...
}else {
	$url="https://portal.aerlingus.com/";
	$date = date("d/m/Y", strtotime("".$params['date']));
	$year = date('Y', strtotime($date));
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


		// $url = 'https://portal.aerlingus.com/web/Portal/Operations%20Portal/Cabin%20Crew/Home';
		// $header = array(
		// 					'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
		// 					'Accept-Encoding: gzip, deflate, br',
		//      'Cookie: '.$cookie1[0].'; '.$cookie2[0].'; '.$JSESSIONID[0],
		//      'Host: portal.aerlingus.com',
		//      'Upgrade-Insecure-Requests: 1',
		//      'Referer: https://portal.aerlingus.com/',
		//      'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36'
		//  );
		// $resp = $scrap->getMethod($url,$header);
		//
		// $url = 'https://ecrew.aerlingus.com/wtouch/wtouch.exe/index?MAC=0&VER=1';
		// $header = array(
		// 					'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
		// 					'Accept-Encoding: gzip, deflate, br',
		//      'Cookie: '.$cookie1[0].'; '.$cookie2[0],
		//      'Host: ecrew.aerlingus.com',
		//      'Upgrade-Insecure-Requests: 1',
		//      'Referer: https://portal.aerlingus.com/',
		//      'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36'
		//  );
		// $resp = $scrap->getMethod($url,$header);

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


		// $url = 'https://ecrew.aerlingus.com/wtouch/perinfo.exe/index';
		// $header = array(
		// 					'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
		// 					'Accept-Encoding: gzip, deflate, br',
		//      'Cookie: '.$cookie3[0].'; '.$cookie4[0].'; '.$cookie5[0].'; '.$cookie6[0].'; '.$cookie7[0].'; '.$cookie1[0].'; '.$cookie2[0],
		//      'Host: ecrew.aerlingus.com',
		//      'Upgrade-Insecure-Requests: 1',
		//      'Referer: https://portal.aerlingus.com/',
		//      'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36'
		//  );
		// $resp = $scrap->getMethod($url,$header);
		//
		//
		// $url = 'https://ecrew.aerlingus.com/wtouch/fltinfo.exe/index';
		// $header = array(
		// 					'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
		// 					'Accept-Encoding: gzip, deflate, br',
		//      'Cookie: '.$cookie3[0].'; '.$cookie4[0].'; '.$cookie5[0].'; '.$cookie6[0].'; '.$cookie7[0].'; '.$cookie1[0].'; '.$cookie2[0],
		//      'Host: ecrew.aerlingus.com',
		//      'Upgrade-Insecure-Requests: 1',
		//      'Referer: https://portal.aerlingus.com/',
		//      'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36'
		//  );
		// $resp = $scrap->postMethod($url,$header,'flightinfo');


		$url = 'https://ecrew.aerlingus.com/wtouch/fltinfo.exe/AjAction'; // getflight info
		$header = array(
							'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
							'Accept-Encoding: gzip, deflate, br',
		     'Cookie: '.$cookie3[0].'; '.$cookie4[0].'; '.$cookie5[0].'; '.$cookie6[0].'; '.$cookie7[0].'; '.$cookie1[0].'; '.$cookie2[0],
		     'Host: ecrew.aerlingus.com',
		     'Upgrade-Insecure-Requests: 1',
		     'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36'
		 );
		// $resp = $scrap->postMethod($url,$header,'whoisonboard');
		$flight_crew_members_api = $scrap->postMethod($url,$header,'flightsdata',$date);
		$flight_crew_members = $flight_crew_members_api['data'];
		$crew_data = $scrap->get_crew_members($flight_crew_members,$params['flight_number']);
		echo "<pre>"; print_r($crew_data);
		$resp = $scrap->postMethod($url,$header,'whoisonboard',$date);
		$cookiecat = explode(';',$resp['header']['Set-Cookie'][0]);

		// Get crew members
		$url = 'https://ecrew.aerlingus.com/wtouch/fltinfo.exe/onboard?ac_select=0&route_day='.$crew_data["route_day"].'&carrier_select=0&dep_arr_select=0&displayed_date='.$crew_data["displayed_date"].'&SROW='.$crew_data["SROW"].'&route_no='.$crew_data["route_no"].'&time_format=2'; // getflight info
		$header = array(
							'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
							'Accept-Encoding: gzip, deflate, br',
		     'Cookie: '.$cookie3[0].'; '.$cookie4[0].'; '.$cookie5[0].'; '.$cookie6[0].'; '.$cookie7[0].'; '.$cookie1[0].'; '.$cookie2[0].'; '.$cookiecat[0].'; AROW=7; ERMSG=866;',
		     'Host: ecrew.aerlingus.com',
		     'Upgrade-Insecure-Requests: 1',
		     'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36'
		 );
		$resp = $scrap->getMethod($url,$header);

		$dom = new DOMDocument();
		@$dom->loadHTML($resp['data']);
		echo $resp['data'];
		// get the third table
		$thirdTable = $dom->getElementsByTagName('table')->item(0);
		$node = $thirdTable->firstChild;
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
		$scrap->dbConnection(DB_HOST,DB_USER,DB_PWD,DB_NAME);
		$membercount = 0;
		foreach ($data as $key => $value) {
			if ($key > 1) {
				$crew_members_data[$membercount]['flight_number'] = $params['flight_number'];
				$crew_members_data[$membercount]['flight_date'] = date("Y-m-d", strtotime("".$_GET['date']));
				$crew_members_data[$membercount]['member_id'] = $value[0];
				$crew_members_data[$membercount]['name'] = str_replace("'","`",$value[2]);
				$crew_members_data[$membercount]['base'] = $value[4];
				$crew_members_data[$membercount]['ac'] = $value[6];
				$crew_members_data[$membercount]['pos'] = $value[8];
				$crew_members_data[$membercount]['py'] = $value[10];
				$crew_members_data[$membercount]['status'] = '';
				$scrap->insertMemebersQuery($crew_members_data[$membercount]);
				$membercount++;
			}
		}



		//echo $thirdTable->childNodes;
		// echo "<pre>"; print_r($resp);
		echo "<pre>"; print_r($crew_members_data);
		die();
		// $cookiecat = explode(';',$resp['header']['Set-Cookie'][0]);
		// $url = 'https://ecrew.aerlingus.com/wtouch/perinfo.exe/crwsche';
		// $header = array(
		// 					'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
		// 					'Accept-Encoding: gzip, deflate, br',
		//      'Cookie: '.$cookie3[0].'; '.$cookie4[0].'; '.$cookie5[0].'; '.$cookie6[0].'; '.$cookie7[0].'; '.$cookie1[0].'; '.$cookie2[0].'; SCR=1B;',
		//      'Host: ecrew.aerlingus.com',
		//      'Origin: https://ecrew.aerlingus.com',
		//      'Upgrade-Insecure-Requests: 1',
		//      'Referer: https://ecrew.aerlingus.com/wtouch/perinfo.exe/oper',
		//      'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36'
		//  );
		//
		// $resp = $scrap->postMethod($url,$header,'getdata',$date);
		//
		// $cookie8 = explode(';',$resp['header']['Set-Cookie'][1]);
		// $cookie9 = explode(';',$resp['header']['Set-Cookie'][3]);
		// $cookie10 = explode(';',$resp['header']['Set-Cookie'][4]);
		//
		// $url = 'https://ecrew.aerlingus.com/wtouch/perinfo.exe/waMin1Body';
		// $header = array(
		// 					'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
		// 					'Accept-Encoding: gzip, deflate, br',
		//      'Cookie: '.$cookie3[0].'; '.$cookie4[0].'; '.$cookie5[0].'; '.$cookie6[0].'; '.$cookie7[0].'; '.$cookie1[0].'; '.$cookie2[0].'; '.$cookie8[0].' SCR=1B; STATUS=0; '.$cookie9[0].'; Report='.$cookie10[0],
		//      'Host: ecrew.aerlingus.com',
		//      'Upgrade-Insecure-Requests: 1',
		//      'Referer: https://portal.aerlingus.com/',
		//      'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36'
		//  );
		// $resp = $scrap->getMethod($url,$header);
		$_SESSION['htmldata'] = $resp;
}

echo json_encode(['message'=>'Data sync successfully', 'success'=>true]);
