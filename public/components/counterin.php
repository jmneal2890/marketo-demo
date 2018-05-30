<?php

  function countin($page) {
      //Call DB handler
      include 'dbh.inc.php';

      //establish page variables
      $total = $page . '_total';
      $registered = $page . '_registered';
      $timestamp = $page . '_timestamp';

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

      //IP manipulation for testing
      //$ipaddress = 'UNKNOWN';
      //$ipaddress = '192.129.2.9';

      //Check if IP address is valid
      if ($ipaddress == 'UNKNOWN') {

        //Tick up internal counter of UNKNOWN IP
        echo '<br>update UNKNOWN ip counter';
        $sql = "SELECT * FROM pageviewcount WHERE user_ip='UNKNOWN'";
        $results = mysqli_query($conn, $sql);
        foreach ($results as $row) {
          $userTimestamp = $row[$timestamp];
          $totalViews = $row[$total];
          $registeredViews = $row[$registered];
        }
        $totalViews++;
        $sql = "UPDATE pageviewcount SET $total = $totalViews, $timestamp = '$currentTime', user_timestamp = '$currentTime' WHERE user_ip = 'UNKNOWN'";
        mysqli_query($conn, $sql);

      } else {

        //Check user IP is registered in DB
        echo '<br>IP register check';
        $sql = "SELECT * FROM pageviewcount WHERE user_ip = INET_ATON('$ipaddress')";
        $results = mysqli_query($conn, $sql);
        foreach ($results as $row) {
          $userIp = $row['user_ip'];
          $userTimestamp = $row[$timestamp];
          $totalViews = $row[$total];
          $registeredViews = $row[$registered];
        }

        if ($userIp != '') {

          //Check if most recent registered timestamp against $currentVisit
          echo '<br>Timestamp check';
          if (strtotime($userTimestamp) <= strtotime('-12 hours')) {

            //Add registered hit and total hit to approriate column, update user_timestamp

            echo '<br>Registered hit add and timestamp update';
            $totalViews++;
            $registeredViews++;
            $sql = "UPDATE pageviewcount SET user_timestamp = '$currentTime', $timestamp = '$currentTime', $total = '$totalViews', $registered = '$registeredViews' WHERE user_ip = INET_ATON('$ipaddress')";
            mysqli_query($conn, $sql);

          } else {

            //Add total hit to appropriate column

            echo '<br>total hit add';
            $totalViews++;
            $sql = "UPDATE pageviewcount SET user_timestamp = '$currentTime', $total = $totalViews WHERE user_ip = INET_ATON('$ipaddress')";
            mysqli_query($conn, $sql);

          }
        } else {

          //Add new user to DB and log registered hit
          echo '<br>add new user with total and registered hit';
          $sql = "INSERT INTO pageviewcount (user_ip, user_timestamp, $timestamp, $total, $registered) VALUES (INET_ATON('$ipaddress'), '$currentTime', '$currentTime', 1, 1)";
          mysqli_query($conn, $sql);

        }
      }
      mysqli_close($conn);
    }
