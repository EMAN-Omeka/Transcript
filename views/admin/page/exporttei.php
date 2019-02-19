<?php
	$csv = fopen('php://output', 'w');
 	ob_start();
  echo $transcription;

	$string = ob_get_clean();
	
	$filename = 'eman-tei-export-' . date('Ymd') .'-' . date('His');
			
 // Output CSV-specific headers
  header('Pragma: public');
  header('Expires: 0');
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
  header('Cache-Control: private', false);
  header('Content-Type: application/octet-stream');
  header('Content-Disposition: attachment; filename="' . $filename . '.xml";');
  header('Content-Transfer-Encoding: binary');
	
	exit($string);

?>

