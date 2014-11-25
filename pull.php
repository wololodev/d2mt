<?php

if ( $_POST['payload'] ) {
  // Only respond to POST requests from Github
  
    shell_exec("git pull");
    die("pulled @ " . mktime());
}

?>
