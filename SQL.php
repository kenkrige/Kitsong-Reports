<?php
class SQL{
//remove public later
public $con = null;

public function __construct() {
//	$this->con = mysqli_connect("localhost","reports","kuamosi","moodle");
	$this->con = mysqli_connect("localhost","moodle", "ku4m0s1","moodle");
}

public function students_in_cohort($cohort){
	$sql =	"SELECT
		mdl_user.id as id,
		firstname, lastname
		FROM
		mdl_cohort_members
 		JOIN mdl_user
		on mdl_cohort_members.userid = mdl_user.id
		WHERE mdl_cohort_members.cohortid = ".$cohort
		." ORDER BY lastname, firstname";
	return mysqli_query($this->con,$sql);
} //end of students_in_cohort()

public function subjects($grade, $year){
	$subjectarray = array();

	$sql2 = "SELECT DISTINCT id,
		fullname
		FROM mdl_course
		WHERE (replace(fullname, ' ', '') LIKE 'Grade".$grade.
		"%' OR replace(fullname, ' ', '') LIKE 'AdvancedProgramme%') 
		AND fullname LIKE '%".$year. 
		"' ORDER BY mdl_course.sortorder";

	$subjects = mysqli_query($this->con,$sql2);
	while($subject = mysqli_fetch_array($subjects)){
		array_push($subjectarray, $subject);
	}
	return $subjectarray;
} // end of subjects()

public function averages($subjects, $grade_category, $cohort){

	$averages = array();

	foreach ($subjects as $subject){
		$subjectid = $subject['id'];

		$sql_ave = "SELECT avg(mdl_grade_grades.finalgrade) AS average,
			mdl_grade_grades.rawgrademax AS outof
			FROM
			mdl_cohort_members
 			JOIN mdl_user
			ON mdl_cohort_members.userid = mdl_user.id
			JOIN mdl_grade_grades ON mdl_user.id = mdl_grade_grades.userid
			JOIN mdl_grade_items ON mdl_grade_grades.itemid = mdl_grade_items.id
			JOIN mdl_grade_categories ON mdl_grade_items.iteminstance = mdl_grade_categories.id
			JOIN mdl_course ON mdl_grade_items.courseid = mdl_course.id
			WHERE mdl_cohort_members.cohortid = ".$cohort.
			" AND mdl_course.id = ".$subjectid.
			" AND (( mdl_grade_items.itemtype = 'category' 
			 AND replace(mdl_grade_categories.fullname, ' ', '') = '".$grade_category."') 
			OR (mdl_grade_items.itemtype = 'course' AND '".$grade_category."' = 'Coursetotal'))";
		$resultset = mysqli_query($this->con,$sql_ave);
		if($result = mysqli_fetch_array($resultset)){
			$average = 100 * $result['average'] / $result['outof'];
			array_push($averages, $average);
		}
	}
	return $averages;
} // end of averages()
//AND mdl_grade_grades.timemodified > 1502500000 

public function lock($subjects, $grade_category){
	echo "<p>";
	foreach ($subjects as $subject){
		$subjectid = $subject['id'];
		$sql_lock = "";
		if($grade_category == "Coursetotal"){
			$sql_lock = "UPDATE mdl_grade_items  
		                SET locked = 1 
				WHERE courseid = ".$subjectid." 
				AND itemtype = 'course'";
		}else{
			$sql_lock = "UPDATE mdl_grade_items, mdl_grade_categories 
		                SET mdl_grade_items.locked = 1 
				WHERE mdl_grade_items.iteminstance = mdl_grade_categories.id 
		                AND mdl_grade_items.courseid = ".$subjectid." 
				AND mdl_grade_items.itemtype = 'category' 
				AND replace(mdl_grade_categories.fullname, ' ', '') = '".$grade_category."'";
		}
		//echo $sql_lock;
	        if (mysqli_query($this->con,$sql_lock)) {
                   echo $subject['fullname']." ".$grade_category." locked.<br>"; 
 	        } else {
                   echo "Error updating record: " . mysqli_error($this->con);
                   echo $subject['fullname'];
	        }
//                echo mysqli_query($this->con,$sql_lock);
	}
	echo "</p>";
	
	return;
} // end of lock()


public function mark_items($studentid, $subjectid, $grade_category){
	$sql3 = "SELECT DISTINCT mdl_grade_items.itemname AS activity,
		mdl_grade_grades.finalgrade AS mark,
		mdl_grade_grades.rawgrademax AS outof,
		mdl_grade_grades.feedback AS comment
		FROM mdl_user
		JOIN mdl_grade_grades ON mdl_user.id = mdl_grade_grades.userid
		JOIN mdl_grade_items ON mdl_grade_grades.itemid = mdl_grade_items.id
		JOIN mdl_grade_categories ON mdl_grade_items.iteminstance = mdl_grade_categories.id
		JOIN mdl_course ON mdl_grade_items.courseid = mdl_course.id
		WHERE mdl_user.id = ".$studentid.
		" AND mdl_course.id = ".$subjectid.
		" AND (( mdl_grade_items.itemtype = 'category' 
		 AND replace(mdl_grade_categories.fullname, ' ', '') = '".$grade_category."') 
		OR (mdl_grade_items.itemtype = 'course' AND '".$grade_category."' = 'Coursetotal'))";
		return mysqli_query($this->con,$sql3);

} //end mark_items

public function tutor($studentid){

	$tutor = "";

	$sql_tutor = "SELECT
		mdl_cohort.name as tutor
		FROM
		mdl_cohort_members
 		JOIN mdl_cohort ON mdl_cohort_members.cohortid = mdl_cohort.id
		JOIN mdl_context ON mdl_cohort.contextid = mdl_context.id
		JOIN mdl_course_categories ON mdl_context.instanceid = mdl_course_categories.id
		WHERE mdl_cohort_members.userid = ".$studentid.
		" AND mdl_course_categories.name = 'Tutor'";

	$tutorset = mysqli_query($this->con,$sql_tutor);
	if($temp = mysqli_fetch_array($tutorset)){
		$tutor = $temp['tutor'];
	}
	return $tutor;
} //end of function tutor

public function days_absent($studentid, $term, $year){
	$sqlabsent = "SELECT
         	mdl_grade_grades.finalgrade AS days
		FROM mdl_user
		JOIN mdl_grade_grades ON mdl_user.id = mdl_grade_grades.userid
		JOIN mdl_grade_items ON mdl_grade_grades.itemid = mdl_grade_items.id
		JOIN mdl_course ON mdl_grade_items.courseid = mdl_course.id
		WHERE mdl_user.id = ".$studentid.
		" AND mdl_grade_items.itemname LIKE '%erm ".$term."' 
		AND mdl_grade_items.itemname LIKE 'Days absent%' 
		AND mdl_course.fullname LIKE 'Register%' 
		AND mdl_course.fullname LIKE '%".$year."'";


	$abs = "null";
	$days = mysqli_query($this->con,$sqlabsent);
	if($absent = mysqli_fetch_array($days)){
		$abs = round($absent['days']);
	}
	return $abs;
} //end days_absent()

public function days_late($studentid, $term, $year){
	$sqllate = "SELECT
         	mdl_grade_grades.finalgrade AS days
		FROM mdl_user
		JOIN mdl_grade_grades ON mdl_user.id = mdl_grade_grades.userid
		JOIN mdl_grade_items ON mdl_grade_grades.itemid = mdl_grade_items.id
		JOIN mdl_course ON mdl_grade_items.courseid = mdl_course.id
		WHERE mdl_user.id = ".$studentid.
		" AND mdl_grade_items.itemname LIKE '%erm ".$term."' 
		AND mdl_grade_items.itemname LIKE 'Days late%' 
		AND mdl_course.fullname LIKE 'Register%' 
		AND mdl_course.fullname LIKE '%".$year."'";


	$L = "null";
	$days = mysqli_query($this->con,$sqllate);
	if($late = mysqli_fetch_array($days)){
		$L = round($late['days']);
	}
	return $L;
} //end days_late()

public function birthdate($studentid){
	$sqlDOB = "SELECT
         	mdl_user_info_data.data AS birthday
		FROM mdl_user_info_data
		JOIN mdl_user_info_field ON mdl_user_info_field.id = mdl_user_info_data.fieldid
		WHERE mdl_user_info_data.userid = ".$studentid.
		" AND mdl_user_info_field.shortname = 'DOB'";

	$birthday = "null";
	$bday = mysqli_query($this->con,$sqlDOB);
	if($DOB = mysqli_fetch_array($bday)){
		$birthday = $DOB['birthday'];
	}
	return $birthday;
} //end birthdate()

public function tutor_comment($studentid, $term, $year){
	$sql = "SELECT
         	mdl_grade_grades.feedback AS feedback
		FROM mdl_user
		JOIN mdl_grade_grades ON mdl_user.id = mdl_grade_grades.userid
		JOIN mdl_grade_items ON mdl_grade_grades.itemid = mdl_grade_items.id
		JOIN mdl_course ON mdl_grade_items.courseid = mdl_course.id
		WHERE mdl_user.id = ".$studentid.
		" AND mdl_grade_items.itemname = 'Term ".$term."' 
		AND mdl_course.fullname LIKE 'Tutor%' 
		AND mdl_course.fullname LIKE '%".$year."'";

	$comment = "";
	$comments = mysqli_query($this->con,$sql);
	if($comment = mysqli_fetch_array($comments)){
		$comment = $comment['feedback'];
	}
	return $comment;	

} //end tutor_comment()

public function principal(){
	$sql =	"SELECT
		firstname, lastname
		FROM mdl_user
		WHERE username = 'principal'";
	$principal = mysqli_query($this->con,$sql);
	return mysqli_fetch_array($principal);
} //end principal()

} //end of class SQL
?>

