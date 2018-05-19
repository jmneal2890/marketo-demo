<?php

  function countin($page) {
      //Call DB handler
      include 'dbh.inc.php';

      //Set timestamp for current visit
      $currentTime = date("Y-m-d H:i:s");

      //Pull visitor IP
      $ipaddress = '';
      if ($_SERVER['HTTP_CLIENT_IP']) {
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
      } elseif ($_SERVER['HTTP_X_FORWARDED_FOR']) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
      } elseif ($_SERVER['HTTP_X_FORWARDED']) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
      } elseif ($_SERVER['HTTP_FORWARDED_FOR']) {
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
      } elseif ($_SERVER['HTTP_FORWARDED']) {
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
      } elseif ($_SERVER['REMOTE_ADDR']) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
      } else {
        $ipaddress = 'UNKNOWN';
      }

      //Check if IP address is valid
      if ($ipaddress == 'UNKNOWN') {

        //Tick up internal counter of UNKNOWN IP
        $sql = "SELECT user_ip FROM pageviewcount WHERE user_ip=UNKNOWN";
        $result = mysqli_query($conn, $sql);
        $inc = mysqli_fetch_array($result, MYSQLI_NUM);
        $inc[0] ++;
        $sql = "UPDATE pageviewcount SET $page" . "_total = ($inc[0]), user_timestamp = $currentVisit WHERE user_ip = UNKNOWN";
        $update = mysqli_query($conn, $sql);
      } else {

        //Check user IP is registered in DB
        $sql = "SELECT * FROM pageviewcount WHERE user_ip=$ipaddress";
        $results = mysqli_query($conn, $sql);
        $data = mysqli_fetch_array($results);
        echo $data[1];
        foreach ($results as $row) {
          $userIp = $row['user_ip'];
          $userTimestamp = $row['user_timestamp'];
          $totalViews = $row[$page.'_total'];
          $registeredViews = $row[$page.'_registered'];
        }
        if ($userIp !== null) {

          //Check if most recent registered timestamp against $currentVisit
          if ($userTimestamp +12 > $currentTime) {

            //Add registered hit and total hit to approriate column, update user_timestamp
            $totalViews++;
            $registeredViews++;
            $sql = "UPDATE pageviewcount SET user_timestamp = $userTimestamp, $page"."_total = $totalViews, $page"."_registered = $registeredViews WHERE user_ip = $ipaddress";
            $update = mysqli_query($conn, $sql);
          } else {

            //Add total hit to appropriate column
            $sql = "UPDATE pageviewcount SET $page";
          }
        } else {

          //Add new user to DB and log registered hit
          $sql = "INSERT INTO pageviewcount VALUES (null, $ipaddress, $currentTime, )";

        }
      }
    }
