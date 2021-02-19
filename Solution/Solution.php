<?php 

 // Initiate a new database connection
function databaseConnect(){

	$dbInfo = array(
		'host'      => "localhost",
		'user'      => "root",
		'pass'      => "password"
	);
$db = new database($dbInfo);
mysql_close($db->get_link());

// Construct the database class
class database{
   protected $databaseLink;
   function __construct(){
	include "Solution.php";
	   $this->database = $dbInfo['host'];
	   $this->mysql_user = $dbInfo['user'];
	   $this->mysql_pass = $dbInfo['pass'];
	   $this->openConnection();
	   return $this->get_link();
   }
   function openConnection(){
   $this->databaseLink = mysql_connect($this->database, $this->mysql_user, $this->mysql_pass);
   }

   function get_link(){
   return $this->databaseLink;
   }
}

	//Push the candidates and jobs to the database
    $data = getCandidates();
    insertArr("Employees.Candidates", $data);
	$data = getJobs();
    insertArr("Employees.Jobs", $data);

	//handle the insertion
function insertArr($tableName, $insData){
   $db = new database();
   $columns = implode(", ",array_keys($insData));
   $escaped_values = array_map('mysql_real_escape_string', array_values($insData));
   foreach ($escaped_values as $idx=>$data) $escaped_values[$idx] = "'".$data."'";
   $values  = implode(", ", $escaped_values);
   $query = "INSERT INTO $tableName ($columns) VALUES ($values)";
   mysql_query($query) or die(mysql_error());
   mysql_close($db->get_link());
}
}

combineValues();

//Combine the two seperate arrays into one
function combineValues(){
	$candidates = getCandidates();
	$jobs = getJobs();

	//loop the candidate and job arrays to match jobs to candidates via candidate ID
	foreach ($candidates as $candidate){
		foreach ($jobs as $job){
			if ($job["CandidateId"] == $candidate["CandidateId"]){

				$candidates[$job["CandidateId"] - 1]["Jobs"][] = $job;
			}
		}
	}
	
	//usort each candidates jobs via its end date
	foreach ($candidates as $candidate){
		usort($candidate["Jobs"], 'date_compare');
		print_r($candidate);

	}
	//print_r($candidates);
}

//usort function
function date_compare($element1, $element2) { 
    $datetime1 = DateTime::createFromFormat('d.m.Y H:i', $element1['EndDate']); 
    $datetime2 = DateTime::createFromFormat('d.m.Y H:i', $element2['EndDate']); 

    return $datetime2 <=> $datetime1; 
}  

//parse the candidate csv file into an associative array
function getCandidates(){
$file = "candidates.csv";
$candidateArray = array_map('str_getcsv', file($file));
    array_walk($candidateArray, function(&$a) use ($candidateArray) {
      $a = array_combine($candidateArray[0], $a);
    });
    array_shift($candidateArray);

return $candidateArray;
}

//parse the job csv file into an associative array
function getJobs(){
	$file = "jobs.csv";
$jobArray = array_map('str_getcsv', file($file));
    array_walk($jobArray, function(&$a) use ($jobArray) {
      $a = array_combine($jobArray[0], $a);
    });
    array_shift($jobArray);

return $jobArray;
}
