<?php

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

$dt1 = new DateTime();
$startDate = $dt1->format("Y-m-01");
$startDateRange = $dt1->format("m/01/Y");

$dt2 = new DateTime();
$endDate = $dt2->format("Y-m-d");
$endDateRange = $dt2->format("m/d/Y");

$sdl = $dt1->modify("-1 month");
$startDateL = $sdl->format("Y-m-01");

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
} else {
  $status = "Connected to Data Warehouse";
}

if(isset($_GET["startDate"]) && isset($_GET["endDate"])) {
  $startDate = $_GET["startDate"];
  $endDate = $_GET["endDate"];

  $startDateL = date("Y-m-01", strtotime($_GET["startDate"] . " - 1 month"));

  $startDateRange = date("m/d/Y", strtotime($_GET["startDate"]));
  $endDateRange = date("m/d/Y", strtotime($_GET["endDate"]));
}

$hold = false;
$override = false;

if(isset($_GET["override"])) {
  $override = $_GET["override"];
}


$q1 = "select CF3, 'New', count(*) from SO join CustomFldPt on (CustomFldPt.PtKey=SO.PtKey) where SOCreateDt > '{$startDate}' and SOCreateDt < '{$endDate}' and (SOConfirmDt is null or SOConfirmDt > '{$endDate}') and SOClassification in ('New Pump Order','Non-Hygeia Pump Order') and CF3 in ('Moms Get More','momsgetmore.com','OB Office','OB Office - EMR','OB Office - Text','OB Office- Brochure','OB Office- MGM','OB Office- Tear Sheet','OB portal') group by CF3";
$q1L = "select CF3, 'New', count(*) from SO join CustomFldPt on (CustomFldPt.PtKey=SO.PtKey) where SOCreateDt > '{$startDateL}' and SOCreateDt < '{$startDate}' and (SOConfirmDt is null or SOConfirmDt > '{$startDate}') and SOClassification in ('New Pump Order','Non-Hygeia Pump Order') and CF3 in ('Moms Get More','momsgetmore.com','OB Office','OB Office - EMR','OB Office - Text','OB Office- Brochure','OB Office- MGM','OB Office- Tear Sheet','OB portal') group by CF3";

$q1r = $conn->query($q1);
$q1rL = $conn->query($q1L);

$b1F = ["Moms Get More", "OB Office - EMR", "OB Office - Text", "OB Office- Brochure", "OB Office- Tear Sheet", "OB portal"];
$b1R = [["Moms Get More", 0], ["OB Office - EMR", 1], ["OB Office - Text", 2], ["OB Office- Brochure", 3], ["OB Office- Tear Sheet", 4], ["OB portal", 5]];

$b1FL = ["Moms Get More", "OB Office - EMR", "OB Office - Text", "OB Office- Brochure", "OB Office- Tear Sheet", "OB portal"];
$b1RL = [["Moms Get More", 0], ["OB Office - EMR", 1], ["OB Office - Text", 2], ["OB Office- Brochure", 3], ["OB Office- Tear Sheet", 4], ["OB portal", 5]];

if($q1r->num_rows > 0) {

  while($row = $q1r->fetch_assoc()) {
    $indx = 0;
    for($x=0; $x < count($b1R); $x++) {
      if($row["CF3"] == $b1R[$x][0]) {
        $indx = $b1R[$x][1];
      }
    }
      if(is_numeric($row["count(*)"])) {
        $b1F[$indx] = [$row["CF3"], $row["count(*)"]];
      } else {
        $b1F[$indx] = [$row["CF3"], 0];
      }
  }

}
if($q1rL->num_rows > 0) {

  while($row = $q1rL->fetch_assoc()) {
    $indx = 0;
    for($x=0; $x < count($b1RL); $x++) {
      if($row["CF3"] == $b1RL[$x][0]) {
        $indx = $b1RL[$x][1];
      }
    }
      if(is_numeric($row["count(*)"])) {
        $b1FL[$indx] = [$row["CF3"], $row["count(*)"]];
      } else {
        $b1FL[$indx] = [$row["CF3"], 0];
      }
  }

}

$q2 = "select CF3, 'Closed', count(*) from SO join CustomFldPt on (CustomFldPt.PtKey=SO.PtKey) where SOCreateDt > '{$startDate}' and SOCreateDt < '{$endDate}' and SOConfirmDt is not null and SOConfirmDt  < '{$endDate}' and SOClassification in ('New Pump Order','Non-Hygeia Pump Order') and CF3 in ('Moms Get More','momsgetmore.com','OB Office','OB Office - EMR','OB Office - Text','OB Office- Brochure','OB Office- MGM','OB Office- Tear Sheet','OB portal') group by CF3,SOStatus";
$q2L = "select CF3, 'Closed', count(*) from SO join CustomFldPt on (CustomFldPt.PtKey=SO.PtKey) where SOCreateDt > '{$startDateL}' and SOCreateDt < '{$startDate}' and SOConfirmDt is not null and SOConfirmDt  < '{$startDate}' and SOClassification in ('New Pump Order','Non-Hygeia Pump Order') and CF3 in ('Moms Get More','momsgetmore.com','OB Office','OB Office - EMR','OB Office - Text','OB Office- Brochure','OB Office- MGM','OB Office- Tear Sheet','OB portal') group by CF3,SOStatus";

$q2r = $conn->query($q2);
$q2rL = $conn->query($q2L);

if($q2r->num_rows > 0) {

  while($row = $q2r->fetch_assoc()) {
    $indx = 0;
    for($x=0; $x < count($b1R); $x++) {
      if($row["CF3"] == $b1R[$x][0]) {
        $indx = $b1R[$x][1];
      }
    }
      if(is_numeric($b1F[$indx][1])) {
        $b1F[$indx] = [$row["CF3"], $b1F[$indx][1], $row["count(*)"]];
      } else {
        $b1F[$indx] = [$row["CF3"], 0, $row["count(*)"]];
      }
  }

}
if($q2rL->num_rows > 0) {

  while($row = $q2rL->fetch_assoc()) {
    $indx = 0;
    for($x=0; $x < count($b1RL); $x++) {
      if($row["CF3"] == $b1RL[$x][0]) {
        $indx = $b1RL[$x][1];
      }
    }
      if(is_numeric($b1F[$indx][1])) {
        $b1FL[$indx] = [$row["CF3"], $b1FL[$indx][1], $row["count(*)"]];
      } else {
        $b1FL[$indx] = [$row["CF3"], 0, $row["count(*)"]];
      }
  }

}

$q3 = "select
    CF3, 'Voided', count(*)
from SOVoid
    join CustomFldPt on (CustomFldPt.PtKey=SOVoid.PtKey)
where
    CreateDt > '{$startDate}' and
    CreateDt < '{$endDate}' and
    VoidedDt > '{$startDate}' and
    SOClassification in ('New Pump Order','Non-Hygeia Pump Order') and
    CF3 in ('Moms Get More','momsgetmore.com','OB Office','OB Office - EMR','OB Office - Text','OB Office- Brochure','OB Office- MGM','OB Office- Tear Sheet','OB portal')
group by CF3";
$q3L = "select
    CF3, 'Voided', count(*)
from SOVoid
    join CustomFldPt on (CustomFldPt.PtKey=SOVoid.PtKey)
where
    CreateDt > '{$startDateL}' and
    CreateDt < '{$startDate}' and
    VoidedDt > '{$startDateL}' and
    SOClassification in ('New Pump Order','Non-Hygeia Pump Order') and
    CF3 in ('Moms Get More','momsgetmore.com','OB Office','OB Office - EMR','OB Office - Text','OB Office- Brochure','OB Office- MGM','OB Office- Tear Sheet','OB portal')
group by CF3";
$q3r = $conn->query($q3);
$q3rL = $conn->query($q3L);

if($q3r->num_rows > 0) {

  while($row = $q3r->fetch_assoc()) {
    $indx = 0;
    for($x=0; $x < count($b1R); $x++) {
      if($row["CF3"] == $b1R[$x][0]) {
        $indx = $b1R[$x][1];
      }
    }
    if($row["count(*)"] > 0) {
      $b1F[$indx] = [$b1F[$indx][0], $b1F[$indx][1], $b1F[$indx][2], $row["count(*)"]];
    }
  }

}

if($q3rL->num_rows > 0) {

  while($row = $q3rL->fetch_assoc()) {
    $indx = 0;
    for($x=0; $x < count($b1RL); $x++) {
      if($row["CF3"] == $b1RL[$x][0]) {
        $indx = $b1RL[$x][1];
      }
    }
    if($row["count(*)"] > 0) {
      $b1FL[$indx] = [$b1FL[$indx][0], $b1FL[$indx][1], $b1FL[$indx][2], $row["count(*)"]];
    }
  }

}

for($x=0; $x<count($b1F); $x++) {
  if(is_null($b1F[$x][3])) {
    $b1F[$x] = [$b1R[$x][0], $b1F[$x][1], $b1F[$x][2], 0];
  }
}

for($x=0; $x<count($b1FL); $x++) {
  if(is_null($b1FL[$x][3])) {
    $b1FL[$x] = [$b1R[$x][0], $b1F[$x][1], $b1F[$x][2], 0];
  }
}

$q4 = "select CF3, 'New', count(*) from SO join CustomFldPt on (CustomFldPt.PtKey=SO.PtKey) where SOCreateDt > '{$startDate}' and SOCreateDt < '{$endDate}' and (SOConfirmDt is null or SOConfirmDt > '{$endDate}') and SOClassification in ('New Pump Order','Non-Hygeia Pump Order') and CF3 not in ('Moms Get More','momsgetmore.com','OB Office','OB Office - EMR','OB Office - Text','OB Office- Brochure','OB Office- MGM','OB Office- Tear Sheet','OB portal') group by CF3";
$q4r = $conn->query($q4);

$b2F = ["Facebook", "Google", "HH", "Insurance gave phone #", "WWW", "Zeeto"];
$b2R = [["Facebook", 0], ["Google", 1], ["HH", 2], ["Insurance gave phone #", 3], ["WWW", 4], ["Zeeto", 5]];

if($q4r->num_rows > 0) {

  while($row = $q4r->fetch_assoc()) {
    if($row["CF3"] == "FB" or $row["CF3"] == "Facebook") {
      $b2F[0] = [$b2R[0][0], $row["count(*)"]];
    } elseif($row["CF3"] == "Ads" or $row["CF3"] == "Google") {
      $b2F[1] = [$b2R[1][0], $row["count(*)"]];
    } elseif($row["CF3"] == "HH") {
      $b2F[2] = [$b2R[2][0], $row["count(*)"]];
    } elseif($row["CF3"] == "Insurance gave phone #") {
      $b2F[3] = [$b2R[3][0], $row["count(*)"]];
    } elseif($row["CF3"] == "WWW") {
      $b2F[4] = [$b2R[4][0], $row["count(*)"]];
    } elseif($row["CF3"] == "Zeeto") {
      $b2F[5] = [$b2R[5][0], $row["count(*)"]];
    }
  }
}

$q5 = "select CF3, 'Closed', count(*) from SO join CustomFldPt on (CustomFldPt.PtKey=SO.PtKey) where SOCreateDt > '{$startDate}' and SOCreateDt < '{$endDate}' and SOConfirmDt is not null and SOConfirmDt  < '{$endDate}' and SOClassification in ('New Pump Order','Non-Hygeia Pump Order') and CF3 not in ('Moms Get More','momsgetmore.com','OB Office','OB Office - EMR','OB Office - Text','OB Office- Brochure','OB Office- MGM','OB Office- Tear Sheet','OB portal') group by CF3,SOStatus";
$q5r = $conn->query($q5);

if($q5r->num_rows > 0) {

  while($row = $q5r->fetch_assoc()) {
    if($row["CF3"] == "FB" or $row["CF3"] == "Facebook") {
      $b2F[0] = [$b2R[0][0], $b2F[0][1], $row["count(*)"]];
    } elseif($row["CF3"] == "Ads" or $row["CF3"] == "Google") {
      $b2F[1] = [$b2R[1][0], $b2F[1][1], $row["count(*)"]];
    } elseif($row["CF3"] == "HH") {
      $b2F[2] = [$b2R[2][0], $b2F[2][1], $row["count(*)"]];
    } elseif($row["CF3"] == "Insurance gave phone #") {
      $b2F[3] = [$b2R[3][0], $b2F[3][1], $row["count(*)"]];
    } elseif($row["CF3"] == "WWW") {
      $b2F[4] = [$b2R[4][0], $b2F[4][1], $row["count(*)"]];
    } elseif($row["CF3"] == "Zeeto") {
      $b2F[5] = [$b2R[5][0], $b2F[5][1], $row["count(*)"]];
    }
  }

}

$q6 = "select CF3, 'New', count(*) from SOVoid join CustomFldPt on (CustomFldPt.PtKey=SOVoid.PtKey) where CreateDt > '{$startDate}' and CreateDt < '{$endDate}' and VoidedDt < '{$endDate}' and SOClassification in ('New Pump Order','Non-Hygeia Pump Order') and CF3 not in ('Moms Get More','momsgetmore.com','OB Office','OB Office - EMR','OB Office - Text','OB Office- Brochure','OB Office- MGM','OB Office- Tear Sheet','OB portal') group by CF3";
$q6r = $conn->query($q6);

if($q6r->num_rows > 0) {
  while($row = $q6r->fetch_assoc()) {
    if($row["CF3"] == "FB" or $row["CF3"] == "Facebook") {
      $b2F[0] = [$b2R[0][0], $b2F[0][1], $b2F[0][2], $row["count(*)"]];
    } elseif($row["CF3"] == "Ads" or $row["CF3"] == "Google") {
      $b2F[1] = [$b2R[1][0], $b2F[1][1], $b2F[1][2], $row["count(*)"]];
    } elseif($row["CF3"] == "HH") {
      $b2F[2] = [$b2R[2][0], $b2F[2][1], $b2F[2][2], $row["count(*)"]];
    } elseif($row["CF3"] == "Insurance gave phone #") {
      $b2F[3] = [$b2R[3][0], $b2F[3][1], $b2F[3][2], $row["count(*)"]];
    } elseif($row["CF3"] == "WWW") {
      $b2F[4] = [$b2R[4][0], $b2F[4][1], $b2F[4][2], $row["count(*)"]];
    } elseif($row["CF3"] == "Zeeto") {
      $b2F[5] = [$b2R[5][0], $b2F[5][1], $b2F[5][2], $row["count(*)"]];
    }
  }
}

for($x=0; $x<count($b2F); $x++) {
  if(is_null($b2F[$x][3])) {
    $b2F[$x] = [$b2R[$x][0], $b2F[$x][1], $b2F[$x][2], 0];
  }
}

$q7 = "select
   CF3, 'New', count(*)
from SO join CustomFldPt on (CustomFldPt.PtKey=SO.PtKey) join Doctor on (SO.OrderingDocKey=Doctor.DocKey)
where
    SOCreateDt > '{$startDate}' and
    SOCreateDt < '{$endDate}' and
    (SOConfirmDt is null or SOConfirmDt > '{$endDate}') and
    SOClassification in ('New Pump Order','Non-Hygeia Pump Order') and
    (Doctor.MktRepKey is null or Doctor.MktRepKey in (101,106,107)) and
    CF3 not in ('Moms Get More','momsgetmore.com','OB Office','OB Office - EMR','OB Office - Text','OB Office- Brochure','OB Office- MGM','OB Office- Tear Sheet','OB portal')
group by CF3";

$q7r = $conn->query($q7);

$b3F = ["Facebook", "Google", "HH", "Insurance gave phone #", "WWW", "Zeeto"];
$b3R = [["Facebook",0], ["Google", 1], ["HH", 2], ["Insurance gave phone #",3], ["WWW",4], ["Zeeto",5]];


if($q7r->num_rows > 0) {
  while($row = $q7r->fetch_assoc()) {
    if($row["CF3"] == "FB" or $row["CF3"] == "Facebook") {
      $b3F[0] = [$b3R[0][0], $row["count(*)"]];
    } elseif($row["CF3"] == "Ads" or $row["CF3"] == "Google") {
      $b3F[1] = [$b3R[1][0], $row["count(*)"]];
    } elseif($row["CF3"] == "HH") {
      $b3F[2] = [$b3R[2][0], $row["count(*)"]];
    } elseif($row["CF3"] == "Insurance gave phone #") {
      $b3F[3] = [$b3R[3][0], $row["count(*)"]];
    } elseif($row["CF3"] == "WWW") {
      $b3F[4] = [$b3R[4][0], $row["count(*)"]];
    } elseif($row["CF3"] == "Zeeto") {
      $b3F[5] = [$b3R[5][0], $row["count(*)"]];
    }
  }
}

$q8 = "select
 CF3, 'Closed', count(*)
from SO join CustomFldPt on (CustomFldPt.PtKey=SO.PtKey) join Doctor on (SO.OrderingDocKey=Doctor.DocKey)
where
    SOCreateDt > '{$startDate}' and
    SOCreateDt < '{$endDate}' and
    SOConfirmDt is not null and
    SOConfirmDt  < '{$endDate}' and
    SOClassification in ('New Pump Order','Non-Hygeia Pump Order') and
    (Doctor.MktRepKey is null or Doctor.MktRepKey in (101,106,107)) and
    CF3 not in ('Moms Get More','momsgetmore.com','OB Office','OB Office - EMR','OB Office - Text','OB Office- Brochure','OB Office- MGM','OB Office- Tear Sheet','OB portal')
group by CF3,SOStatus";
$q8r = $conn->query($q8);

if($q8r->num_rows > 0) {

  while($row = $q8r->fetch_assoc()) {
    if($row["CF3"] == "FB" or $row["CF3"] == "Facebook") {
      $b3F[0] = [$b3R[0][0], $b3F[0][1], $row["count(*)"]];
    } elseif($row["CF3"] == "Ads" or $row["CF3"] == "Google") {
      $b3F[1] = [$b3R[1][0], $b3F[1][1], $row["count(*)"]];
    } elseif($row["CF3"] == "HH") {
      $b3F[2] = [$b3R[2][0], $b3F[2][1], $row["count(*)"]];
    } elseif($row["CF3"] == "Insurance gave phone #") {
      $b3F[3] = [$b3R[3][0], $b3F[3][1], $row["count(*)"]];
    } elseif($row["CF3"] == "WWW") {
      $b3F[4] = [$b3R[4][0], $b3F[4][1], $row["count(*)"]];
    } elseif($row["CF3"] == "Zeeto") {
      $b3F[5] = [$b3R[5][0], $b3F[5][1], $row["count(*)"]];
    }
  }

}

for($x=0; $x<count($b3F); $x++) {
  if(is_null($b3F[$x][3])) {
    $b3F[$x] = [$b3R[$x][0], $b3F[$x][1], $b3F[$x][2], 0];
  }
}

$q9 = "select
    CF3, 'New', count(*)
from SOVoid
    join CustomFldPt on (CustomFldPt.PtKey=SOVoid.PtKey) join Doctor on (SOVoid.OrderingDocKey=Doctor.DocKey)
where
    CreateDt > '{$startDate}' and
    CreateDt < '{$endDate}' and
    VoidedDt < '{$endDate}' and
    SOClassification in ('New Pump Order','Non-Hygeia Pump Order') and
    (Doctor.MktRepKey is null or
	   Doctor.MktRepKey in (101,106,107)) and
    CF3 not in ('Moms Get More','momsgetmore.com','OB Office','OB Office - EMR','OB Office - Text','OB Office- Brochure','OB Office- MGM','OB Office- Tear Sheet','OB portal')
group by CF3";

$q9r = $conn->query($q9);

if($q9r->num_rows > 0) {
  while($row = $q9r->fetch_assoc()) {
    if($row["CF3"] == "FB" or $row["CF3"] == "Facebook") {
      $b3F[0] = [$b3R[0][0], $b3F[0][1], $b3F[0][2], $row["count(*)"]];
    } elseif($row["CF3"] == "Ads" or $row["CF3"] == "Google") {
      $b3F[1] = [$b3R[1][0], $b3F[1][1], $b3F[1][2], $row["count(*)"]];
    } elseif($row["CF3"] == "HH") {
      $b3F[2] = [$b3R[2][0], $b3F[2][1], $b3F[2][2], $row["count(*)"]];
    } elseif($row["CF3"] == "Insurance gave phone #") {
      $b3F[3] = [$b3R[3][0], $b3F[3][1], $b3F[3][2], $row["count(*)"]];
    } elseif($row["CF3"] == "WWW") {
      $b3F[4] = [$b3R[4][0], $b3F[4][1], $b3F[4][2], $row["count(*)"]];
    } elseif($row["CF3"] == "Zeeto") {
      $b3F[5] = [$b3R[5][0], $b3F[5][1], $b3F[5][2], $row["count(*)"]];
    }
  }
}

$b1Totals = [["New", 0], ["Shipped", 0], ["Voided", 0], ["Total", 0]];
for($x=0; $x<count($b1F); $x++) {
  $b1Totals[0] = ["New", $b1Totals[0][1]+$b1F[$x][1]];
  $b1Totals[1] = ["Shipped", $b1Totals[1][1]+$b1F[$x][2]];
  $b1Totals[2] = ["Voided", $b1Totals[2][1]+$b1F[$x][3]];
  $b1Totals[3] = ["Total", $b1Totals[3][1]+$b1F[$x][1]+$b1F[$x][2]+$b1F[$x][3]];
}

$b2Totals = [["New", 0], ["Shipped", 0], ["Voided", 0], ["Total", 0]];
for($x=0; $x<count($b2F); $x++) {
  $b2Totals[0] = ["New", $b2Totals[0][1]+$b2F[$x][1]];
  $b2Totals[1] = ["Shipped", $b2Totals[1][1]+$b2F[$x][2]];
  $b2Totals[2] = ["Voided", $b2Totals[2][1]+$b2F[$x][3]];
  $b2Totals[3] = ["Total", $b2Totals[3][1]+$b2F[$x][1]+$b2F[$x][2]+$b2F[$x][3]];
}

$b3Totals = [["New", 0], ["Shipped", 0], ["Voided", 0], ["Total", 0]];
for($x=0; $x<count($b3F); $x++) {
  $b3Totals[0] = ["New", $b3Totals[0][1]+$b3F[$x][1]];
  $b3Totals[1] = ["Shipped", $b3Totals[1][1]+$b3F[$x][2]];
  $b3Totals[2] = ["Voided", $b3Totals[2][1]+$b3F[$x][3]];
  $b3Totals[3] = ["Total", $b3Totals[3][1]+$b3F[$x][1]+$b3F[$x][2]+$b3F[$x][3]];
}


$runningTotal = $b1Totals[3][1] + $b2Totals[3][1] + $b3Totals[3][1];

$q7 = "select count(*) from SO where date(SOActualDeliveryDt) >= '{$startDate}' and date(SOActualDeliveryDt) <= '{$endDate}' and date(SOActualDeliveryDt) >= date(SOCreateDt) and SOStatus in ('Closed','Delivered') and SOClassification in ('New Pump Order','Non-Hygeia Pump Order');";
$q7r = $conn->query($q7);

$shippedTotal = 0;

if($q7r->num_rows > 0) {
  while($row = $q7r->fetch_assoc()) {
    $shippedTotal = $row["count(*)"];
  }
}

?>