
function onDocumentLoaded() {
	updateSignature();
}

function waitDocumentLoaded() {
	if (document.readyState != "complete") {
		window.setTimeout(waitDocumentLoaded, 100);
	}
	window.setTimeout(onDocumentLoaded, 100);
}

function get_surveys(fk_id_classroom) {
	var data = new Array();
	data['fk_id_classroom'] = fk_id_classroom;
	data['target'] = "surveys";
	send_request("get_surveys", data);
}

function add_survey(fk_id_classroom) {
	var data = new Array();
	data['fk_id_classroom'] = fk_id_classroom;
	data['target'] = "add_survey";
	send_request("get_survey_form", data);
}

function deleteSignature() {
	var id;
	if (document.getElementById('signselect') == null) {
		id = "";
	} else {
		id = document.getElementById('signselect').value;
	}
	if (confirm('Wollen sie die aktuell ausgewählte Signatur wirklich löschen?')) {
		sendRequest("deleteSignature", id);
	}
}

function updateSignature() {
	sendRequest("updateSignature", document.getElementById('signselect').value);
}

function setSignature() {
	CKEDITOR.instances.signature.setData(decObject.signature);
	document.getElementById("signselectwrap").innerHTML = decObject.signselectwrap;
}

