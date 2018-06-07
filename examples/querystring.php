<?php

      $bgcolor="#FFFFFF";

      print "<html><body bgcolor='".$_GET['bgcolor']."'>";

      // Print out the current data and time
    
      $dt = date("g:i:s A l, F j Y.");
      print "<font color='".$_GET['fgcolor']."' size='".$_GET['size']."'>";
      print "The Current Date and Time is: $dt<br/>";

      print "<li> Server received QueryString:   ".$_SERVER['QUERY_STRING'];
      print "</font></body></html>";
?>
