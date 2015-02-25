/**
 * ajax.js
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     libcompactmvc
 * @copyright   Copyright (c) Botho Hohbaum
 * @link		https://github.com/bhohbaum
 * @classDescription example:
 * 		<input type='hidden' id='tags' class="ajax form-control full-width"
 * 			data-placeholder="Titel eintragen"
 * 			data-path="ajax/survey/bonus/<?= $this->get_value("surveyId") ?>#name"
 */

var $_ajax = [];
var $ajax = function() {

	$ajax._cfg = [];

	this._cfg = [];
	this._responseType = "";
	this._cbok = null;
	this._cberr = null;

	/**
	 * functions for public use
	 */
	this.get = function(url) {
		return this._doRequest('GET', url);
	};
	this.post = function(url) {
		return this._doRequest('POST', url);
	};
	this.put = function(url) {
		return this._doRequest('PUT', url);
	};
	this.del = function(url) {
		return this._doRequest('DELETE', url);
	};
	this.reload = function() {
		$(".ajax").each(function() {
			var $this = $(this);
			var url = "/" + $this.attr("data-path") + "/" +
							$this.attr("data-param0") + "/" +
							$this.attr("data-param1") + "/" +
							$this.attr("data-param2") + "/" +
							$this.attr("data-param3") + "/" +
							$this.attr("data-param4");
			while (url != url.replace("/undefined", "")) {
				url = url.replace("/undefined", "");
			}
			var content = $this.attr("data-content");
			new $ajax().ok(function(result) {
				var cmd = (content.substring(0, 6) == "$this.") ? content : "$this." + content;
				try {
					eval(cmd);
				} catch (e) {
					console.log(e);
				}
			}).get(url);
		});
	};
	this.data = function(str) {
		this._data = str;
		return this;
	};
	this.responseType = function(type) {
		this._responseType = type;
		return this;
	};
	this.ok = function(cb) {
		this._cbok = cb;
		return this;
	};
	this.err = function(cb) {
		this._cberr = cb;
		return this;
	};

	/**
	 * internal functions
	 */
	this._response = function(response) {
		var res = null;
		if (response.currentTarget.hasOwnProperty('response')) {
			res = response.currentTarget.response;
		} else if (response.currentTarget.hasOwnProperty('responseText')) {
			res = response.currentTarget.responseText;
		} else { // FF hack
			res = response.currentTarget.response;
		}
		return res;
	};
	this._callHandler = function(url, response, rData) {
		if (response.target.status == 200) {
			console.log(response);
			if ($_ajax[url]._cfg[url].cbok) {
				$_ajax[url]._cfg[url].cbok(rData);
			};
		} else {
			console.log(response);
			if ($_ajax[url]._cfg[url].cberr) {
				$_ajax[url]._cfg[url].cberr(rData);
			};
		}
	};
	this._doRequest = function(method, url) {
		var xhr = new XMLHttpRequest();
		xhr.open(method, url, true);
		xhr.responseType = this._responseType;
		xhr.onload = function(response) {
			new $ajax()._callHandler(url, response, new $ajax()._response(response));
		};
		this._cfg[url] = [];
		this._cfg[url].cbok = this._cbok;
		this._cfg[url].cberr = this._cberr;
		$_ajax[url] = this;
		xhr.send(this._data);
		return this;
	};


};

$(document).ready(function() {
	$(".ajax").each(function() {
		var $this = $(this);
		var url = "/" + $this.attr("data-path") + "/" +
						$this.attr("data-param0") + "/" +
						$this.attr("data-param1") + "/" +
						$this.attr("data-param2") + "/" +
						$this.attr("data-param3") + "/" +
						$this.attr("data-param4");
		while (url != url.replace("/undefined", "")) {
			url = url.replace("/undefined", "");
		}
		var content = $this.attr("data-content");
		events = ["keydown", "keypress", "keyup",
		          "click", "dblclick",
		          "mousedown", "mouseup", "mouseover", "mousemove", "mouseout",
		          "dragstart", "drag", "dragenter", "dragleave", "dragover", "drop", "dragend",
		          "load", "unload", "abort", "error", "resize", "scroll",
		          "select", "change", "submit", "reset", "focus", "blur",
		          "focusin", "focusout"];
		for (var i = 0; i < events.length; i++) {
			if ($this.attr("data-" + events[i]) != null) {
				$this.on(events[i], function(event) {
					var addThis = ($this.attr("data-" + event.type).substring(0, 6) == "$this.") ? "" : "$this.";
					var cmd = 'var data = ' + addThis + $this.attr("data-" + event.type);
					try {
						eval(cmd);
						var rnd = Math.round(Math.random() * 1000000);
						new $ajax().data("&data=" + escape(data)).ok(function(result) {
							var cmd = (content.substring(0, 6) == "$this.") ? content : "$this." + content;
							try {
								eval(cmd);
							} catch (e) {
								console.log(e);
							}
						}).post(url + "#toserver" + rnd);
					} catch (e) {
						console.log(e);
					}
				});
			}
		}
		var rnd = Math.round(Math.random() * 1000000);
		new $ajax().ok(function(result) {
			var cmd = (content.substring(0, 6) == "$this.") ? content : "$this." + content;
			try {
				eval(cmd);
			} catch (e) {
				console.log(e);
			}
		}).get(url + "#fromserver" + rnd);
	});
});

