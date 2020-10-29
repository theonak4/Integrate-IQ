function option1Gate() {
  var input = document.getElementById("viewBy").value;
  if(input == 2) {
    document.getElementById("lead_sources").style.display = "none";
    document.getElementById("sales_reps").style.removeProperty("display");
  } else if(input == 3) {
    document.getElementById("sales_reps").style.display = "none";
    document.getElementById("lead_sources").style.removeProperty("display");
  } else if(input == 4) {
    document.getElementById("lead_sources").style.removeProperty("display");
    document.getElementById("sales_reps").style.removeProperty("display");
  } else if(input == 1) {
    document.getElementById("lead_sources").style.display = "none";
    document.getElementById("sales_reps").style.display = "none";
  }
}
