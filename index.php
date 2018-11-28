<html>
<link rel="stylesheet" type="text/css" href="default.css">
<head>
<link rel="SHORTCUT ICON" HREF="favicon.ico">
<h1>End of Term Reports</h1>
</head>
<body>
<div>
<?php
$con = mysqli_connect("localhost","reports","kuamosi","moodle");
$cohorts = mysqli_query($con,
	"SELECT DISTINCT
	id, name
	FROM mdl_cohort
	WHERE name LIKE 'Matric%'
	ORDER BY name DESC
	");
echo '<form action=pdf.php method="GET">'."\n";

echo '<p>';
echo 'Cohort: <select name="cohort">\n'."\n";
while($row = mysqli_fetch_array($cohorts)){
	$grade =  12 + date("Y") - substr($row['name'],7);	
	$info['id'] = $row['id'];
	$info['grade'] = $grade;
	$data = serialize($info);
	if($grade < 13 && (strlen($row['name']) < 13)){
		printf ("<option value=%s>Grade %s</option>\n", $data, $grade);
	}
}
?>
</select></p>

<p>
Term: <select name="term">'
	<option value=1>Term 1</option>	
	<option value=2>Term 2</option>
	<option value=3>Term 3</option>	
	<option value=4>Term 4</option>	
</select>
</p>
<p>
<input type="checkbox" name="lock" checked> Lock moodle category totals<br>
</p>
<p>Dates as (yyyy-mm-dd)</p>
<p>Term Beginning: <input type="datetime" name="termbegin" value="2018-10-09"></p>
<p>Term Ending: <input type="datetime" name="termend" value="2018-12-07"></p>
<p>Next Term Begins: <input type="datetime" name="nextbegin" value="2019-01-16"></p>

<p>Year: <input type="number" size=4 name="year" value="<?php echo Date("Y")?>"></p>

<button type="view" formtarget="_blank">Submit</button>

</form>
</div>
</body>
</html>
