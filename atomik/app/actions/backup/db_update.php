<h3> Process database update </h3>
<?php
$maxSize=30000000;                            // Only save files smaller than 30M
$uploadSize = $_FILES['filename']['size'];  // The size of our uploaded file
$uploadType = $_FILES['filename']['type'];  // The type of the file.
$uploadName = getcwd().'/uploadedFile.dat'; // Never trust the upload, make your own name
$filename = basename( $_FILES['filename']['name']);
if ($uploadSize<$maxSize) {              // Make sure the file size isn't too big.
   move_uploaded_file($_FILES['filename']['tmp_name'], $uploadName);   // save file.
   echo "File $filename has been treated<BR>";
   echo "It was $uploadSize bytes of type $uploadType"; 
   if ($uploadType == "application/x-gzip") {
	/* type gzip, we must gunzip */
	//$ret = readgzfile($uploadName);
	$lines = gzfile($uploadName);
	$sql="";
	foreach ($lines as $line) {
    	   echo $line;
	   $sql = $sql + $line;
	}
   } 
   else {
	/* work also if not gzipped */
	$ret = readgzfile($uploadName);
   }
   //$result_response = do_query($sql);
   //$fp = fopen($uploadName,  "r");
   //$source = fread($fp, $uploadSize);
   //echo $source;
}

