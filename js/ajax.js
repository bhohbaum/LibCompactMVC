/**
 * Umfragemodul fuer die Classrooms
 *
 * @author      Botho Hohbaum <b.hohbaum@compactmvc.de>
 * @package     VWA Classrooms
 * @copyright   Copyright (c) HTML Design 08.12.2011
 * @link		https://redmine.hive2.compactmvc.net/projects/vwa-classrooms
 */

var res_object;
var dec_object;

if(navigator.appName.search("Microsoft") > -1) {
	res_object = new ActiveXObject("MSXML2.XMLHTTP");
} else {
	res_object = new XMLHttpRequest();
}

/**
 * @param arr
 * @return JSON representation
 */
function array2json(arr) {
    var parts = [];
    for(var key in arr) {
    	var value = arr[key];
        if(typeof value == "object") { 
            parts[key] = array2json(value);
        } else {
            var str = "";
            str = '"' + key + '":';
            if(typeof value == "number") str += value;
            else if(value === false) str += 'false';
            else if(value === true) str += 'true';
            else str += '"' + value + '"';
            parts.push(str);
        }
    }
    var json = parts.join(",");
    return '{' + json + '}';
}

/**
 * 
 * @param method
 * @param data
 * @return
 */
function send_get_request(method, data) {
	var request = './jqueryLoading/survey/survey.ajax.php?sub0=ajax&sub1='+method+"&data="+escape(array2json(data));
//	alert(method + " " + array2json(data));
	res_object.open('get', request, true);
	res_object.onreadystatechange = handleResponse;
	res_object.send(null);
}

/**
 * 
 * @param method
 * @param data
 * @return
 */
function send_post_request(method, data) {
	var request = './jqueryLoading/forum/forum.ajax.php';
	var params = 'sub0=ajaxforum&sub1='+method+"&data="+encodeURI(array2json(data));
	res_object.open('post', request, true);
	res_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	res_object.setRequestHeader("Content-length", params.length);
	res_object.setRequestHeader("Connection", "close");
	res_object.onreadystatechange = handleResponse;
	res_object.send(params);
}


/**
 * 
 * @return
 */
function handleResponse() {
	if (res_object.readyState == 4) {
		if (res_object.status == 200) {
//			alert(res_object.responseText);
			dec_object = JSON.parse(res_object.responseText);
			eval(dec_object.handler);
		}
	}
}

/**
 * 
 * @return
 */
function set_target_content() {
	document.getElementById(dec_object['target']).innerHTML = dec_object['content'];
}

