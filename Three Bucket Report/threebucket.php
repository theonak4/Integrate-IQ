<!--

THREE BUCKET REPORT
Version 0.1.2
Theo Nakfoor

LAST UPDATE: 9/1/2020

TODO:
  - Minimize queries & initial PHP to shorten loading time.
  - Add "expand" to buckets 2 and 3.
  - Add month labels to bars in previous comparison charts !IMPORTANT!

RECENT CHANGES:
  - Added Chart.js support.
  - Added catch clause & warning to stop from displaying bad data for first 3 days of new Month.
  - Added previous month comparisons.
  - Changed indicator arrows to thumbs up/down and changed logic for displaying icons.

-->

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<?php
include 'ASEngine/AS.php';

if (! app('login')->isLoggedIn()) {
    redirect("login.php");
}

$currentUser = app('current_user');

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
error_reporting(0);

include '3BSQL.php';

?>

<head>
<title>3 Bucket Report</title>
<style>
html,body {
  font-family: Montserrat;
}

td:hover {
  background: transparent;
  color: black;
}

table:hover {
  background-color: white !important;
}

</style>

<script>
$(function() {
  $('input[name="daterange"]').daterangepicker({
    opens: 'left'
  }, function(start, end, label) {
    console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
    $(location).attr('href', 'http://reports.hygeiahealth.com/threebucket.php?startDate=' + start.format('YYYY-MM-DD') + '&endDate=' + end.format('YYYY-MM-DD'));
  });
});
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
</script>
<script>

function toggleCalc() {
  if(document.getElementById("displayer").style.display == "none") {
    document.getElementById("toggler").innerHTML = "<strong>hide calculations</strong>";
    document.getElementById("displayer").style.display = "inline";
  } else {
    document.getElementById("toggler").innerHTML = "<strong>show calculations</strong>";
    document.getElementById("displayer").style.display = "none";
  }
}

function toggleB1(x) {
  ename = "bucket1Content" + x;
  tname = "bucket1Toggle" + x;
  if(document.getElementById(ename).style.display == "none") {
        document.getElementById(ename).style.display = null;
        document.getElementById(tname).innerHTML = "Collapse";
  } else {
    document.getElementById(ename).style.display = "none";
    document.getElementById(tname).innerHTML = "Expand";
  }
}

</script>

</head>

<body>
<br>
<div style="position: absolute; left: 20.5%;">
<h1>Three Bucket Report <small><strong>PAST VIEW</strong><img src="https://img.pngio.com/past-free-icon-of-news-and-media-icons-past-png-512_512.png" style="width: 25; margin-left: 5; margin-bottom: 3;"></img></small> <small style="font-size: 15;">Beta v0.1.2</small></h1>
<hr style="">
<strong>Date Range</strong><br>
<input name="daterange" type="text" value="<?php echo $startDateRange . " - " . $endDateRange; ?>" style="margin-top: 10; width: 300; padding: 20; text-align: center; border: 1px solid black;"></input><button style="margin-left: 10; margin-right: 5; padding: 20; width: 150; text-align: center; border: 1px solid lightblue; background: lightblue; color: white;"><strong>SUBMIT</strong><img src="https://cdn.iconscout.com/icon/free/png-256/right-arrow-1767497-1502505.png" style="width: 18; filter: invert(100%); margin-left: 5px"></img></button><button id="resetbutton" onclick="window.location.href='https://reports.hygeiahealth.com/threebucket.php'" style="margin-left: 5; margin-right: 20; padding: 20; width: 150; text-align: center; border: 1px solid red; background: red; color: white;"><strong>RESET</strong><img id="resetimg" src="https://icon-library.com/images/reset-icon-png/reset-icon-png-4.jpg" style="width: 18; filter: invert(100%); margin-left: 10px"></img></button>

<?php
  echo "Displaying data from <strong>" . date("F jS, Y", strtotime($startDate)) . "</strong> through <strong>" . date("F jS, Y", strtotime($endDate)) . "</strong>";
?>

</div>
<center>
<br><br><br><br><br><br><br><br><br><br><br>
<table style="width:1300px">
  <tr style="background: white; border-top: 0;">
    <th></th>
    <th><center>A</center></th>
    <th><center>B</center></th>
    <th width="100px"><center>C</center></th>
    <th width="100px"><center>D</center></th>
    <th width="100px"><center>E</center></th>
    <th width="50px"><center>F</center></th>
    <th><center>G</center></th>
    <th><center>H</center></th>
    <th><center>I</center></th>
    <th></th>
  </tr>
  <tr style="background: black; color: white">
    <th></th>
    <th><center>Bucket</center></th>
    <th><center>Leadsource</center></th>
    <th width="100px"><center>New</center></th>
    <th width="100px"><center>Shipped</center></th>
    <th width="100px"><center>Voided</center></th>
    <th width="50px"><center>Total</center></th>
    <th><center>Lead Source Percent<br> of Bucket Apps</center></th>
    <th><center>Lead Source Percent<br> of Total Apps</center></th>
    <th><center>Void Rate</center></th>
    <th></th>
  </tr>
  <?php

  $idx = 1;
  $lstTotals1 = 0;
  $idx1 = 0;
  for($x=0; $x<count($b1F); $x++) {

  ?>
    <tr>
      <td style="background: black; color: white; font-weight: 600;"><?php echo $idx; ?></td>
      <td>Bucket 1</td>
      <td><?php echo $b1R[$idx1][0]; ?></td>
      <td style="background: #98FB98"><center><?php if(is_numeric($b1F[$x][1])) { echo $b1F[$x][1]; } else { $b1F[$x][1] = 0; echo $b1F[$x][1]; }  ?></center></td>
      <td style="background: #FAC881"><center><?php if(is_numeric($b1F[$x][2])) { echo $b1F[$x][2]; } else { $b1F[$x][2] = 0; echo $b1F[$x][2]; }  ?></center></td>
      <td style="background: #FFCCCB"><center><?php if(is_numeric($b1F[$x][3])) { echo $b1F[$x][3]; } else { $b1F[$x][3] = 0; echo $b1F[$x][3]; }  ?></center></td>
      <td style="background: 	#686868; color: white;"><center><strong><?php echo $b1F[$x][1]+$b1F[$x][2]+$b1F[$x][3];?></strong></center></td>
      <td><center><?php echo round((($b1F[$x][1]+$b1F[$x][2]+$b1F[$x][3])/$b1Totals[3][1])*100, 2);?>%</center></td>
      <td><center><?php echo round((($b1F[$x][1]+$b1F[$x][2]+$b1F[$x][3])/$runningTotal)*100, 2);?>%</center></td>
      <?php $lstTotals1 = $lstTotals1 + round((($b1F[$x][1]+$b1F[$x][2]+$b1F[$x][3])/$runningTotal)*100, 2); ?>
      <td><center><?php  echo round((($b1F[$x][3]/($b1F[$x][1]+$b1F[$x][2]+$b1F[$x][3]))*100), 2); ?>%</center></td>
      <td><a onclick="toggleB1(<?php echo $x; ?>)" id="bucket1Toggle<?php echo $x; ?>">Expand</a></td>
    </tr>
    <tr style="display: none;" id="bucket1Content<?php echo $x; ?>">
      <td style="background: black;"></td>
      <td colspan=10>
        <div style="width: 100%; background: white; color: black; padding: 10; padding-left: 15;">
          <small><strong style="text-transform: uppercase;"><?php echo $b1R[$idx1][0]; ?></strong> (<?php echo date("F Y",strtotime($startDate)); ?> vs. <?php echo date("F Y",strtotime($startDateL)); ?>) <a>Change comparison month</a> <a onclick="toggleB1(<?php echo $x; ?>)" style="float: right;" ><img src="icons/x-circle.svg" style="width: 20;"></img></a></small>
          <br>
          <br>
          <div style="width: 100%; margin-bottom: 20;">
            <div style="float: left; width: 33.33%; height: 20;"><center>
              <h1 style="display: inline; ">
                <span style="font-size: 17; font-weight: 600;">NEW</span>
                <strong style="color: #98FB98; margin-right: 5;"><?php if(is_numeric($b1F[$x][1])) { echo $b1F[$x][1]; } else { $b1F[$x][1] = 0; echo $b1F[$x][1]; }  ?></strong>
                <strong style="color: black; font-style: italic; border-left: 1px solid black; padding-left: 15;"> <?php if(is_numeric($b1FL[$x][1])) { echo $b1FL[$x][1]; } else { $b1FL[$x][1] = 0; echo $b1FL[$x][1]; }  ?></strong>
                <?php if(!$b1F[$x][1] > $b1FL[$x][1]) { echo "<img src='icons-new/hand-thumbs-up.svg' style='width: 30; filter: invert(17%) sepia(73%) saturate(3524%) hue-rotate(98deg) brightness(97%) contrast(105%);'></img>";  } else { echo "<img src='icons-new/hand-thumbs-down.svg' style='width: 30; filter: invert(8%) sepia(69%) saturate(5314%) hue-rotate(356deg) brightness(113%) contrast(112%);'></img>"; }?>
                </h1>
            </center></div>
            <div style="float: left; width: 33.33%;height: 20;"><center>
              <h1 style="display: inline; margin-left: 20;">
                <span style="font-size: 17;  font-weight: 600;">SHIPPED</span>
                <strong style="color: #FAC881; margin-right: 5;"><?php if(is_numeric($b1F[$x][2])) { echo $b1F[$x][2]; } else { $b1F[$x][2] = 0; echo $b1F[$x][2]; }  ?></strong>
                <strong style="color: black; font-style: italic; border-left: 1px solid black; padding-left: 15;"> <?php if(is_numeric($b1FL[$x][2])) { echo $b1FL[$x][2]; } else { $b1FL[$x][2] = 0; echo $b1FL[$x][2]; }  ?></strong>
                <?php if($b1F[$x][2] > $b1FL[$x][2]) { echo "<img src='icons-new/hand-thumbs-up.svg' style='width: 30; filter: invert(17%) sepia(73%) saturate(3524%) hue-rotate(98deg) brightness(97%) contrast(105%);'></img>";  } else { echo "<img src='icons-new/hand-thumbs-down.svg' style='width: 30; filter: invert(8%) sepia(69%) saturate(5314%) hue-rotate(356deg) brightness(113%) contrast(112%);'></img>"; }?>
                </h1>
            </center></div>
            <div style="float: left; width: 33.33%;height: 20;"><center>
              <h1 style="display: inline; margin-left: 20;">
                <span style="font-size: 17; font-weight: 600;">VOIDED</span>
                <strong style="color: #FFCCCB; margin-right: 5;"><?php if(is_numeric($b1F[$x][3])) { echo $b1F[$x][3]; } else { $b1F[$x][3] = 0; echo $b1F[$x][3]; }  ?></strong>
                <strong style="color: black; font-style: italic; border-left: 1px solid black; padding-left: 15;"><?php if(is_numeric($b1FL[$x][3])) { echo $b1FL[$x][3]; } else { $b1FL[$x][3] = 0; echo $b1FL[$x][3]; }  ?></strong>
                <?php if($b1F[$x][3] < $b1FL[$x][3]) { echo "<img src='icons-new/hand-thumbs-up.svg' style='width: 30; filter: invert(17%) sepia(73%) saturate(3524%) hue-rotate(98deg) brightness(97%) contrast(105%);'></img>";  } else { echo "<img src='icons-new/hand-thumbs-down.svg' style='width: 30; filter: invert(8%) sepia(69%) saturate(5314%) hue-rotate(356deg) brightness(113%) contrast(112%);'></img>"; }?>
                </h1>
            </center></div>
            <br style="clear: left;" />
          </div>
          <br>
          <canvas id="chartBucket1ID<?php echo $x; ?>"></canvas>
          <small><strong>Solid:</strong> <?php echo date("F Y",strtotime($startDate)); ?> <strong>Transparent:</strong> <?php echo date("F Y",strtotime($startDateL)); ?></small>
          <script>
          var ctx = document.getElementById("chartBucket1ID<?php echo $x; ?>").getContext("2d");
          var data = {
            labels: ["NEW", "SHIPPED", "VOIDED"],
            datasets: [{
              label: "<?php echo date("F",strtotime($startDate)); ?>",
              backgroundColor: ["#98FB98", "#FAC881", "#FFCCCB"],
              data: [<?php if(is_numeric($b1F[$x][1])) { echo $b1F[$x][1]; } else { $b1F[$x][1] = 0; echo $b1F[$x][1]; }  ?>, <?php if(is_numeric($b1F[$x][2])) { echo $b1F[$x][2]; } else { $b1F[$x][2] = 0; echo $b1F[$x][2]; }  ?>, <?php if(is_numeric($b1F[$x][3])) { echo $b1F[$x][3]; } else { $b1F[$x][3] = 0; echo $b1F[$x][3]; }  ?>]
            }, {
              label: "<?php echo date("F",strtotime($startDateL)); ?>",
              backgroundColor: ["rgba(152, 251, 152, 0.2)", "rgba(250, 200, 129, 0.2)", "rgba(255, 204, 203, 0.2)"],
              borderColor: ["#98FB98", "#FAC881", "#FFCCCB"],
              borderWidth: "3",
              data: [<?php if(is_numeric($b1FL[$x][1])) { echo $b1FL[$x][1]; } else { $b1FL[$x][1] = 0; echo $b1FL[$x][1]; }  ?>, <?php if(is_numeric($b1FL[$x][2])) { echo $b1FL[$x][2]; } else { $b1FL[$x][2] = 0; echo $b1FL[$x][2]; }  ?>, <?php if(is_numeric($b1FL[$x][3])) { echo $b1FL[$x][3]; } else { $b1FL[$x][3] = 0; echo $b1FL[$x][3]; }  ?>]
            }]
          };
          var myBarChart = new Chart(ctx, {
            type: 'bar',
            data: data,
            options: {
              barValueSpacing: 20,
              scales: {
                yAxes: [{
                  ticks: {
                    min: 0,
                  }
                }]
              },
              legend: {
                display: false
              }
            }
          });
          </script>
          <br>
        </div>
      </td>

    </tr>
  <?php
    $idx++;
    $idx1++;
  }
  ?>
  <tr style="background: black; color: white">
    <th><?php echo $idx; $idx++; ?></th>
    <th><center>Bucket 1 Totals</center></th>
    <th><center>/</center></th>
    <th><center><?php echo $b1Totals[0][1]; ?></center></th>
    <th><center><?php echo $b1Totals[1][1]; ?></center></th>
    <th><center><?php echo $b1Totals[2][1]; ?></center></th>
    <th><center><?php echo $b1Totals[3][1]; ?></center></th>
    <th><center>100%</center></th>
    <th><center><?php echo $lstTotals1; ?>%</center></th>
    <th><center><?php echo round((($b1Totals[2][1]/$b1Totals[3][1])*100), 2);?>%</center></th>
    <th></th>
  </tr>
  <?php

  $lstTotals2 = 0;
  $idx2 = 0;

  for($x=0; $x<count($b2F); $x++) {
  ?>
    <tr>
      <td style="background: black; color: white; font-weight: 600;"><?php echo $idx; ?></td>
      <td>Bucket 2</td>
      <td><?php echo $b2R[$idx2][0]; ?></td>
      <td style="background: 	#98FB98"><center><?php if(is_numeric($b2F[$x][1]) && $b2F[$x][1] > 0) { echo $b2F[$x][1]; } else { $b2F[$x][1] = 0; echo $b2F[$x][1]; } ?></center></td>
      <td style="background: #FAC881"><center><?php  if(is_numeric($b2F[$x][2]) && $b2F[$x][1] > 0) { echo $b2F[$x][2]; } else { $b2F[$x][2] = 0; echo $b2F[$x][2]; }  ?></center></td>
      <td style="background: #FFCCCB"><center><?php  if(is_numeric($b2F[$x][3]) && $b2F[$x][1] > 0) { echo $b2F[$x][3]; } else { $b2F[$x][3] = 0; echo $b2F[$x][3]; }   ?></center></td>
      <td  style="background: 	#686868; color: white;"><center><strong><?php echo $b2F[$x][1]+$b2F[$x][2]+$b2F[$x][3];?></strong></center></td>
      <td><center><?php echo round((($b2F[$x][1]+$b2F[$x][2]+$b2F[$x][3])/$b2Totals[3][1])*100, 2);?>%</center></td>
      <td><center><?php echo round((($b2F[$x][1]+$b2F[$x][2]+$b2F[$x][3])/$runningTotal)*100, 2);?>%</center></td>
      <?php $lstTotals2 = $lstTotals2 + round((($b2F[$x][1]+$b2F[$x][2]+$b2F[$x][3])/$runningTotal)*100, 2); ?>
      <td><center><?php  echo round((($b2F[$x][3]/($b2F[$x][1]+$b2F[$x][2]+$b2F[$x][3]))*100), 2); ?>%</center></td>
    </tr>
  <?php
    $idx++;
    $idx2++;
  }
  ?>
  <tr style="background: black; color: white">
    <th><?php echo $idx; $idx++; ?></th>
    <th><center>Bucket 2 Totals</center></th>
    <th><center>/</center></th>
    <th><center><?php echo $b2Totals[0][1]; ?></center></th>
    <th><center><?php echo $b2Totals[1][1]; ?></center></th>
    <th><center><?php echo $b2Totals[2][1]; ?></center></th>
    <th><center><?php echo $b2Totals[3][1]; ?></center></th>
    <th><center>100%</center></th>
    <th><center><?php echo $lstTotals2; ?>%</center></th>
    <th><center><?php echo round((($b2Totals[2][1]/$b2Totals[3][1])*100), 2);?>%</center></th>
    <th></th>
  </tr>
    <tr>
      <td style="background: black; color: white; font-weight: 600;"><?php echo $idx; ?></td>
      <td>Bucket 3</td>
      <td><?php echo $b3R[$idx3][0]; ?></td>
      <td style="background: 	#98FB98"><center><?php if(is_numeric($b3F[$x][1]) && $b3F[$x][1] > 0) { echo abs($b3F[$x][1]); } else { $b3F[$x][1] = 0; echo $b3F[$x][1]; }  ?></center></td>
      <td style="background: #FAC881"><center><?php  if(is_numeric($b3F[$x][2]) && $b3F[$x][1] > 0) { echo abs($b3F[$x][2]); } else { $b3F[$x][1] = 0; echo $b3F[$x][2]; }  ?></center></td>
      <td style="background: #FFCCCB"><center><?php  if(is_numeric($b3F[$x][3]) && $b3F[$x][1] > 0) { echo abs($b3F[$x][3]); } else { $b3F[$x][3] = 0; echo $b3F[$x][3]; }  ?></center></td>
      <td  style="background: 	#686868; color: white;"><center><strong><?php echo $b3F[$x][1]+$b3F[$x][2]+$b3F[$x][3];?></strong></center></td>
      <td><center><?php echo round((($b3F[$x][1]+$b3F[$x][2]+$b3F[$x][3])/$b3Totals[3][1])*100, 2);?>%</center></td>
      <td><center><?php echo round((($b3F[$x][1]+$b3F[$x][2]+$b3F[$x][3])/$runningTotal)*100, 2);?>%</center></td>
      <?php $lstTotal = $lstTotal + round((($b3F[$x][1]+$b3F[$x][2]+$b3F[$x][3])/$runningTotal)*100, 2); ?>
      <td><center><?php  echo round(((abs($b3F[$x][3])/abs(($b3F[$x][1]+$b3F[$x][2]+$b3F[$x][3])))*100), 2); ?>%</center></td>
    </tr>
  <?php
    $idx++;
    $idx3++;
  }
  ?>
  <tr style="background: black; color: white">
    <th><?php echo $idx; $idx++; ?></th>
    <th><center>Bucket 3 Totals</th>
    <th><center>/</center></th>
    <th><center><?php echo $b3Totals[0][1]; ?></center></th>
    <th><center><?php echo $b3Totals[1][1]; ?></center></th>
    <th><center><?php echo abs($b3Totals[2][1]); ?></center></th>
    <th><center><?php echo abs($b3Totals[3][1]); ?></center></th>
    <th><center>100%</center></th>
    <th><center><?php echo $lstTotal; ?>%</center></th>
    <th><center><?php echo round((($b3Totals[2][1]/$b3Totals[3][1])*100), 2);?>%</center></th>
    <th></th>
  </tr>
</table>
<h3>Total of all buckets: <strong><?php echo $runningTotal; ?></strong> <span style="margin-left: 20;">Total Pumps Shipped: <strong><?php echo $shippedTotal; ?></strong></span></h3>
<br>
</center>

<center>
  <button onclick="toggleCalc()" id="toggler" style="background: black; color: white; width: 500; border: 0; padding: 10;"><strong>show calculations</strong></button>
  <div style="<?php if($hold) { } else { echo 'display: none;'; } ?>">
    <br>
    <div style="border: 1px solid red; background: rgba(255, 0, 0, 0.2); padding: 20; color: red; width: 730; ">
      <strong>Alert:</strong> Not enough data to display for <?php $adr = $dt1->modify("+2 month"); $ad = $adr->format("Y-m-01"); echo date("F Y", strtotime($ad)); ?>. Data will be available on <?php echo date("m/04/Y", strtotime($ad)); ?>. <a href="https://reports.hygeiahealth.com/threebucket.php?override=true"><button style="margin-left: 10; background: red; color: white; font-weight: 600; border: 0;">Override</button></a>
    </div>
  </div>
</center>
<br>
<div style="position: absolute; left: 28.5%; width: 1000px; display: none;" id="displayer">
<div style="width: 300px; float: left;">
<label title="Formula: F7+F15">Total Apps (OB Only):</label> <strong><?php echo $b1Totals[3][1]+$b2Totals[3][1]; ?></strong><br>
<label title="Formula: F7+F15+F22">Total Apps:</label> <strong><?php echo $b1Totals[3][1]+$b2Totals[3][1]+$b3Totals[3][1]; ?></strong><br>
<label title="Formula: D7+D15">OB Only, Shipped apps from this period:</label> <strong><?php echo $b1Totals[1][1]+$b2Totals[1][1]; ?></strong><br>
<label title="Formula: D7+D15+D22">This period app only, Total Shipments:</label> <strong><?php echo $b1Totals[1][1]+$b2Totals[1][1]+$b3Totals[1][1]; ?></strong><br>
<label title="Formula: Total Shipments - App Only Shipments">Pumps shipped from backlog:</label> <strong><?php if($shippedTotal-($b1Totals[1][1]+$b2Totals[1][1]+$b3Totals[1][1]) < 0) { echo "Error: App only > normal shipments."; } else { echo $shippedTotal-($b1Totals[1][1]+$b2Totals[1][1]+$b3Totals[1][1]); } ?></strong>
</div>
<div style="width: 300px; float: right;">
<label title="Formula: 1-(Void Rate + Gross Throughput Rate)">Total Backlog Rate:</label> <strong><?php echo 100-((round((($b1Totals[2][1]+$b2Totals[2][1]+$b3Totals[2][1])/($b1Totals[3][1]+$b2Totals[3][1]+$b3Totals[3][1])*100), 2))+round((($shippedTotal/($b1Totals[3][1]+$b2Totals[3][1]+$b3Totals[3][1]))*100), 2)); ?>%</strong><br>
<label title="Formula: Total Apps * Backlog Rate">Total Backlog Pumps:</label> <strong><?php echo round(((1-(round((($b1Totals[2][1]+$b2Totals[2][1]+$b3Totals[2][1])/($b1Totals[3][1]+$b2Totals[3][1]+$b3Totals[3][1])), 2)+round((($b1Totals[1][1]+$b2Totals[1][1]+$b3Totals[1][1])/($b1Totals[3][1]+$b2Totals[3][1]+$b3Totals[3][1])), 2)))*($b1Totals[3][1]+$b2Totals[3][1]+$b3Totals[3][1])), 0);?></strong><br>
<label title="Formula: Total Apps (OB Only) / OB Only Shipped ">OB Throughput Rate: </label><strong><?php echo round((($b1Totals[1][1]+$b2Totals[1][1])/($b1Totals[3][1]+$b2Totals[3][1])*100), 2); ?>%</strong><br>
<label title="Formula: (E7+E15)/Total Apps (OB Only)">OB Void Rate:</label> <strong><?php echo round((($b1Totals[2][1]+$b2Totals[2][1])/($b1Totals[3][1]+$b2Totals[3][1])*100), 2);?>%</strong><br>
<label title="Formula: (E22/F22)">Non OB Void Rate:</label> <strong><?php echo round((($b3Totals[2][1]/$b3Totals[3][1])*100), 2); ?>%</strong>
</div>
<div style="width: 300px; margin: 0 auto;">
  <label title="Formula: (E7+E15+E22)/Total Apps">Void Rate:</label> <strong><?php echo round((($b1Totals[2][1]+$b2Totals[2][1]+$b3Totals[2][1])/($b1Totals[3][1]+$b2Totals[3][1]+$b3Totals[3][1])*100), 2); ?>%</strong><br>
  <label title="Formula: App Only Total Shipments / Total Apps">Throughput Rate:</label> <strong><?php echo round((($b1Totals[1][1]+$b2Totals[1][1]+$b3Totals[1][1])/($b1Totals[3][1]+$b2Totals[3][1]+$b3Totals[3][1])*100), 2); ?>%</strong><br>
  <label title="Formula: Void Rate + Throughput Rate">Consumption Rate:</label> <strong><?php echo round((($b1Totals[2][1]+$b2Totals[2][1]+$b3Totals[2][1])/($b1Totals[3][1]+$b2Totals[3][1]+$b3Totals[3][1])*100), 2)+round((($b1Totals[1][1]+$b2Totals[1][1]+$b3Totals[1][1])/($b1Totals[3][1]+$b2Totals[3][1]+$b3Totals[3][1])*100), 2); ?>%</strong><br>
  <label title="Formula: 1 - Consumption Rate">Backlog Rate:</label> <strong><?php echo 100-(round((($b1Totals[2][1]+$b2Totals[2][1]+$b3Totals[2][1])/($b1Totals[3][1]+$b2Totals[3][1]+$b3Totals[3][1])*100), 2)+round((($b1Totals[1][1]+$b2Totals[1][1]+$b3Totals[1][1])/($b1Totals[3][1]+$b2Totals[3][1]+$b3Totals[3][1])*100), 2)); ?>%</strong><br>
  <label title="Formula: Total Shipments / Total Apps">Gross Throughput Rate:</label> <strong><?php echo round((($shippedTotal/($b1Totals[3][1]+$b2Totals[3][1]+$b3Totals[3][1]))*100), 2) ?>%</strong>
</div>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>

</div>

</body>
