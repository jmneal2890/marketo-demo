<?php
    include 'dbh.inc.php';

    $sql = "SELECT SUM(bloga_registered) FROM pageviewcount";
    $results = mysqli_query($conn, $sql);
    $bloga = mysqli_fetch_array($results);

    $sql = "SELECT SUM(blogb_registered) FROM pageviewcount";
    $results = mysqli_query($conn, $sql);
    $blogb = mysqli_fetch_array($results);

    echo "<h4>Style A Count: $bloga[0]</h4></br><h4>Style B Count: $blogb[0]</h4>";
