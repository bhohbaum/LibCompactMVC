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
			var element = $(this);
			var url = "/" + element.attr("data-path") + "/" +
							element.attr("data-parm1") + "/" +
							element.attr("data-parm2") + "/" +
							element.attr("data-parm3") + "/" +
							element.attr("data-parm4");
			while (url != url.replace("/undefined", "")) {
				url = url.replace("/undefined", "");
			}
			var content = element.attr("data-content");
			new $ajax().ok(function(result) {
				var cmd = 'element.' + content;
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
			$_ajax[url]._cfg[url] = null;
		};
		this._cfg[url] = [];
		this._cfg[url].cbok = this._cbok;
		this._cfg[url].cberr = this._cberr;
		$_ajax[url] = this;
		xhr.send(this._data);
		return this;
	}
	
	
};

$(document).ready(function() {
	$(".ajax").each(function() {
		var element = $(this);
		var url = "/" + element.attr("data-path") + "/" +
						element.attr("data-parm1") + "/" +
						element.attr("data-parm2") + "/" +
						element.attr("data-parm3") + "/" +
						element.attr("data-parm4");
		while (url != url.replace("/undefined", "")) {
			url = url.replace("/undefined", "");
		}
		var content = element.attr("data-content");
		events = ["change", "keydown", "keyup", "click", "mouseup"];
		for (var i = 0; i < events.length; i++) {
			if (element.attr("data-" + events[i]) != null) {
				element.on(events[i], function(event) {
					var cmd = 'var data = element.' + element.attr("data-" + event.type);
					try {
						eval(cmd);
						new $ajax().data("&data=" + escape(data)).ok(function(result) {
							var cmd = 'element.' + content;
							try {
								eval(cmd);
							} catch (e) {
								console.log(e);
							}
						}).post(url + "#toserver");
					} catch (e) {
						console.log(e);
					}
				});
			}
		}
		new $ajax().ok(function(result) {
			var cmd = 'element.' + content;
			try {
				eval(cmd);
			} catch (e) {
				console.log(e);
			}
		}).get(url + "#fromserver");
	});
});

