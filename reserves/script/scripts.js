function openpopup(popurl){
	winpops=window.open(popurl,"","width=450,height=550,resizable,scrollbars")
}

function openpopup_vaixell(popurl){
	winpops=window.open(popurl,"","width=800,height=650,resizable")
}

function openpopup_taller(popurl){
	winpops=window.open(popurl,"","width=510,height=700,resizable")
}

function finestra(popurl){
	winpops=window.open(popurl,"","width=450,height=550,resizable,scrollbars")
}

function finestraTotal (popurl){
	winpops=window.open(popurl,"","width=800,height=600,toolbar,location,directories,status,scrollbars,menubar,resizable")
}

function finestraTotal_vaixell (popurl){
	winpops=window.open(popurl,"","width=800,height=650,toolbar,location,directories,status,scrollbars,menubar,resizable")
}
function get_cookie(Name) {
  var search = Name + "="
  var returnvalue = "";
  if (document.cookie.length > 0) {
    offset = document.cookie.indexOf(search)
    if (offset != -1) { // if cookie exists
      offset += search.length
      // set index of beginning of value
      end = document.cookie.indexOf(";", offset);
      // set index of end of cookie value
      if (end == -1)
         end = document.cookie.length;
      returnvalue=unescape(document.cookie.substring(offset, end))
      }
   }
  return returnvalue;
}

function loadornot(){
if (get_cookie('poppedup')==''){
finestraTotal('principal.php')
document.cookie="poppedup=yes"
}
}