function nps_admin_select_desgin(htmlsel) {
	var selval = htmlsel.options[htmlsel.selectedIndex].value;
	var myelem = document.getElementById('nps_admin_selecteddesign');
	var designpreviewpath = document.getElementById('nps_admin_designpath');
	var imgsrc = designpreviewpath.value + "styles/previews/" + selval + "_preview.png";
	myelem.innerHTML = '<img src="' + imgsrc + '"/>';
}