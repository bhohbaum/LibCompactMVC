/**
 * ajax.js
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 * @classDescription example:
 * 		<input id="description_<?= $epod->id ?>" type="text"
 * 			class="ajax"
 * 			data-path="<?= $this->lnk("ajaxep", "epod_lang_group", "/" . $epod->id . "/description") ?>"
 * 			data-content="$this.val(JSON.parse(result).description)"
 * 			data-change="$this.val()" />
 * 		<input id="is_global_<?= $epod->id ?>" type="checkbox"
 * 			class="ajax"
 * 			data-path="<?= $this->lnk("ajaxep", "epod_lang_group", "/" . $epod->id . "/is_global") ?>"
 * 			data-content="$this.prop('checked', JSON.parse(result).is_global == 1)"
 * 			data-change="($this.prop('checked') == true) ? 1 : 0" />
 */

var $_ajax = [];
var $ajax = function() {

	$ajax._cfg = [];

	this._cfg = [];
	this._responseType = "";
	this._cbok = null;
	this._cberr = null;

	/**
	 * public functions
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
			var praefix = ($this.attr("data-path").substring(0, 4) == "http") ? "" : "/";
			var url = praefix + $this.attr("data-path") + "/" +
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
	this.init = function() {
		$(".ajax").each(function() {
			var $this = $(this);
			var praefix = ($this.attr("data-path").substring(0, 4) == "http") ? "" : "/";
			var url = praefix + $this.attr("data-path") + "/" +
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
						var cmd = 'var data = ' + $this.attr("data-" + event.type);
						try {
							eval(cmd);
							new $ajax().data("&data=" + encodeURIComponent(data)).ok(function(result) {
								var cmd = (content.substring(0, 6) == "$this.") ? content : "$this." + content;
								try {
									eval(cmd);
								} catch (e) {
									console.log(e);
								}
							}).post(url);
						} catch (e) {
							console.log(e);
						}
					});
				}
			}
			new $ajax().ok(function(result) {
				var cmd = content;
				try {
					eval(cmd);
				} catch (e) {
					console.log(e);
				}
			}).get(url);
			$this.removeClass("ajax");
		});
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
		var rnd = Math.round(Math.random() * 1000000);
		url = url + "#doRequest" + rnd;
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

var $ws = function() {

	this.url = null;
	this.socket = null;
	this.handlers = [];
	this.numhandlers = 0;
	this.id = null;

	/**
	 * public functions
	 */
	this.init = function(url) {
		this.url = url;
		this.id = url.substring(url.length - 32, url.length);
		ws = this;
		try {
			this.socket = new WebSocket(this.url, "event-dispatch-protocol");
			console.log('WebSocket - status ' + this.socket.readyState);
			this.socket.onopen = function(msg) {
				console.log("Connected. Status: " + this.readyState);
			};
			this.socket.onmessage = function(msg) {
				console.log("Received: " + msg.data);
				ws._run_handlers(msg.data);
			};
			this.socket.onclose = function(msg) {
				console.log("Disconnected. Status: " + this.readyState);
			};
		} catch (ex) {
			console.log(ex);
		}
		return this;
	};

	this.add_handler = function(handler) {
		this.handlers[this.numhandlers++] = handler;
		return this;
	};

	this.set_handler = function(event, handler) {
		if (!Number.isInteger(event)) {
			this.handlers[event] = handler;
		}
		return this;
	};

	this.send = function(msg) {
		try {
			this.socket.send(this.id + " " + msg);
		} catch (ex) {
			console.log(ex);
		}
		return this;
	}

	/**
	 * internal functions
	 */
	this._run_handlers = function(data) {
		for (idx in this.handlers) {
			if (Number.isInteger(parseInt(idx))) {
				this.handlers[idx](data);
			} else {
				if (data.substring(33, 33 + idx.length) == idx) {
					this.handlers[idx](data.substring(33 + idx.length + 1, data.length));
				}
			}
		}
	};

};

$(document).ready(function() {
	new $ajax().init();
});

