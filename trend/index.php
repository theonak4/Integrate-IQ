<?php

/*

  TODO:

  - Fix comparison innacuarcies
  - Ask Tyler to authorize new IP on DW


*/

$DB_HOST = "34.232.48.193";
$DB_USER = "hgdwreadwrite";
$DB_PASS = "1vmiWAqndlX1";
$DB_NAME = "hygeiabtdw-v3";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
} else {

}

$dateRanges = explode(" - ", $_POST["daterange"]);
$startRange = "2020-01-01";
$endRange = "2021-01-01";

$compareRanges = explode(" - ", $_POST["daterange2"]);
$compareStart = "";
$compareEnd = "";

if(isset($_POST["daterange"])) {
  $startRange = date("Y-m-d", strtotime($dateRanges[0]));
  $endRange = date("Y-m-d", strtotime($dateRanges[1]));
}

if(isset($_POST["daterange2"])) {
  $compareStart = date("Y-m-d", strtotime($compareRanges[0]));
  $compareEnd = date("Y-m-d", strtotime($compareRanges[1]));
}

$sameYear = false;
if(date("Y", strtotime($compareRanges[0])) == date("Y", strtotime($dateRanges[0]))) {
  $sameYear = true;
}

$salesReps = array(
    125 => "CA - Ann Freeman",
    110 => "CA - April Williams",
    111 => "CA - Diana Vial",
    104 => "CA - Marcy Ziesing",
    131 => "TX - Aly Gilmartin",
    133 => "TX - Angela Thayer",
    132 => "TX - Brittany Burris",
    129 => "TX - Jessica Lewin",
    117 => "TX - Katherine Nevins",
    113 => "TX - Melanie Benfield",
    120 => "TX - Nikki Andazola",
);

$salesRepFilter = "";
$leadSourceFilter = "";
$viewingBy = "All SR / All LS";

if($_POST["viewBy"] == 2) {
    $name = $salesReps[$_POST["salesRep"]];
    $viewingBy = "Sales Rep ({$name})";
    $salesRepFilter = "MktRepKey = {$_POST['salesRep']} AND";
} elseif($_POST["viewBy"] == 3) {
    $viewingBy = "Lead Source ({$_POST['leadSource']})";
    $leadSourceFilter = "CF3 = '{$_POST['leadSource']}' AND";
} elseif($_POST["viewBy"] == 4) {
    $name = $salesReps[$_POST["salesRep"]];
    $ls = $_POST["leadSource"];
    $viewingBy = "Sales Rep ({$name}) & Lead Source ({$ls})";
    $salesRepFilter = "MktRepKey = {$_POST['salesRep']} AND";
    $leadSourceFilter = "CF3 = '{$_POST['leadSource']}' AND";
}

$startDisplay = date("F jS, Y", strtotime($startRange));
$endDisplay = date("F jS, Y", strtotime($endRange));

echo "Viewing by <strong>{$viewingBy}</strong> for {$startDisplay} through {$endDisplay} <br><br>";

/* NORMAL QUERIES */
$getVoided = $conn->query("select
  MONTH(CreateDt) as 'Month', DATE(CreateDt) as 'Date', count(*) as 'COUNT'
  from SOVoid join CustomFldPt on (CustomFldPt.PtKey=SOVoid.PtKey)
  where
    CreateDt > '2020-01-01' and {$salesRepFilter} {$leadSourceFilter}
    CreateDt < '2021-01-01'
  group by MONTH(CreateDt), DAY(CreateDt)");
$getNew = $conn->query("select
	MONTH(SOCreateDt) as 'Month', DATE(SOCreateDt) as 'Date', count(*) as 'COUNT'
from SO join CustomFldPt on (CustomFldPt.PtKey=SO.PtKey)
where
	SOCreateDt > '2020-01-01' and {$salesRepFilter} {$leadSourceFilter}
    SOCreateDt < '2021-01-01' and
    (SOConfirmDt is null or SOConfirmDt > '2020-01-01') and
    SOClassification in ('New Pump Order','Non-Hygeia Pump Order')
group by MONTH(SOCreateDt), DAY(SOCreateDt)");
$getShipped = $conn->query("select
	MONTH(SOCreateDt) as 'Month', DATE(SOCreateDt) as 'Date', count(*) as 'COUNT'
from SO join CustomFldPt on (CustomFldPt.PtKey=SO.PtKey)
where
	SOCreateDt > '2020-01-01' and {$salesRepFilter} {$leadSourceFilter}
    SOCreateDt < '2021-01-01' and
	SOConfirmDt is not null and SOConfirmDt  < '2021-01-01' and
    SOClassification in ('New Pump Order','Non-Hygeia Pump Order')
group by MONTH(SOCreateDt), DAY(SOCreateDt)");

/* COMPARISON QUERIES */
$getVoidedC = $conn->query("select
  MONTH(CreateDt) as 'Month', DATE(CreateDt) as 'Date', count(*) as 'COUNT'
  from SOVoid join CustomFldPt on (CustomFldPt.PtKey=SOVoid.PtKey)
  where
    CreateDt > '{$compareStart}' and {$salesRepFilter} {$leadSourceFilter}
    CreateDt < '{$compareEnd}'
  group by MONTH(CreateDt), DAY(CreateDt) LIMIT 29");
$getNewC = $conn->query("select
	MONTH(SOCreateDt) as 'Month', DATE(SOCreateDt) as 'Date', count(*) as 'COUNT'
from SO join CustomFldPt on (CustomFldPt.PtKey=SO.PtKey)
where
	SOCreateDt > '{$compareStart}' and {$salesRepFilter} {$leadSourceFilter}
    SOCreateDt < '{$compareEnd}' and
    (SOConfirmDt is null or SOConfirmDt > '{$compareEnd}') and
    SOClassification in ('New Pump Order','Non-Hygeia Pump Order')
group by MONTH(SOCreateDt), DAY(SOCreateDt) LIMIT 29");
$getShippedC = $conn->query("select
	MONTH(SOCreateDt) as 'Month', DATE(SOCreateDt) as 'Date', count(*) as 'COUNT'
from SO join CustomFldPt on (CustomFldPt.PtKey=SO.PtKey)
where
	SOCreateDt > '{$compareStart}' and {$salesRepFilter} {$leadSourceFilter}
    SOCreateDt < '{$compareEnd}' and
	SOConfirmDt is not null and SOConfirmDt  < '{$compareEnd}' and
    SOClassification in ('New Pump Order','Non-Hygeia Pump Order')
group by MONTH(SOCreateDt), DAY(SOCreateDt) LIMIT 29");


?>
<html>
<head>
  <title>Trending</title>
  <script type="text/javascript" src="https://unpkg.com/vis-timeline@7.3.10/standalone/umd/vis-timeline-graph2d.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
  <style>
  body, html {
    font-family: "DM Sans";
    overflow: hidden;
  }
  .vis-legend {
    box-shadow: 0px 0px;
    border: 0;
    border-left: 3px solid #F49831;
  }
  .vis-minor {
    font-weight: 700;
  }

  /* DEFAULT STYLES */
  .voidedStyle {
  fill: #FFCCCB;
  fill-opacity:0;
  stroke-width:2px;
  stroke: #FFCCCB;
  }

  .createdStyle {
    fill: #98FB98;
    fill-opacity:0;
    stroke-width:2px;
    stroke: #98FB98;
  }

  .shippedStyle {
    fill: #FAC881;
    fill-opacity:0;
    border-style: dashed;
    stroke-width:2px;
    stroke: #FAC881;
  }

  /* COMPARISON STYLES */

  .voidedStyle_compare {
  fill: none;
  fill-opacity:0;
  stroke-width:2px;
  stroke: red;
  stroke-dasharray: 4;
  }

  .createdStyle_compare  {
    fill: none;
    fill-opacity:0;
    stroke-width:2px;
    stroke: red;
    stroke-dasharray: 4;
  }

  .shippedStyle_compare  {
    fill: none;
    fill-opacity:0;
    stroke-width:2px;
    stroke: red;
    stroke-dasharray: 4;
  }

  #visualization {
    width: 100%;
    height: 100%;
    margin-top: 10;
  }

  .daterangepicker {
    border-radius: 0;
    border-bottom: 3px solid #F49831;
  }

  .btn-primary {
    background: #F49831;
    border: 0;
    border-radius: 0;
  }

  input {
    text-align: center;
    border: 1px solid #d3d3d3;
    border-left: 5px solid #F49831;
  }

  input:focus {
    outline: none !important;
    border: 1px solid #d3d3d3;
    border-left: 5px solid #F49831;
  }
  </style>

</head>
<body>
  Jump to <input type="text" name="jumpTo" value="<?php echo date("m/d/Y"); ?>" />
  <div id="visualization"></div>
  <script>




var groups = new vis.DataSet([
  {id: 'voided', content: 'Voided Orders', className: 'voidedStyle'},
  {id: 'created', content: 'New Orders', className: 'createdStyle'},
  {id: 'shipped', content: 'Shipped Orders', className: 'shippedStyle'},
  {id: 'voided_c', content: 'Voided Orders <?php if($_POST["compareTo"]) { echo substr($compareStart, 0, 4); } ?>', className: 'voidedStyle_compare'},
  {id: 'created_c', content: 'New Orders <?php if($_POST["compareTo"]) { echo substr($compareStart, 0, 4); } ?>', className: 'createdStyle_compare'},
  {id: 'shipped_c', content: 'Shipped Orders <?php if($_POST["compareTo"]) { echo substr($compareStart, 0, 4); } ?>', className: 'shippedStyle_compare'}
]);

var container = document.getElementById("visualization");
var items = [
 <?php
    if($getVoided->num_rows > 0) {
      while($row = $getVoided->fetch_assoc()) {
        $date = $row["Date"];
        $count = $row["COUNT"];
        echo "{x: '{$date}', y: {$count}, label: { content: '{$count}', yOffset: 20}, group: 'voided'},";
      }
    }

    if($getNew->num_rows > 0) {
      while($row = $getNew->fetch_assoc()) {
        $date = $row["Date"];
        $count = $row["COUNT"];
        echo "{x: '{$date}', y: {$count}, label: { content: '{$count}', yOffset: -10}, group: 'created'},";
      }
    }

    if($getShipped->num_rows > 0) {
      while($row = $getShipped->fetch_assoc()) {
        $date = $row["Date"];
        $count = $row["COUNT"];
        echo "{x: '{$date}', y: {$count}, label: { content: '{$count}'}, group: 'shipped'},";
      }
    }
    /* Comparisons */

    if($_POST["compareTo"] == true) {
      if($_POST["compareToType"] == "voided") {
      if($getVoidedC->num_rows > 0) {
        while($row = $getVoidedC->fetch_assoc()) {
          $date = $row["Date"];
          if($sameYear) {
          $date = substr($dateRanges[0], 6, 4) . "-" . substr($dateRanges[0], 0, 2) . "-" . substr($date, 8);
          } else {
          $date = substr($startRange, 0, 4) . substr($date, 4);
          }
          $count = $row["COUNT"];
          echo "{x: '{$date}', y: {$count}, label: { content: '{$count}', yOffset: 20}, group: 'voided_c'},";
        }
      }
    } elseif($_POST["compareToType"] == "new") {

      if($getNewC->num_rows > 0) {
        while($row = $getNewC->fetch_assoc()) {
          $date = $row["Date"];
          if($sameYear) {
          $date = substr($dateRanges[0], 6, 4) . "-" . substr($dateRanges[0], 0, 2) . "-" . substr($date, 8);
          } else {
          $date = substr($startRange, 0, 4) . substr($date, 4);
          }
          $count = $row["COUNT"];
          echo "{x: '{$date}', y: {$count}, label: { content: '{$count}', yOffset: 20}, group: 'created_c'},";
        }
      }
    } elseif($_POST["compareToType"] == "shipped") {

      if($getShippedC->num_rows > 0) {
        while($row = $getShippedC->fetch_assoc()) {
          $date = $row["Date"];
          if($sameYear) {
          $date = substr($dateRanges[0], 6, 4) . "-" . substr($dateRanges[0], 0, 2) . "-" . substr($date, 8);
          } else {
          $date = substr($startRange, 0, 4) . substr($date, 4);
          }
          $count = $row["COUNT"];
          echo "{x: '{$date}', y: {$count}, label: { content: '{$count}', yOffset: 20}, group: 'shipped_c'},";
        }
      }
    }
    }

 ?>
];

var dataset = new vis.DataSet(items);
var options = {
  legend: true,
  start: "2020-07-01",
  end: "2021-01-01",
  min: "<?php echo $startRange; ?>",
  max: "<?php echo $endRange; ?>",
  zoomMax: 4000000000,
  zoomMin: 1000000000,
  drawPoints: {
    size: 5,
    style: "circle"
  },
  shaded: true,
  zoomKey: "ctrlKey",
  horizontalScroll: true
};
var graph2d = new vis.Graph2d(container, dataset, groups, options);

$(function() {
$('input[name="jumpTo"]').daterangepicker({
  singleDatePicker: true,
  showDropdowns: true,
  minYear: 1901,
  maxYear: parseInt(moment().format('YYYY'),10)
}, function(start, end, label) {
  graph2d.moveTo(start.format('YYYY-MM-DD'));
});
});

  </script>
</body>
</html>
