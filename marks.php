<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="marks.css">
</head>
<body>

<?php
$year = $_GET['year'];
$grade = $_GET['grade'];
$term = $_GET['term'];

$_header = '<p>'.$year.' Term '.$term.' Grade '.$grade.'</p>';

$column = array("Term1", "Term2", "JuneExam", "Term3", "Term4", "NovemberExam", "Coursetotal");
$column_heading = array("T1", "T2", "Ju<br>Ex", "T3", "T4", "No<br>Ex", "Fin");
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
$students = $sql->students_in_cohort($_GET['cohort']);

?>
<table>
<thead>
<?php
$header1 = "<tr>\n<th colspan=2>".$_header."</th>";
$header2 = "<tr>\n<th width=100px>Name</th>\n<th width=100px>Surname</th>";
$subjects = $sql->subjects($grade, $year);
foreach($subjects as $subject){
	$subjectname = $subject['fullname'];
	$subjectname = substr($subjectname, 0, strlen($subjectname) - 5);
	if(strpos($subjectname, "rade") == 1){
		$cut = 8;
		if($grade > 9){$cut++;}
		$subjectname = substr($subjectname, $cut);
	}
	$header1 = $header1."<th colspan='".$columns."'".' style="text-align: center;">'.$subjectname."</th>\n";
	$c = -1;
	while(++$c < $columns){	
		$header2 = $header2.'<th style="text-align: center;">'.$column_heading[$c]."</th>\n";
	}
}
$header1 = $header1."</tr>\n";
$header2 = $header2."</tr>\n";
echo $header1;
echo $header2;
echo "</thead>\n";


while($student = mysqli_fetch_array($students))
{
	$studentid = $student['id'];

	echo "<tr><td>".$student['firstname']."</td>\n";
	echo "<td>".$student['lastname']."</td>\n";

	$subjects = $sql->subjects($grade, $year);
	$subject_number = 0;	
	foreach($subjects as $subject){
		$subjectid = $subject['id'];
		$c = 0;
		while($c < $columns){
			$markitems = $sql->mark_items($studentid, $subjectid, $column[$c]);
			echo '<td style="text-align: center;">';
			if($markitem = mysqli_fetch_array($markitems)){
				$mark = round($markitem['mark']);
				$outof = $markitem['outof'];
				if($c+1 == $columns){$mark = '<font color="blue">'.$mark.'</font>';}
				echo $mark."</td>\n";
			} else {
				echo "</td>\n";
			}
			$c++;
		}
	}
	echo "</tr>\n";
}

?>
	</tbody>
	</table>
	</div>
	<br><br>
</div>
</body>
</html>
