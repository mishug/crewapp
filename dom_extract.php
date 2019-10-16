<?php
session_start();
if (isset($_SESSION['flights'])) {
  //echo $_SESSION['flights'];
  $dom = new DOMDocument();
  @$dom->loadHTML($_SESSION['flights']);
  // get the third table
  $table = $dom->getElementsByTagName('table')->item(0);
  $trs = $table->childNodes;
  foreach ($trs as $key => $tr) {
    $tds = $tr->childNodes;
    $flight_number =$tds->item(0)->nodeValue;
    $flight_number = htmlentities($flight_number, null, 'utf-8');
    $flight_number = str_replace("&nbsp;", "", $flight_number);
    $flight_number = html_entity_decode($flight_number);
    if (trim($flight_number) == "572") {
      foreach ($tds as $key => $td) {
        if ($key > 18) {
          /*
          Codes for getting the crew members data
          19: Route Number,
          20: route_day
          22: displayed_date
          21 : SROW

          */
          //$td->nodeValue;
          $flight['route_no'] = $tds->item(19)->nodeValue;
          $flight['route_day'] = $tds->item(21)->nodeValue;
          $flight['SROW'] = $tds->item(23)->nodeValue;
          $flight['displayed_date'] = $tds->item(25)->nodeValue;
        }

      }
      echo "<pre>"; print_r($flight);
    }

  //  echo $tds->item(0)->nodeValue."/n";
  }
  //echo "<pre>"; print_r($first_row);
  echo $_SESSION['flights'];
  exit();
}


 ?>
