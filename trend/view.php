<!--

  Order Trend Report Viewer

  Purpose: Display configuration controls on static webpage and refresh graph inside iFrame based on user input.

-->

<?php
date_default_timezone_set('America/Los_Angeles');
?>
<html>
<head>
  <title>Order Trend</title>
  <script type="text/javascript" src="https://unpkg.com/vis-timeline@7.3.10/standalone/umd/vis-timeline-graph2d.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
  <link rel="stylesheet" href="index.css">
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
  <script type="text/javascript" src="index.js"></script>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
  <style>
  .rangepicker {
    width: 300;
    border: 1px solid #d3d3d3;
    text-align: center;
    padding: 10;
  }
  #rangepicker2 {
    border-left: 3px solid #F49831;
  }
  ::-webkit-scrollbar {
    width: 10px;
  }
  ::-webkit-scrollbar-track {
    background: #f1f1f1;
  }
  ::-webkit-scrollbar-thumb {
    background: #F49831;
  }
  ::-webkit-scrollbar-thumb:hover {
    background: #F49831;
  }

  .footer {
    background: #fafafa;
    padding: 5;
    position: fixed;
    top: 0;
    font-size: 15;
    left: 0;
    width: 100%;
    text-align: center;
  }
  </style>
  <script>
$(function() {
  $('input[name="daterange"]').daterangepicker({
    opens: 'right'
  }, function(start, end, label) {
    document.getElementById("rangepicker").value = start.format('YYYY-MM-DD') + "-" + end.format('YYYY-MM-DD');
  });

  $('input[name="daterange2"]').daterangepicker({
    opens: 'right'
  }, function(start, end, label) {
    document.getElementById("rangepicker2").value = start.format('YYYY-MM-DD') + "-" + end.format('YYYY-MM-DD');
  });
});

</script>
<script>
function formSubmit(timeout) {
  document.getElementById("iframe").style.display = "none";
  setTimeout(function() {
  document.getElementById("iframe").style.removeProperty("display");
}, timeout);
}
function resetFrame() {
  location.reload();
}

function showCompare() {
  if(document.getElementById("compareTo").checked) {
    document.getElementById("rangepicker2").style.removeProperty("display");
    document.getElementById("compareToType").style.removeProperty("display");
  } else {
    document.getElementById("rangepicker2").style.display = "none";
    document.getElementById("compareToType").style.display = "none";
  }
}
</script>
</head>
<body>
  <br>
  <h3>Order Trend</h3>
  <div style="position: absolute; width: 90%; height: 550px; border-bottom: 5px solid #F49831;">
  <div class="outer" id="load">
    <div class="middle">
      <div class="inner">
        <div id="loader">
          <center>
            <div class="spinner-border text-warning" role="status" style="margin-bottom: 10;"></div><br>
            <strong>Building trend...</strong>
          </center>
        </div>
      </div>
    </div>
  </div>
  <iframe name="iframe" id="iframe" src="http://52.89.106.99/z_hygdev/trend/" style="position: relative; z-index: 2; border: 0; width: 100%; height: 100%; box-shadow: 0px 0px;"></iframe>
</div>
<br>
<div style="position: absolute; top: 655;">
<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample" style="margin-right: 5;">
    configure
  </button>
  <button class="btn btn-danger" type="button" onclick="resetFrame()" style="margin-right: 10;">
      reset
    </button>
  <strong>Tip:</strong> Use scroll wheel to pan horizontally and control key to zoom in.
  <br>
<div class="collapse" id="collapseExample">
  <div class="card card-body">

    <form style="margin: 0; padding: 0;" method="POST" target="iframe" action="index.php">
      <!-- VIEW SELECTOR -->
        <div class="row">
          <legend class="col-form-label col-sm-2 pt-0"><strong>View by</strong></legend>
          <div class="col-sm-10">
            <select id="viewBy" class="form-control" onchange="option1Gate()" name="viewBy">
              <option selected disabled>Choose...</option>
              <option value="1">All SR / All LS <strong>(Default)</strong></option>
              <option value="2">Sales Rep</option>
              <option value="3">Lead Source</option>
              <option value="4">Sales Rep & Lead Source</option>
            </select>
          </div>
        </div>

        <!-- SORT OPTION: Sales Rep -->
        <div class="row" style="margin-top: 10; display: none;" id="sales_reps">
          <legend class="col-form-label col-sm-2 pt-0">Sales Rep</legend>
          <div class="col-sm-10">
            <select id="salesRepSelect " class="form-control" name="salesRep">
              <option selected disabled>Choose...</option>
              <option disabled>California</option>

              <option value="125">CA - Ann Freeman</option>
              <option value="110">CA - April Williams</option>
              <option value="111">CA - Diana Vial</option>
              <option value="104">CA - Marcy Ziesing</option>

              <option disabled>Texas</option>

              <option value="131">TX - Aly Gilmartin</option>
              <option value="133">TX - Angela Thayer</option>
              <option value="132">TX - Brittany Burris</option>
              <option value="129">TX - Jessica Lewin</option>
              <option value="117">TX - Katherine Nevins</option>
              <option value="113">TX - Melanie Benfield</option>
              <option value="120">TX - Nikki Andazola</option>

            </select>
          </div>
        </div>

        <!-- SORT OPTION: Lead Source -->
        <div class="row" style="margin-top: 10; display: none;" id="lead_sources">
          <legend class="col-form-label col-sm-2 pt-0">Lead Source</legend>
          <div class="col-sm-10">
            <select id="leadSourceSelect" class="form-control" name="leadSource">
              <option selected disabled>Choose...</option>

              <option value="Moms Get More">Moms Get More</option>
              <option value="OB Office - EMR">OB Office - EMR</option>
              <option value="OB Office - Text">OB Office - Text</option>
              <option value="OB Office- Brochure">OB Office - Brochure</option>
              <option value="OB Office- Tear Sheet">OB Office - Tear Sheet</option>
              <option value="OB Portal">OB Portal</option>
              <option value="Facebook">Facebook</option>
              <option value="Google">Google</option>
              <option value="HH">HH</option>
              <option value="Insurance Gave Phone #">Insurance Gave Phone #</option>
              <option value="WWW">WWW</option>
              <option value="Zeeto">Zeeto</option>
            </select>
          </div>
        </div>

        <br>

        <!-- DATE RANGE OPTION -->
      <div class="form-group row">
        <div class="col-sm-2"><strong>Date range</strong></div>
        <div class="col-sm-10">
          <!-- DEFAULT RANGE PICKER -->
          <input type="text" name="daterange" value="01/01/2020 - 12/01/2020" id="rangepicker" class="rangepicker"></input>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="compareTo" onclick="showCompare()" name="compareTo" value="true"> Compare to </input>
          </div>
          <!-- COMPARE TO RANGE PICKER -->
          <input type="text" name="daterange2" value="01/01/2020 - 12/01/2020" id="rangepicker2" style="display: none;"class="rangepicker"> </input>
          <!-- COMPARE TO CHOOSE TYPE -->
          <select id="compareToType" class="form-control" name="compareToType" style="margin-top: 5; display: none;">
            <option selected disabled>Choose...</option>
            <option value="all" disabled>All Orders</option>
            <option value="new">New Orders</option>
            <option value="shipped">Shipped Orders</option>
            <option value="voided">Voided Orders</option>

          </select>
        </div>
      </div>

      <!-- DISPLAY LABELS OPTION -->
      <div class="form-group row">
        <div class="col-sm-2"><strong>Display labels</strong></div>
        <div class="col-sm-10">
          <div class="form-check">
            <input class="form-check-input" name="displayLabels" value="true" type="checkbox" id="labels" checked>
          </div>
        </div>
      </div>

      <br>

      <!-- ACTION CONTROLS -->
      <div class="form-group row" style="margin-bottom: 0; padding-bottom: 0;">
        <div class="col-sm-10">
          <button type="submit" class="btn btn-light" onclick="formSubmit(3500)">update trend <img src="http://52.89.106.99/sqlvis/assets/icons_new/arrow-clockwise.svg" style="margin-bottom: 1; margin-left: 5;"></img></button>
        </div>
      </div>
    </form>

  </div>
</div>
</div>
<img src="http://52.89.106.99/z_hygdev/logo.png" style="position: fixed; right: 20; bottom: 20; width: 80;"></img>
<br><br><br><br><br>
<div class="footer">
  <strong style="margin-right: 20;">HYG Test Instance</strong><small> Test Name: <strong style="margin-right: 20;">Order Trend Report</strong> Last Updated: <strong><?php echo date ("F d Y H:i:s.", getlastmod()); ?></strong></small>
</div>
</body>
</html>
