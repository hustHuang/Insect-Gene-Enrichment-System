<?php
   # ##### The server-side code in PHP ####     
   //# Type sent as part of the URL:    
   $type = $_GET['type'];
   # Get the raw POST data:    
   $data = file_get_contents('php://input');
   # Set the content type accordingly:
   if ($type == 'png') {
       header('Content-type: image/png');    
   }
   else if ($type == 'pdf') {
        $header = header('Content-type: application/pdf'); 
   } 
   else if ($type == 'svg') {
       header('Content-type: image/svg+xml');
   } 
   else if ($type == 'xml') {
       header('Content-type: text/xml');
   } 
   else if ($type == 'txt') {
       header('Content-type: text/plain');
   }
   # To force the browser to download the file:    
   header('Content-disposition: attachment; filename="network.' . $type . '"');
   # Send the data to the browser:    
   print $data;
?>
