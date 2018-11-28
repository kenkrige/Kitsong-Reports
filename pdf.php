<?php
$cohort = unserialize($_GET['cohort']);
$grade =  $cohort['grade'];
$reports_file = "Reports_Grade".$grade."_Term".$_GET['term']."_".$_GET['year']."_Published_".date("Y_m_d").".pdf";
$marks_file = "Marks_Grade".$grade."_Term".$_GET['term']."_".$_GET['year']."_Published_".date("Y_m_d").".pdf";

$params = "cohort=".$cohort['id']."&term=".$_GET['term']."&year=".$_GET['year']."&grade=".$grade."&termbegin=".$_GET['termbegin']."&termend=".$_GET['termend']."&nextbegin=".$_GET['nextbegin']."&lock=".$_GET['lock'];

//$url1 = "http://".$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF'])."/marks.php?".$params;
//echo "<html><script>window.open('".$url1."');</script></html>";
//header("Location: $url1");

$url_reports = "http://".$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF'])."/cohort.php?".$params;
$command_rep = "prince \"".$url_reports."\" ".getcwd()."/pdf/".$reports_file;

shell_exec ($command_rep);

$url_marks = "http://".$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF'])."/marks.php?".$params;
$command_marks = "prince \"".$url_marks."\" ".getcwd()."/pdf/".$marks_file;

shell_exec ($command_marks);

$url2 = "http://".$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF'])."/pdf/".$reports_file;

header("Location: $url2");

//echo "<html><script>window.open('".$url2."');</script></html>";


?>

