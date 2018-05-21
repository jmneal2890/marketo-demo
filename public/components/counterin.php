<?php

  function countin($page) {
      //Call DB handler
      include 'dbh.inc.php';

      //establish page variables
      $total = $page . '_total';
      $registered = $page . '_registered';

      //Set timestamp for current visit
      $currentTime = date("Y-m-d H:i:s");
      echo '<br>'.$currentTime;
      echo '<br>'.strtotime($currentTime);
      echo '<br>'.strtotime('-12 hours');

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
      $ipaddress = '192.168.1.1';
      echo '<br>'.$ipaddress;

      //Check if IP address is valid
      if ($ipaddress == 'UNKNOWN') {

        //Tick up internal counter of UNKNOWN IP
        $sql = "SELECT user_ip FROM pageviewcount WHERE user_ip=UNKNOWN;";
        $result = mysqli_query($conn, $sql);
        $inc = mysqli_fetch_array($result, MYSQLI_NUM);
        $inc[$total] ++;
        $sql = "UPDATE pageviewcount SET $total = $inc[$total], user_timestamp = $currentVisit WHERE user_ip = UNKNOWN;";
        mysqli_query($conn, $sql);
        mysqli_close($conn);
      } else {

        //Check user IP is registered in DB
        echo '<br>IP register check';
        $sql = "SELECT * FROM pageviewcount WHERE user_ip=INET_ATON('$ipaddress');";
        $results = mysqli_query($conn, $sql);
        foreach ($results as $row) {
          $userIp = $row['user_ip'];
          echo '<br>'.$userIp;
          $userTimestamp = $row['user_timestamp'];
          echo '<br>'.$userTimestamp;
          echo '<br>'.strtotime($userTimestamp);
          $totalViews = $row[$total];
          echo '<br>'.$totalViews;
          $registeredViews = $row[$registered];
          echo '<br>'.$registeredViews;
        }
        mysqli_close($conn);

        if ($userIp != '') {

          //Check if most recent registered timestamp against $currentVisit
          echo '<br>Timestamp check';
          if (strtotime($userTimestamp) >= strtotime('-12 hours')) {

            //Add registered hit and total hit to approriate column, update user_timestamp
            echo '<br>Registered hit add and timestamp update';
            $totalViews++;
            echo '<br>'.$totalViews;
            $registeredViews++;
            echo '<br>'.$registeredViews;
            $sql = "UPDATE pageviewcount SET user_timestamp = $currentTime, $total = $totalViews, $registered = $registeredViews WHERE user_ip=INET_ATON('$ipaddress');";
            mysqli_query($conn, $sql);
            mysqli_close($conn);
          } else {

            //Add total hit to appropriate column
            echo '<br>total hit add';
            $totalViews++;
            $sql = "UPDATE pageviewcount SET $total=$totalViews WHERE user_ip=INET_ATON('$ipaddress');";
            mysqli_query($conn, $sql);
            mysqli_close($conn);
          }
        } else {
          include 'dbh.inc.php';

          //Add new user to DB and log registered hit
          echo '<br>add new user and registered hit add';
          $sql = "INSERT INTO pageviewcount (user_id, user_ip, user_timestamp, $total, $registered) VALUES (null, INET_ATON('$ipaddress'), $currentTime, 1, 1);";
          echo '<br>'.$sql;
          if (mysqli_query($conn, $sql)) {
            echo "new record added";
          } else {
            echo "There was an issue";
          }
          //mysqli_query($conn, $sql);
          mysqli_close($conn);
          echo '<br> tried inserting';
        }
      }
    }
