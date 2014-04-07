<h1>Mailing erstellen/bearbeiten</h1>
<div class="breadcrumb clearfix">
	<ul class="clearfix">
		<li><a href="/app/mailinglist">Mailings</a></li>
		<li>Erstellen / Bearbeiten</li>
	</ul>
</div>
<div>
	<div class="form-row">
		<label>Interne Bezeichnung des Mailings:</label>
		<input  class="form-control" type="text" id="mailingname" placeholder="Bezeichnung" />
	</div>
	<hr />
	<h2>Mailing gestalten</h2>
	<div class="mail-template">
		<div class="form-row highlight">
			<label>Betreff:</label>
			<input type="text" id="subject" placeholder="Betreff der Mail" />
		</div>
		<hr />
		<div id="mailparts_wrapper">
			
		</div>
		<div>
			<h3>Element hinzufügen</h3>
			<button class="btn btn-primary" onclick="add_text_only()"><i class="fa fa-font"></i> Textblock</button>
			<button class="btn btn-primary" onclick="add_image_only()"><i class="fa fa-picture-o"></i> Bild</button>
			<!-- <button class="btn btn-default" onclick="add_text_with_image()"><i class="fa fa-clipboard"></i> Textblock mit Bild</button> -->
		</div>
	</div>
	<h2>Versandoptionen</h2>
	<div class="form-row ">
		<label>Versand am:</label>
		<input type="text" id="mailingdate" placeholder="2014-03-18" />
	</div>
	<div class="form-row">
		<label>Link zur CSV-Datei:</label>
		<input type="text" id="dataurl" placeholder="ftp://... http://..." />
	</div>
	<hr />
	<div>
		<button class="btn" onmouseup="remove_mailing(function() { window.location.href='/app/mailinglist'; })">Löschen</button>
		<button class="btn" onmouseup="preview()">Vorschau</button>
		<button class="btn btn-primary" onmouseup="save(function() { window.location.href='/app/mailinglist'; })">Speichern</button>
	</div>
</div>


<!-- templates for mail parts -->
<div style="display: none">
	<div id="tpl_text_only____CTR___">
		<div class="template" id="text_only____CTR___">
			<div class="delete">
				<img src="/assets/img/btn-delete.png" onclick="remove_elem(___CTR___, function() { $('#text_only____CTR___').remove(); })">
			</div>
			<div>
				<textarea id="text____CTR___" rows="10" cols="80">
					<h1>Ihre Überschrift kommt hier hin.</h1>
					<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.</p>
				</textarea>
			</div>
			<div class="link form-row">
				<label>Link hinzufügen:</label>
				<input type="text" id="link____CTR___" placeholder="http://..." />
			</div>
		</div>
	</div>
	<div id="tpl_text_with_image____CTR___">
		<div id="text_with_image____CTR___" class="template">
			<div class="delete">
				<img src="/assets/img/btn-delete.png" onclick="remove_elem(___CTR___, function() { $('#text_with_image____CTR___').remove(); })">
			</div>
			<div>
				<img src="/assets/img/upload.jpg" id="image____CTR___" class="imgupload____CTR___" alt="Bild upload">
			</div>
			<div>
				<textarea id="text____CTR___" rows="10" cols="80"></textarea>
			</div>
			<div>
				<label>Link hinzufügen:</label>
				<input type="text" id="link____CTR___" placeholder="http://..." />
			</div>
		</div>
	</div>
	<div id="tpl_image_only____CTR___">
		<div id="image_only____CTR___" class="template">
			<div class="delete">
				<img src="/assets/img/btn-delete.png" onclick="remove_elem(___CTR___, function() { $('#image_only____CTR___').remove(); })">
				<!-- <button >X</button> -->
			</div>
			<div class="image">
				<img src="/assets/img/upload.jpg" id="image____CTR___" class="imgupload____CTR___" alt="Bild upload">
			</div>
			<div class="link form-row">
				<label>Bild verlinken:</label>
				<input type="text" id="link____CTR___" placeholder="http://..." />
			</div>
		</div>
	</div>
</div>
<!-- end of templates -->


<script type="text/javascript">

var addlock = false;
var counter = 0;
var typearr = [];
var textstore = [];
var imagestore = [];
var linkstore = [];
var scrollpos = [];
var mailingid = <?= $this->get_value("mailingid") ?>;

function init_elements(ctr_parm) {
	var ctr = ctr_parm;
	if (ctr == null) ctr = counter;
	while ($("#mailparts_wrapper").html() != $("#mailparts_wrapper").html().replace("___CTR___", ctr)) {
		$("#mailparts_wrapper").html($("#mailparts_wrapper").html().replace("___CTR___", ctr));
	}
	for (var i = 0; i <= ctr; i++) {
		if ((typearr[ctr] != null) && (document.getElementById("image_" + i) != null)) {
			
			$(".imgupload_" + i).dropzone({
				createImageThumbnails: false,
				clickable: true,
				url: "/app/mailingedit/" + mailingid + "/img/" + typearr[ctr],
				paramName: "img_" + i, 
				maxFilesize: 20, // MB
				accept: function(file, done) {
					if (file.name.substring(file.name.length - 4, file.name.length).toLowerCase() == ".jpg") {
						done();
						fname = file.name;
						ftype = "image";
					} else {
						alert("Es sind nur Bilder erlaubt!\nDateiendung: .jpg");
					}
				},
				complete: function() {
					load_mailparts();
				}
			});
		}
	}
	if (ctr_parm == null) counter++;
	addlock = false;
}

function add_text_only() {
	if (addlock) {
		return;
	} else {
		addlock = true;
	}
	typearr[counter] = 'text_only';
	local_save();
	$("#mailparts_wrapper").html($("#mailparts_wrapper").html() + $("#tpl_text_only____CTR___").html());
	init_elements();
	local_restore();
}

function add_text_with_image() {
	if (addlock) {
		return;
	} else {
		addlock = true;
	}
	typearr[counter] = 'text_with_image';
	local_save();
	$("#mailparts_wrapper").html($("#mailparts_wrapper").html() + $("#tpl_text_with_image____CTR___").html());
	init_elements();
	local_restore();
}

function add_image_only() {
	if (addlock) {
		return;
	} else {
		addlock = true;
	}
	typearr[counter] = 'image_only';
	local_save();
	$("#mailparts_wrapper").html($("#mailparts_wrapper").html() + $("#tpl_image_only____CTR___").html());
	init_elements();
	local_restore();
}

function load_mailparts() {
	var ctr = 0;
	for (var i = 0; i < counter; i++) {
		if (document.getElementById("text_" + i) != null) {
			ctr++;
			var url = "/app/mailingedit/" + mailingid + "/txt/" + typearr[i];
			var xhr = new XMLHttpRequest();
			var data = "&ordinal=" + i;
			xhr.open('PUT', url, true);
			xhr.onload = function(response) {
				ctr--;
				console.log(response.currentTarget.response);
				var res = JSON.parse(response.currentTarget.response)
				if (res != null) {
					if ((res["mailpart"] != null) && (res["text"] != null)) {
						$("#text_" + res["mailpart"]["ordinal"]).html(res["text"]["text"]);
					}
					if ((res["mailpart"] != null) && (res["mailpart"]["link"] != null)) {
						$("#link_" + res["mailpart"]["ordinal"]).val(res["mailpart"]["link"]);
					}
				}
				if (ctr == 0) {
					setup_ckeditor();
				}
			};
			xhr.send(data);
		}
		if (document.getElementById("image_" + i) != null) {
			ctr++;
			var url = "/app/mailingedit/" + mailingid + "/img/" + typearr[i];
			var xhr = new XMLHttpRequest();
			var data = "&ordinal=" + i;
			xhr.open('PUT', url, true);
			xhr.onload = function(response) {
				ctr--;
				console.log(response.currentTarget.response);
				var res = JSON.parse(response.currentTarget.response)
				if (res != null) {
					if ((res["mailpart"] != null) && (res["image"] != null)) {
						$("#image_" + res["mailpart"]["ordinal"]).attr("src", "/files/images/" + res["image"]["name"] + ".jpg");
					}
					if ((res["mailpart"] != null) && (res["mailpart"]["link"] != null)) {
						$("#link_" + res["mailpart"]["ordinal"]).val(res["mailpart"]["link"]);
					}
				}
				if (ctr == 0) {
					setup_ckeditor();
				}
			};
			xhr.send(data);
		}
	}
}

function load_mailing() {
	var url = "/app/mailingedit/" + mailingid + "/mailing";
	var xhr = new XMLHttpRequest();
	xhr.open('PUT', url, true);
	xhr.onload = function(response) {
		console.log(response.currentTarget.response);
		var res = JSON.parse(response.currentTarget.response);
		$("#mailingname").val(res["mailing"]["name"]);
		$("#mailingdate").val(res["mailing"]["send_date"]);
		$("#dataurl").val(res["mailing"]["data_url"]);
		$("#subject").val(res["mailing"]["subject"]);
		counter = 0;
		var highestOrdinal = 0;
		for (var i = 0; i < res["mailparts"].length; i++) {
			$("#mailparts_wrapper").html($("#mailparts_wrapper").html() + $("#tpl_" + res["mailparttypes"][i]["name"] + "____CTR___").html());
			typearr[res["mailparts"][i]["ordinal"]] = res["mailparttypes"][i]["name"];
			init_elements(res["mailparts"][i]["ordinal"]);
			highestOrdinal = (parseInt(res["mailparts"][i]["ordinal"]) > parseInt(highestOrdinal)) ? 
					parseInt(res["mailparts"][i]["ordinal"]) : parseInt(highestOrdinal);
		}
		counter = parseInt(highestOrdinal) + 1;
		load_mailparts();
	};
	xhr.send();
}

function setup_ckeditor() {
	for (var i = 0; i < counter; i++) {
		if (document.getElementById("text_" + i) != null) {
			/*
			CKEDITOR.replace("text_" + i, {
				toolbar: [
					{ name: 'document', groups: [ 'mode', 'document', 'doctools' ], items: [ 'Source', '-', 'Save', 'NewPage', 'Preview', 'Print', '-', 'Templates' ] },
					{ name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
					{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ], items: [ 'Find', 'Replace', '-', 'SelectAll', '-', 'Scayt' ] },
					{ name: 'forms', items: [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ] },
					'/',
					{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
					{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language' ] },
					{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
					{ name: 'insert', items: [ 'Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe' ] },
					'/',
					{ name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
					{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
					{ name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] },
					{ name: 'others', items: [ '-' ] },
					{ name: 'about', items: [ 'About' ] }
				]
			});
			*/
			CKEDITOR.replace("text_" + i, {
				toolbar: [
					{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
					{ name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
					{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
					{ name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] },
					{ name: 'others', items: [ '-' ] },
					{ name: 'about', items: [ 'About' ] }
				]
			});
		}
	}
}

function remove_ckeditor() {
	for(name in CKEDITOR.instances) {
		CKEDITOR.instances[name].destroy();
	}
}

function local_restore() {
	for (var i = 0; i < counter; i++) {
		if (document.getElementById("text_" + i) != null) {
			$("#text_" + i).html(textstore[i]);
		}
		if (document.getElementById("image_" + i) != null) {
			$("#image_" + i).attr("src", imagestore[i]);
		}
		if (document.getElementById("link_" + i) != null) {
			$("#link_" + i).val(linkstore[i]);
		}
	}
	setup_ckeditor();
	window.setTimeout(function() { 
		setScrollXY(scrollpos[0], scrollpos[1] + 300);
	}, 500);
	window.setTimeout(function() { 
		setScrollXY(scrollpos[0], scrollpos[1] + 300);
	}, 1000);
	window.setTimeout(function() { 
		setScrollXY(scrollpos[0], scrollpos[1] + 300);
	}, 1500);
}

function local_save() {
	scrollpos = getScrollXY();
	remove_ckeditor();
	for (var i = 0; i < counter; i++) {
		if (document.getElementById("text_" + i) != null) {
			textstore[i] = $("#text_" + i).val();
		}
		if (document.getElementById("image_" + i) != null) {
			imagestore[i] = $("#image_" + i).attr("src");
		}
		if (document.getElementById("link_" + i) != null) {
			linkstore[i] = $("#link_" + i).val();
		}
	}
}

function save(cb) {
	var lctr = 0;
	var didRequest = false;
	var url1 = "/app/mailingedit/" + mailingid;
	var xhr1 = new XMLHttpRequest();
	var data1 = new FormData();
	data1.append('mailingname', escape($("#mailingname").val()));
	data1.append('mailingdate', escape($("#mailingdate").val()));
	data1.append('dataurl', escape($("#dataurl").val()));
	data1.append('subject', escape($("#subject").val()));
	xhr1.open('POST', url1, true);
	remove_ckeditor();
	xhr1.onload = function(response) {
		console.log(response.currentTarget.response);
		for (var i = 0; i <= counter; i++) {
			if (document.getElementById("text_" + i) != null) {
				textstore[i] = $("#text_" + i).val();
				lctr++;
				didRequest = true;
				var url = "/app/mailingedit/" + mailingid + "/txt/" + typearr[i];
				var xhr = new XMLHttpRequest();
				var data = new FormData();
				data.append('text', $("#text_" + i).val());
				data.append('type', typearr[i]);
				data.append('ordinal', i);
				xhr.open('POST', url, true);
				xhr.onload = function(response) {
					lctr--;
					console.log(response.currentTarget.response);
					if (lctr == 0) {
						if (cb) {
							reorder_ordinals(cb);
						}
					}
				};
				xhr.send(data);
			}
			if (document.getElementById("link_" + i) != null) {
				linkstore[i] = $("#link_" + i).val();
				lctr++;
				didRequest = true;
				var url = "/app/mailingedit/" + mailingid + "/lnk/" + typearr[i];
				var xhr = new XMLHttpRequest();
				var data = new FormData();
				data.append('link', $("#link_" + i).val());
				data.append('type', typearr[i]);
				data.append('ordinal', i);
				xhr.open('POST', url, true);
				xhr.onload = function(response) {
					lctr--;
					console.log(response.currentTarget.response);
					if (lctr == 0) {
						if (cb) {
							reorder_ordinals(cb);
						}
					}
				};
				xhr.send(data);
			}
			if (document.getElementById("image_" + i) != null) {
				imagestore[i] = $("#image_" + i).attr("src");
			}
		}
		if (!didRequest) {
			if (cb) {
				reorder_ordinals(cb);
			}
		}
	};
	xhr1.send(data1);
}

function remove_elem(ordinal, cb) {
	var url = "/app/mailingedit/" + mailingid + "/" + ordinal + "/" + typearr[ordinal];
	var xhr = new XMLHttpRequest();
	xhr.open('DELETE', url, true);
	scrollpos = getScrollXY();
	remove_ckeditor();
	xhr.onload = function(response) {
		console.log(response.currentTarget.response);
		setup_ckeditor();
		window.setTimeout(function() { 
			setScrollXY(scrollpos[0], scrollpos[1]);
		}, 500);
		window.setTimeout(function() { 
			setScrollXY(scrollpos[0], scrollpos[1]);
		}, 1000);
		window.setTimeout(function() { 
			setScrollXY(scrollpos[0], scrollpos[1]);
		}, 1500);
		if (cb) {
			cb(ordinal);
		}
	};
	xhr.send();
}

function remove_mailing(cb) {
	var msg = "Soll das ganze Mailing gelöscht werden?";
	if (confirm(msg)) {
		var url = "/app/mailingedit/" + mailingid;
		var xhr = new XMLHttpRequest();
		xhr.open('DELETE', url, true);
		xhr.onload = function(response) {
			console.log(response.currentTarget.response);
			if (cb) {
				cb();
			}
		};
		xhr.send();
	}
}

function reorder_ordinals(cb) {
	var url = "/app/mailingedit/" + mailingid + "/ordinals";
	var xhr = new XMLHttpRequest();
	xhr.open('PUT', url, true);
	xhr.onload = function(response) {
		console.log(response.currentTarget.response);
		if (cb) {
			cb();
		}
	};
	xhr.send();
}

function preview() {
	remove_ckeditor();
	save(function() {
		setup_ckeditor();
		window.open("/app/mail/byid/" + mailingid);
	});
}

function getScrollXY() {
	var scrX = 0, scrY = 0;
 
	if( typeof( window.pageYOffset ) == 'number' ) {
		//Netscape compliant
		scrY = window.pageYOffset;
		scrX = window.pageXOffset;
	} else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
		//DOM compliant
		scrY = document.body.scrollTop;
		scrX = document.body.scrollLeft;
	} else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
		//IE6 standards compliant mode
		scrY = document.documentElement.scrollTop;
		scrX = document.documentElement.scrollLeft;
	}
	console.log("getScrollXY() : [ " + scrX + ", " + scrY +" ]");
	return [ scrX, scrY ];
}

function setScrollXY(scrX, scrY) {
	console.log("setScrollXY(" + scrX + ", " + scrY +")");
	if( typeof( window.pageYOffset ) == 'number' ) {
		//Netscape compliant
		window.pageYOffset = scrX;
		window.pageXOffset = scrY;
	}
	if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
		//DOM compliant
		document.body.scrollTop = scrY;
		document.body.scrollLeft = scrX;
	}
	if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
		//IE6 standards compliant mode
		document.documentElement.scrollTop = scrY;
		document.documentElement.scrollLeft = scrX;
	}
	return [ scrX, scrY ];
}


CKEDITOR.disableAutoInline = true;
load_mailing();

</script>