<script type="text/javascript">

var tn = "<?= $this->get_value("table") ?>";

onFrameworkReady(function() {
	eval(`
		window.tablename = function() {
				${tn}.call(this);
		};
		window.tablename.prototype = Object.create(${tn}.prototype);
		window.tablename.prototype.constructor = eventlog;
	`);
});

var pglen = 15;
var idxsize = 10;
var curpage = 1;
var startidx = 0;
var sortcol = "id";
var sortdir = $DB_ORDER_ASCENDING;

function set_sort(col = "id") {
	sortdir = (sortdir == $DB_ORDER_ASCENDING) ? $DB_ORDER_DESCENDING : $DB_ORDER_ASCENDING;
	sortcol = col;
	show_list(startidx);
}

function search_filter() {
	var s = $("#search").val();
	var c = (new $DbFilter())
			.set_column_filter("id", s)
			.set_column_filter("name", "%" + s + "%")
			.set_column_filter("email", "%" + s + "%")
			.set_column_filter("pmtitle", "%" + s + "%")
			.set_column_filter("os", "%" + s + "%")
			.set_comparator($DB_COMPARE_LIKE)
			.set_logical_operator($DB_LOGIC_OPERATOR_OR);
	return c;	
}

function dc() {
	var c = (new $DbConstraint())
			.add_filter(search_filter())
			.set_column_filter("pmenabled", 1)
			.order_by(sortcol, sortdir);
	return c;
}

function gen_table_html(usr) {
	var content = "";
	for (i = 0; i < usr.length; i++) {
		var ckd1 = (usr[i].checked == 1) ? "checked='checked'" : "";
		var ckd2 = (usr[i].remember == 1) ? "checked='checked'" : "";
		var tmp = `
			<tr>
				<td>
					<label class="check-wrapper">
						<input type="checkbox" class="ajax" ${ckd1}
							data-path="<?= lnk("xhrtables", "user") ?>/${usr[i].id}/checked"
							data-change="$this.prop('checked') == true ? 1 : 0" />
						<span class="checkmark"></span>
					</label>
				</td>
				<td>
					<label class="check-wrapper">
						<input type="checkbox" class="ajax" ${ckd2}
							data-path="<?= lnk("xhrtables", "user") ?>/${usr[i].id}/remember"
							data-change="$this.prop('checked') == true ? 1 : 0" />
						<span class="checkmark"></span>
					</label>
				</td>
				<td>
					${usr[i].email}
				</td>
				<td>
					${usr[i].lang}
				</td>
				<td>
					${usr[i].os}
				</td>
				<td>
					${usr[i].pmtitle}
				</td>
				<td>￼
					${usr[i].name}
				</td>
				<td>
					${usr[i].gender}
				</td>
				<td>
					${usr[i].birthday}
				</td>
				<td class="smallfont">
					${usr[i].id}
				</td>
			</tr>`;
		content = content + tmp;
	}
	return content;
}

function show_list(idx) {
	var content = "";
	startidx = idx;
	(new tablename()).all_by(function(size) {
		curpage = (idx >= size - pglen) ? parseInt((size - 1) / pglen) + 1 : parseInt(idx / pglen) + 1;
		startidx = (curpage - 1) * pglen;
		(new tablename()).all_by(function(res) {
			$("#tablecontent").html(gen_table_html(res));
			new $ajax().init();
		}, dc().set_limit(startidx, pglen));
		load_index();
	}, dc().count_only());
}

function load_index() {
	var content = "Page: ";
	(new tablename()).all_by(function(size) {
		for (i = 0; i < size / pglen; i++) {
			var pg = i + 1;
			var idx = i * pglen;
			var tmp = "";
			var current = (i == curpage - 1) ? 'class="selected"' : '' ;
			var dots = ((size > (2 * idxsize + 5)) && (curpage > (idxsize + 2) || i > curpage)) ? "..." : "";
			if (pg == 1) {
				tmp = `<a ${current} onclick="show_list(${idx})">${pg}</a> ${dots} `;
				content += tmp;
				continue;
			}
			if ((curpage - (idxsize + 2)) < i && i < (curpage + idxsize)) {
				tmp = `<a ${current} onclick="show_list(${idx})">${pg}</a> `;
				content += tmp;
				continue;
			}
			var sub1 = (parseInt(size / pglen) < (size / pglen)) ? 0 : 1;
			dots = ((size > (2 * idxsize + 5)) && (curpage < parseInt((size / pglen) - idxsize - sub1))) ? "..." : "";
			if (i == parseInt(size / pglen) - sub1) {
				tmp = ` ${dots} <a ${current} onclick="show_list(${idx})">${pg}</a> `;
				content += tmp;
				continue;
			}
		}
		$("#pagelinks").html(content);
	}, dc().count_only());
}

function mark_all() {
	var u = new tablename();
	u.checked = 1;
	u.update_all(function(res) {
		show_list(startidx);
	}, dc());
}

function unmark_all() {
	var u = new tablename();
	u.checked = 0;
	u.update_all(function(res) {
		show_list(startidx);
	}, dc());
}

function mark_ios() {
	var u = new tablename();
	u.checked = 1;
	u.update_all(function(res) {
		show_list(startidx);
	}, dc().set_column_filter("os", "iOS"));
}

function mark_android() {
	var u = new tablename();
	u.checked = 1;
	u.update_all(function(res) {
		show_list(startidx);
	}, dc().set_column_filter("os", "android"));
}

function remember_all_marked() {
	var u = new tablename();
	u.remember = 1;
	u.update_all(function(res) {
		show_list(startidx);
	}, dc().set_column_filter("checked", 1));
} 

function forget_all_marked() {
	var u = new tablename();
	u.remember = 0;
	u.update_all(function(res) {
		show_list(startidx);
	}, dc().set_column_filter("checked", 1));
} 

function mark_all_remembered() {
	var u = new tablename();
	u.checked = 1;
	u.update_all(function(res) {
		show_list(startidx);
	}, dc().set_column_filter("remember", 1));
} 

function unmark_all_remembered() {
	var u = new tablename();
	u.checked = 0;
	u.update_all(function(res) {
		show_list(startidx);
	}, dc().set_column_filter("remember", 1));
} 

function clear_all_remembered() {
	var u = new tablename();
	u.remember = 0;
	u.update_all(function(res) {
		show_list(startidx);
	}, dc().set_column_filter("remember", 1));
} 

function mark_lang(lang) {
	var u = new tablename();
	u.checked = 1;
	u.update_all(function(res) {
		show_list(startidx);
	}, dc().set_column_filter("lang", lang));
}

function mark_gender(gender) {
	var u = new tablename();
	u.checked = 1;
	u.update_all(function(res) {
		show_list(startidx);
	}, dc().set_column_filter("gender", gender));
}

function invert_remembered() {
	var u = new tablename();
	u.remember = 2;
	u.update_all(function(res) {
		var v = new tablename();
		v.remember = 0;
		v.update_all(function(res) {
			var w = new tablename();
			w.remember = 1;
			w.update_all(function(res) {
				show_list(startidx);
			}, dc().set_column_filter("remember", 2));
		}, dc().set_column_filter("remember", 1));
	}, dc().set_column_filter("remember", 0));
} 

function invert_marked() {
	var u = new tablename();
	u.checked = 2;
	u.update_all(function(res) {
		var v = new tablename();
		v.checked = 0;
		v.update_all(function(res) {
			var w = new tablename();
			w.checked = 1;
			w.update_all(function(res) {
				show_list(startidx);
			}, dc().set_column_filter("checked", 2));
		}, dc().set_column_filter("checked", 1));
	}, dc().set_column_filter("checked", 0));
} 

onFrameworkReady(function() {
	show_list(0);
	$("#search").on("keyup", function() {
		show_list(startidx);
	});
});

</script>


<div class="row">
	<div class="col-xs-12">
		<div class="table-responsive">
			<h4>Users</h4>
			<p>
				<label for="search">Search</label>
				<input type="text" id="search"></input>
			</p>
			<table class="table table-striped">
				<thead>
					<tr>
						<th onclick="set_sort('checked')">Marked</th>
						<th onclick="set_sort('remember')">Remembered</th>
						<th onclick="set_sort('email')">E-Mail</th>
						<th onclick="set_sort('lang')">Language</th>
						<th onclick="set_sort('os')">OS</th>
						<th onclick="set_sort('pmtitle')">PM Title</th>
						<th onclick="set_sort('name')">Name</th>
						<th onclick="set_sort('gender')">Geschlecht</th>
						<th onclick="set_sort('birthday')">Geburtstag</th>
						<th onclick="set_sort('id')">ID</th>
					</tr>
				</thead>
				<tbody id="tablecontent">
				</tbody>
				<tr>
					<td id="pagelinks" colspan="10">
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>
<div class="ib">
	<div class="row buttons">
		<div class="left">
			<button onclick="mark_all()">Mark all</button>
			<button onclick="unmark_all()">Un-mark all</button>
			<button onclick="mark_ios()">Mark iOS users</button>
			<button onclick="mark_android()">Mark android users</button>
			<button onclick="mark_lang('en')">Mark english users</button>
			<button onclick="mark_lang('de')">Mark german users</button>
			<button onclick="mark_gender('M')">Mark male users</button>
			<button onclick="mark_gender('F')">Mark female users</button>
			<button onclick="invert_marked()" class="hspacer40">Invert marked selection</button>
		</div>
		<div class="left">
			<button onclick="remember_all_marked()">Remember all marked</button>
			<button onclick="forget_all_marked()">Forget all marked</button>
			<button onclick="mark_all_remembered()">Mark all remembered</button>
			<button onclick="unmark_all_remembered()">Un-mark all remembered</button>
			<button onclick="clear_all_remembered()">Clear all remembered</button>
			<button onclick="invert_remembered()">Invert remembered selection</button>
		</div>
	</div>
</div>
<div class="row bottomsp">
</div>
