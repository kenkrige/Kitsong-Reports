<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="report2.css">
</head>
<body>

<div class="batch-report">

<?php
$year = $_GET['year'];
$grade = $_GET['grade'];
$term = $_GET['term'];
$cohort = $_GET['cohort'];
$lock = $_GET['lock'];

//$markfile = fopen("MarksGrade".$grade.".csv", "w");

echo '<img src="drongo2w.svg">';
echo '<p>Academic report to parents</p>';
echo '<p>End of Term '.$term.' '.$year.'</p>';
echo '<p>Cohort Batch for Grade '.$grade.'</p>';
echo '<p>Published '.date("l jS \of F Y h:i:s A").'</p>';
echo '</div>';

$column = array("Term1", "Term2", "JuneExam", "Term3", "Term4", "NovemberExam", "Coursetotal");
$column_heading = array("Term 1", "Term 2", "June<br>Exam", "Term 3", "Term 4", "Final<br>Exam", "Final<br>Mark");
$column_phrase = array("Term 1", "Term 2", "the June Exam", "Term 3", "Term 4", "the Final Exam", "the Final Mark");
$columns = 0;

switch($term){
	case "1":
		$columns = 1;
		break;
	case "2":
		$columns = 3;
		break;
	case "3":
		$columns = 4;
		break;
	case "4":
		$columns = 7;
		break;
}

include 'SQL.php';

$sql = new SQL();
$principal = $sql->principal();
$students = $sql->students_in_cohort($cohort);
$subjects = $sql->subjects($grade, $year);
$averages_course = $sql->averages($subjects, "Coursetotal", $cohort);

if($lock){
    $c = 0;
    while($c < $columns){
	$sql->lock($subjects, $column[$c]);
	$c++;
    }
    echo "<p>Locked marks can be unlocked in moodle under: Gradebook > Setup > Subject ";
    echo "> Edit Settings.</p>";

}

//$averages1 = $sql->averages($subjects, "Term1", $cohort);
//$averages2 = $sql->averages($subjects, "Term2", $cohort);
//$averages3 = $sql->averages($subjects, "Term3", $cohort);
//$averagesE = $sql->averages($subjects, "JuneExam", $cohort);



/*$header1 = ",";
$header2 = "Name,";

$subjects = $sql->subjects($grade, $year);
while($subject = mysqli_fetch_array($subjects)){
	$subjectname = $subject['fullname'];
	$subjectname = substr($subjectname, 0, strlen($subjectname) - 5);
	if(strpos($subjectname, "rade") == 1){
		$cut = 8;
		if($grade > 9){$cut++;}
		$subjectname = substr($subjectname, $cut);
	}
	$header1 = $header1.$subjectname;
	$c = -1;
	while(++$c < $columns){	
		$header2 = $header2.$column[$c].",";
		$header1 = $header1.",";
	}
}
fwrite($markfile, $header1."\n");
fwrite($markfile, $header2."\n");
*/
while($student = mysqli_fetch_array($students))
{
	$studentid = $student['id'];
	$date = date("j F Y");
	$tutor = $sql->tutor($studentid);

	//fwrite($markfile, $student['firstname']." ".$student['lastname'].",");

	echo '<div class="user-report" width=100%>'."\n";
	echo '<div class="header">'."\n";
	echo '<img src="Letterhead.png"></div>'."\n\n";

//	echo '<div width=100%><br><table width=100% border="0" style="border-collapse:collapse">'."\n";
//	echo '<tr><td border="0">Academic Report</td><td border="0" style="text-align:right">Term '.$term." ".$year.'</td></tr></table><br></div>'."\n";
	echo "<p>Academic Report: Term ".$term." ".$year."</p>";

$DOB_date = new DateTime();
$DOB_date->setTimestamp($sql->birthdate($studentid));

$begin_date = new DateTime($_GET['termbegin']);
$end_date = new DateTime($_GET['termend']);
$next_begin_date = new DateTime($_GET['nextbegin']);

?>
	<div width=100%>
	<table width=100%>
		<tbody>

		<tr style="background: #b2b2b2">
		<td style="width: 33%">Name</td><td style="width: 33%">Surname</td><td style="width: 34%">Grade</td>
		</tr>

		<tr>
		<td><?php echo $student['firstname'] ?></td><td><?php echo $student['lastname'] ?></td><td><?php echo $grade ?></td>
		</tr>

		<tr style="background: #b2b2b2">
		<td>Date of Birth</td><td>Days Absent</td><td>Days Late</td>
		</tr>

		<tr>
		<td><?php echo $DOB_date->format('j F Y'); ?></td>
		<td><?php echo $sql->days_absent($studentid, $term, $year); ?></td>
		<td><?php echo $sql->days_late($studentid, $term, $year); ?></td>
		</tr>

		<tr style="background: #b2b2b2">
		<td>Term Beginning</td><td>Term Ending</td><td>Next Term Begins</td>
		</tr>

		<tr>
		<td><?php echo $begin_date->format('j F Y'); ?></td>
		<td><?php echo $end_date->format('j F Y'); ?></td>
		<td><?php echo $next_begin_date->format('j F Y'); ?></td>
		</tr>

		</tbody>
	</table>
	</div>

	<p><b>Note: </b>All marks in the following table are expressed as percentages.</p>

	<div class="course" width=100%>
	<table width=100%>
	<tr style="background: #b2b2b2;">
	<td style="width: 30%; vertical-align: bottom;">Subject</td>
<?php
	$c = -1;
	while(++$c < $columns){	
		echo '<td style="width: 7%; text-align: center; vertical-align: bottom;">'.$column_heading[$c].'</td>'."\n\t\t";
	}
?>
	<td style="width: 7%; text-align: center; vertical-align: bottom;">Class<br>Ave</td></tr>
	<tbody>
<?php
	$subjects = $sql->subjects($grade, $year);
	$subject_number = 0;	
	foreach ($subjects as $subject){
		$subjectid = $subject['id'];
		$subjectname = $subject['fullname'];
		$subjectname = substr($subjectname, 0, strlen($subjectname) - 5);
		if(strpos($subjectname, "rade") == 1){
			$cut = 8;
			if($grade > 9){$cut++;}
			$subjectname = substr($subjectname, $cut);
		}

	$row_valid = 0;
	$row = "\n\t\t<tr>";
	$row = $row.'<td>'.$subjectname.'</td>';

	$c = 0;
	while($c < $columns){
		$markitems = $sql->mark_items($studentid, $subjectid, $column[$c]);
		if($markitem = mysqli_fetch_array($markitems)){
			$mark = round($markitem['mark']);
			$outof = $markitem['outof'];
			$comment = $markitem['comment'];
			$row = $row.'<td style="text-align: center;">'.$mark.'</td>';
			if($mark > 0){
				$row_valid = 1;
			}
			//fwrite($markfile, $mark.",");
		} else {
			$row = $row.'<td style="text-align: center;">-</td>';
			//fwrite($markfile, " ,");
		}
		$c++;
	}
//		$ave = $averages1[$subject_number] * 3 
//			+ $averages2[$subject_number] * 4 
//			+ $averagesE[$subject_number] * 3;
		$ave = $averages_course[$subject_number];
		$subject_number++;
//		$row = $row.'<td style="text-align: center;">'.round($ave/10).'</td>';
		$row = $row.'<td style="text-align: center;">'.round($ave).'</td>';
		$row = $row.'</tr>';
		if($row_valid == 1){
			echo $row;
		}
//		if (! is_null($comment)){//if(strlen($comment) > 1){
//			echo '<tr><td></td><td colspan="5">';
//			echo $comment.'</td></tr>';
//		}
	}
//	fwrite($markfile, "\n");
?>
	</tbody>
	</table>
	</div>
	<br><br>
	<div width=100%>
	<table width=100%>
	<tr>
<?php
	$tut_comment_br = $sql->tutor_comment($studentid, $term, $year);
        $tut_comment = str_replace("<br>", " ", $tut_comment_br);
	if(strlen($tut_comment) > 400){
?>
		<td colspan=2><b>Tutor's Comment: </b><?php echo $tut_comment; ?><br><br>
<?php
	} else {
?>
		<td colspan=2>Tutor's Comment:<br><br>
<?php
		echo $tut_comment."<br><br>";
	}
?>	
	</td>
	</tr>
	<tr>
	<td width=50%><br><br></td>
	<td width=50% style="padding-left: 45px;"><img src="sign.png" height="35"></td>
	</tr>
	<tr>
	<td>Tutor: <?php echo $tutor; ?></td>
	<td>Principal: K Krige<?php echo substr($principal['firstname'], 0, 1)." ".$principal['lastname']; ?></td>
	</tr>
	</table>
	</div>
	</div>
<?php
}
//fclose($markfile);
?>
</div>
</body>
</html>
