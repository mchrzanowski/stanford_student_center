<!--
\\
\\ Student Center
\\ SŽbastien Robaszkiewicz
\\ Justin Cheng
\\ Mike Chrzanowski
\\ 2014
\\
-->

<?php

require_once("constants.php");

// Gets the SUNetID.
$webAuthUser = $_SERVER['REMOTE_USER'];

// Fetches the Google Spreadsheet as a CSV file.
// To change the Google Spreadsheet, use the key corresponding to your new document.
$url = "https://docs.google.com/spreadsheet/pub?key=".$key."&output=csv";

// csv_to_array($filename, $delimiter) converts a CSV file to an associative array
//   - Takes the first line of the CSV as the header (key)
//   - Creates a row in the associative array for each new line of the CSV file (value)
// Put differently, the keys are the column headers of the Google Spreadsheet.
//
// @@ For instance, if the CSV file gotten from the Google Spreadsheet is:
// @@
// @@ sunetid, hw1, hw2
// @@ jure, 98, 99
// @@ robinio, 95, 100
// @@
// @@ and we call $students = csv_to_array($filename) on it,
// @@ then $student[0]["sunetid"] would be "jure",
// @@ and $student[1]["hw2"] would be "100".
//
function csv_to_array($filename='', $delimiter=',') {
	global $error;
    $header = NULL;
    $data = array();
    if (($handle = @fopen($filename, 'r')) !== FALSE)
    {
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
        {
            if(!$header)
                $header = $row;
            else
                $data[] = array_combine($header, $row);
        }
        fclose($handle);
    } else {
		$error .= "We're experiencing connectivity issues right now... :(";
	}
    return $data;
}


// $students is the associative array with all the rows from the CSV file.
$students = csv_to_array($url);

// Finds the row corresponding to the logged in student and assign it to $student.
// If that SUNetID is not in the Google Spreadsheet, assign $student = NULL.
$student = NULL;
foreach($students as $stud) {
    $sunetid = $stud["sunetid"];
    if ($sunetid == $webAuthUser) {
        $student = $stud;
    }
    if ($stud["sunetid"] == "0_class_avg") {
        $averageStats = $stud;
    }
    if ($stud["sunetid"] == "0_class_max") {
        $maxStats = $stud;
    }
    if ($stud["sunetid"] == "0_class_sd") {
        $stdevStats = $stud;
    }
    if ($stud["sunetid"] == "0_class_median") {
        $medianStats = $stud;
    }
}

function lateDisplayHW0($lateVal) {
	if($lateVal === "1") {
		return "<span class=\"label label-success\">Received</span>";
	} else {
		return "<span class=\"label label-info\">Not Received</span>";
	}
}

function lateDisplay($lateVal) {
	if($lateVal === "0") {
		return "<span class=\"label label-success\">Received</span>";
	} elseif ($lateVal === "") {
		return "<span class=\"label label-info\">Not Received</span>";
	} else {
		return "<span class=\"label label-warning\">Turned in Late</span>";
	}
}

function coverDisplay($coverVal) {
	if($coverVal === "-2") {
		return "<span class=\"label label-warning\">None!</span>";
	} else {
		return "<span class=\"label label-success\">Present!</span>";
	}
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo $className." ".$termName; ?> Student Center</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <![endif]-->
    </head>

    <body>

<div style="background-color: #ddd; border-bottom: 1px solid #444; margin-bottom: 15px;">
<div class="container">
<nav class="navbar navbar-default" role="navigation" style="background: 0; border: 0; margin-bottom: 0;">
  <!-- Brand and toggle get grouped for better mobile display -->
  <div class="navbar-header">
    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>
    <a class="navbar-brand" href="#" style="padding-left: 0;"><strong><?php echo $className." ".$termName; ?></strong> Student Center</a>
  </div>

  <!--<div class="collapse navbar-collapse navbar-ex1-collapse">
    <ul class="nav navbar-nav">
      <li><a href="#">Homework</a></li>
      <li><a href="#">Final</a></li>
    </ul>
  </div>-->
</nav>
</div>
</div>

<div class="container">

<?php if (strlen($error) > 0) { ?>
<div class="alert alert-danger"><strong><?php echo $error; ?></strong></div>
<?php } ?>

<?php if(!isset($student)) { ?>

    <div class="row-fluid">
        <div class="span12">
            <div class="hero-unit">
                <h1>Sorry <?php echo $webAuthUser; ?>, we couldn't find you in our database.</h1>
                <h2><a href="mailto:<?php echo $staffEmail; ?>?Subject=[<?php echo $className; ?> Student Center] Can't find &quot;<?php echo $webAuthUser; ?>&quot; in database">
Please contact us</a> if you think this is a mistake.</h2>
            </div>
        </div>
    </div>

<?php } else { ?>

    <div class="row-fluid">
        <div class="span12">
			<h2>Hi, <strong><?php echo $student["first_name"] . " " . $student["last_name"]; ?></strong>!</h2>
<?php { ?>
Check out your grades, as well as late periods used. If there are any discrepancies between your actual and recorded grades, <a href="mailto:<?php echo $staffEmail; ?>?Subject=[CS246 Student Center] Grade discrepancy for <?php echo $webAuthUser; ?>">contact us</a>!
<?php } ?>
        </div>
    </div>

<?php { ?>

    <div class="row-fluid">
        <div class="span12">
		<h3>Late Periods</h3>
            <?php
            $late_days = $student["late_days"] + $student["hw3_late_days"];
            if ($late_days == 0) {$alertType = "alert-success"; $alertMessage = "Yay!";}
            else if ($late_days == 1) {$alertType = "alert-info"; $alertMessage = "Heads-up!";}
            else if ($late_days == 2) {$alertType = "alert-warning"; $alertMessage = "Warning!";}
            ?>
            <div class="alert <?php echo $alertType; ?>" >
                <strong><?php echo $alertMessage ?></strong> You have used <strong><?php echo $late_days ?></strong> out of your 2 allowed late periods.
            </div>
        </div>
    </div>

    <div class="row-fluid">
        <div class="span12">
            <div class="hero-unit">
                <h3>Homework</h3>
                <table class="table table-hover table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Status</th>
                            <th>Cover?</th>
                            <th>Q1</th>
                            <th>Q2</th>
                            <th>Q3</th>
                            <th>Q4</th>
                            <th class="total">Total</th>
                            <th class="stat">Avg.</th>
                            <th class="stat">Max</th>
                            <th class="stat">StDev.</th>
                            <th class="stat">Med.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($student["hw0_total"] != "") { ?>
                        <tr>
                            <td><strong>HW0</strong></td>
                            <td><strong><?php echo lateDisplayHW0($student["hw0_total"]); ?></strong></td>
                            <td><strong>--</strong></td>
                            <td><strong>--</strong></td>
                            <td><strong>--</strong></td>
                            <td><strong>--</strong></td>
                            <td><strong>--</strong></td>
                            <td class="total"><strong><?php echo intval($student["hw0_total"]) * 100; ?></strong>/100</td>
                            <td class="stat"><?php echo number_format($averageStats["hw0_total"] * 100,0); ?></td>
                            <td class="stat"><?php echo number_format($maxStats["hw0_total"] * 100,0); ?></td>
                            <td class="stat"><?php echo number_format($stdevStats["hw0_total"] * 100,0); ?></td>
                            <td class="stat"><?php echo number_format($medianStats["hw0_total"] * 100,0); ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($student["hw1_total"] != "") { ?>
                        <tr>
                            <td><strong>HW1</strong></td>
                            <td><?php echo lateDisplay($student["hw1_latedays"]); ?></td>
                            <td><?php echo coverDisplay($student["hw1_nocover"]); ?></td>
                            <td><strong><?php echo $student["hw1_q1"]; ?></strong>/25</td>
                            <td><strong><?php echo $student["hw1_q2"]; ?></strong>/30</td>
                            <td><strong><?php echo $student["hw1_q3"]; ?></strong>/15</td>
                            <td><strong><?php echo $student["hw1_q4"]; ?></strong>/30</td>
                            <td class="total"><strong><?php echo $student["hw1_total"]; ?></strong>/100</td>
                            <td class="stat"><?php echo number_format($averageStats["hw1_total"],0); ?></td>
                            <td class="stat"><?php echo number_format($maxStats["hw1_total"],0); ?></td>
                            <td class="stat"><?php echo number_format($stdevStats["hw1_total"],0); ?></td>
                            <td class="stat"><?php echo number_format($medianStats["hw1_total"],0); ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($student["hw2_total"] != "") { ?>
                        <tr>
                            <td><strong>HW2</strong></td>
                            <td><?php echo lateDisplay($student["hw2_latedays"]); ?></td>
                            <td><?php echo coverDisplay($student["hw2_nocover"]); ?></td>
                            <td><strong><?php echo $student["hw2_q1"]; ?></strong>/25</td>
                            <td><strong><?php echo $student["hw2_q2"]; ?></strong>/25+5</td>
                            <td><strong><?php echo $student["hw2_q3"]; ?></strong>/20</td>
                            <td><strong><?php echo $student["hw2_q4"]; ?></strong>/30</td>
                            <td class="total"><strong><?php echo $student["hw2_total"]; ?></strong>/100</td>
                            <td class="stat"><?php echo number_format($averageStats["hw2_total"],0); ?></td>
                            <td class="stat"><?php echo number_format($maxStats["hw2_total"],0); ?></td>
                            <td class="stat"><?php echo number_format($stdevStats["hw2_total"],0); ?></td>
                            <td class="stat"><?php echo number_format($medianStats["hw2_total"],0); ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($student["hw3_total"] != "") { ?>
                        <tr>
                            <td><strong>HW3</strong></td>
                            <td><?php echo lateDisplay($student["hw3_latedays"]); ?></td>
                            <td><?php echo coverDisplay($student["hw3_nocover"]); ?></td>
                            <td><strong><?php echo $student["hw3_q1"]; ?></strong>/25</td>
                            <td><strong><?php echo $student["hw3_q2"]; ?></strong>/25</td>
                            <td><strong><?php echo $student["hw3_q3"]; ?></strong>/25</td>
                            <td><strong><?php echo $student["hw3_q4"]; ?></strong>/25</td>
                            <td class="total"><strong><?php echo $student["hw3_total"]; ?></strong>/100</td>
                            <td class="stat"><?php echo number_format($averageStats["hw3_total"],0); ?></td>
                            <td class="stat"><?php echo number_format($maxStats["hw3_total"],0); ?></td>
                            <td class="stat"><?php echo number_format($stdevStats["hw3_total"],0); ?></td>
                            <td class="stat"><?php echo number_format($medianStats["hw3_total"],0); ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($student["hw4_total"] != "") { ?>
                        <tr>
                            <td><strong>HW4</strong></td>
                            <td><?php echo lateDisplay($student["hw4_latedays"]); ?></td>
                            <td><?php echo coverDisplay($student["hw4_nocover"]); ?></td>
                            <td><strong><?php echo $student["hw4_q1"]; ?></strong>/25</td>
                            <td><strong><?php echo $student["hw4_q2"]; ?></strong>/20</td>
                            <td><strong><?php echo $student["hw4_q3"]; ?></strong>/20</td>
                            <td><strong><?php echo $student["hw4_q4"]; ?></strong>/35</td>
                            <td class="total"><strong><?php echo $student["hw4_total"]; ?></strong>/100</td>
                            <td class="stat"><?php echo number_format($averageStats["hw4_total"],0); ?></td>
                            <td class="stat"><?php echo number_format($maxStats["hw4_total"],0); ?></td>
                            <td class="stat"><?php echo number_format($stdevStats["hw4_total"],0); ?></td>
                            <td class="stat"><?php echo number_format($medianStats["hw4_total"],0); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php } ?>

<?php if ($showGradiance) { ?>
    <div class="row-fluid">
        <div class="span12">
            <div class="hero-unit">
                <h3>Gradiance Quizzes</h3>
                <table class="table table-hover table-bordered table-striped">                    
                    <tbody>
                        <?php if ($student["gradiance0"] != "") { ?>
                        <tr>
                            <td><strong>MapReduce</strong></td>
                            <td class="total"><strong><?php echo $student["gradiance0"]; ?></strong>/9</td>
                        </tr>
                        <?php } ?>
                        <?php if ($student["gradiance1"] != "") { ?>
                        <tr>
                            <td><strong>Association Rules</strong></td>
                            <td class="total"><strong><?php echo $student["gradiance1"]; ?></strong>/15</td>
                        </tr>
                        <?php } ?>
                        <?php if ($student["gradiance2"] != "") { ?>
                        <tr>
                            <td><strong>LSH: Locality Sensitive Hashing</strong></td>
                            <td class="total"><strong><?php echo $student["gradiance2"]; ?></strong>/15</td>
                        </tr>
                        <?php } ?>
                        <?php if ($student["gradiance3"] != "") { ?>
                        <tr>
                            <td><strong>SVD and Clustering</strong></td>
                            <td class="total"><strong><?php echo $student["gradiance3"]; ?></strong>/15</td>
                        </tr>
                        <?php } ?>
                        <?php if ($student["gradiance4"] != "") { ?>
                        <tr>
                            <td><strong>Recommendation Systems</strong></td>
                            <td class="total"><strong><?php echo $student["gradiance4"]; ?></strong>/9</td>
                        </tr>
                        <?php } ?>
                        <?php if ($student["gradiance5"] != "") { ?>
                        <tr>
                            <td><strong>PageRank</strong></td>
                            <td class="total"><strong><?php echo $student["gradiance5"]; ?></strong>/15</td>
                        </tr>
                        <?php } ?>
                        
                        <?php if ($student["gradiance6"] != "") { ?>
                        <tr>
                            <td><strong>Analysis of Graphs</strong></td>
                            <td class="total"><strong><?php echo $student["gradiance6"]; ?></strong>/12</td>
                        </tr>
                        <?php } ?>
                        <?php if ($student["gradiance7"] != "") { ?>
                        <tr>
                            <td><strong>Machine Learning</strong></td>
                            <td class="total"><strong><?php echo $student["gradiance7"]; ?></strong>/12</td>
                        </tr>
                        <?php } ?>
                        <?php if ($student["gradiance8"] != "") { ?>
                        <tr>
                            <td><strong>Data Streams</strong></td>
                            <td class="total"><strong><?php echo $student["gradiance8"]; ?></strong>/15</td>
                        </tr>
                        <?php } ?>
                        <?php if ($student["gradiance9"] != "") { ?>
                        <tr>
                            <td><strong>Advertising</strong></td>
                            <td class="total"><strong><?php echo $student["gradiance9"]; ?></strong>/12</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php } ?>

<?php if ($showFinalExam) {?>
    <div class="row-fluid">
        <div class="span12">
            <div class="hero-unit">
                <h2>Final Exam</h2>
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="total">Total</th>
                            <th class="stat">Avg.</th>
                            <th class="stat">Max</th>
                            <th class="stat">StDev.</th>
                            <th class="stat">Med.</th>
                        </tr>
                    </thead>
                    <tbody> 
                        <?php if ($student["final_total"] != "") { ?>
                        <tr>
                            <td style="font-size: 26px"><strong>Final Exam</strong></td>
                            <td class="total"><strong><?php echo $student["final_total"]; ?></strong>/180</td>
                            <td class="stat"><?php echo number_format($averageStats["final_total"],2); ?></td>
                            <td class="stat"><?php echo number_format($maxStats["final_total"],2); ?></td>
                            <td class="stat"><?php echo number_format($stdevStats["final_total"],2); ?></td>
                            <td class="stat"><?php echo number_format($medianStats["final_total"],2); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php } ?>

<?php } ?>

<hr />
<footer class="footer">
<?php { ?>
	<p><small>Summary statistics are rounded to the nearest integer, and are calculated based on students who have handed in their work. Naturally, they are subject to change. If you didn't submit your assignment with a cover sheet, 2 points will be deducted from the total score. If you exceeded your number of late days, your assignments may be penalized 50%.</small></p>
<?php } ?>
    <p><small><a href="<?php echo $classWebsite; ?>">Back to <?php echo $className." ".$termName; ?></a></small></p>
</footer>

</div>

</body>
</html>
