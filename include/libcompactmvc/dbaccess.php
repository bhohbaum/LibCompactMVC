<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

// this class handles our DB connection and requests

class DbAccess {
	
	private $mysqli;
	
	// keeps instance of the classs
	private static $instance;
	
	private function __construct() {
		$this->mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
		
		if (mysqli_connect_error()) {
		    die('Connect Error ('.mysqli_connect_errno().') '. mysqli_connect_error());
		}
	}
	
	public function __destruct() {
		$this->close_db();
	}
	
	// prevent cloning
	private function __clone()
	{
		;
	}
	
	/**
	 * @return returns the instance of this class. this is a singleton. there can only be one instance.
	 */
	public static function get_instance() 
	{
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}
	
	public function close_db() {
		if ($this->mysqli != null) {
			$this->mysqli->close();
			$this->mysqli = null;
		}
	}
	
	private function run_query($query, $has_multi_result = false, $object = false, $field = null) {
		$ret = null;
		if (!($result = $this->mysqli->query($query))) {
			throw new Exception(ErrorMessages::DB_QUERY_ERROR.$this->mysqli->error."\nQuery: ".$query);
	    } else {
	    	if (is_object($result)) {
		    	if ($has_multi_result) {
			    	if ($object) {
					    while ($row = $result->fetch_object()) {
					    	if ($field != null) {
					    		$ret[] = $row[$field];
					    	} else {
								$ret[] = $row;
					    	}
						}
			    	} else {
					    while ($row = $result->fetch_assoc()) {
					    	if ($field != null) {
					    		$ret[] = $row[$field];
					    	} else {
								$ret[] = $row;
					    	}
						}
			    	}
		    	} else {
			    	if ($object) {
					    while ($row = $result->fetch_object()) {
					    	if ($field != null) {
					    		$ret = $row[$field];
					    	} else {
								$ret = $row;
					    	}
						}
			    	} else {
					    while ($row = $result->fetch_assoc()) {
					    	if ($field != null) {
					    		$ret = $row[$field];
					    	} else {
								$ret = $row;
					    	}
						}
			    	}
		    	}
				$result->close();
	    	} else {
	    		$ret = $this->mysqli->insert_id;
	    	}
		}
		if (($ret == null) && ($has_multi_result == true)) {
			$ret = array();
		}
		return $ret;
	}

	public function get_image_details_by_image_id($id, $obj = false) {
		$q = "SELECT	*
				FROM	img_details
				WHERE	fk_id_images = '".mysql_escape_string($id)."'";
		return $this->run_query($q, false, $obj);
	}
	
	public function del_image_details_by_image_id($id) {
		$q = "DELETE FROM img_details
				WHERE	fk_id_images = '".mysql_escape_string($id)."'";
		return $this->run_query($q);
	}
	
	public function del_image_by_id($id) {
		$q = "DELETE FROM images
				WHERE	id = '".mysql_escape_string($id)."'";
		return $this->run_query($q);
	}
	
	public function del_cmimage_by_id($id) {
		$q = "DELETE FROM cmimgs
				WHERE	id = '".mysql_escape_string($id)."'";
		return $this->run_query($q);
	}
	
	public function del_craftsman_by_id($id) {
		$q = "DELETE FROM craftsman
				WHERE	id = '".mysql_escape_string($id)."'";
		return $this->run_query($q);
	}
	
	public function del_cmimg_by_id($id) {
		$q = "DELETE FROM cmimgs
				WHERE	id = '".mysql_escape_string($id)."'";
		return $this->run_query($q);
	}
	
	public function del_cmimgs_by_craftsman_id($id) {
		$q = "DELETE FROM cmimgs
				WHERE	fk_id_craftsman = '".mysql_escape_string($id)."'";
		return $this->run_query($q);
	}
	
	public function del_cmtexts_by_craftsman_id($id) {
		$q = "DELETE FROM cmtexts
				WHERE	fk_id_craftsman = '".mysql_escape_string($id)."'";
		return $this->run_query($q);
	}
	
	public function del_cmtext_by_id($id) {
		$q = "DELETE FROM cmtexts
				WHERE	id = '".mysql_escape_string($id)."'";
		return $this->run_query($q);
	}
	
	public function get_craftsman_by_id($id, $obj = false) {
		$q = "SELECT	*
				FROM	craftsman
				WHERE	id = '".mysql_escape_string($id)."'";
		return $this->run_query($q, false, $obj);
	}
	
	public function get_craftsman_by_code($code, $obj = false) {
		$q = "SELECT	*
				FROM	craftsman
				WHERE	code = '".mysql_escape_string($code)."'";
		return $this->run_query($q, false, $obj);
	}
	
	public function get_craftsman_by_subdomain($subdomain, $obj = false) {
		$q = "SELECT	*
				FROM	craftsman
				WHERE	subdomain = '".mysql_escape_string($subdomain)."'";
		return $this->run_query($q, false, $obj);
	}
	
	public function get_image_by_id($id, $obj = false) {
		$q = "SELECT	*
				FROM	images
				WHERE	id = '".mysql_escape_string($id)."'";
		return $this->run_query($q, false, $obj);
	}
	
	public function get_cmimage_by_id($id, $obj = false) {
		$q = "SELECT	*
				FROM	cmimgs
				WHERE	id = '".mysql_escape_string($id)."'";
		return $this->run_query($q, false, $obj);
	}
	
	public function get_cmimgs_by_cmid($id, $obj = false) {
		$q = "SELECT	*
				FROM	cmimgs
				WHERE	fk_id_craftsman = '".mysql_escape_string($id)."'";
		return $this->run_query($q, true, $obj);
	}
	
	public function get_cmtexts_by_cmid($id, $obj = false) {
		$q = "SELECT	*
				FROM	cmtexts
				WHERE	fk_id_craftsman = '".mysql_escape_string($id)."'";
		return $this->run_query($q, true, $obj);
	}
	
	public function get_image_by_name($name, $obj = false) {
		$q = "SELECT	*
				FROM	images
				WHERE	name = '".mysql_escape_string($name)."'";
		return $this->run_query($q, false, $obj);
	}
	
	public function get_cmimg_type_by_name($name, $obj = false) {
		$q = "SELECT	*
				FROM	cmimg_type
				WHERE	name = '".mysql_escape_string($name)."'";
		return $this->run_query($q, false, $obj);
	}
	
	public function get_cmtxt_type_by_name($name, $obj = false) {
		$q = "SELECT	*
				FROM	cmtxt_type
				WHERE	name = '".mysql_escape_string($name)."'";
		return $this->run_query($q, false, $obj);
	}
	
	public function get_cmimage_by_name($name, $obj = false) {
		return $this->get_cmimage_by_code(str_replace(".jpg", "", $name));
	}
	
	public function get_cmimage_by_code($code, $obj = false) {
		$q = "SELECT	*
				FROM	cmimgs
				WHERE	code = '".mysql_escape_string($code)."'";
		return $this->run_query($q, false, $obj);
	}
	
	public function get_cmimages_by_cmid($id, $obj = false) {
		$q = "SELECT	*
				FROM	cmimgs
				WHERE	fk_id_craftsman = '".mysql_escape_string($id)."'";
		return $this->run_query($q, true, $obj);
	}
	
	public function get_cmimage_by_cmid_and_typename($id, $typename, $obj = false) {
		$q = "SELECT	ci.*
				FROM	cmimgs AS ci
			INNER JOIN	cmimg_type AS cit 
				ON		ci.fk_id_cmimg_type = cit.id
				WHERE	ci.fk_id_craftsman = '".mysql_escape_string($id)."'
				AND		cit.name = '".mysql_escape_string($typename)."'";
		return $this->run_query($q, false, $obj);
	}
	
	public function get_cmtext_by_cmid_and_typename($id, $typename, $obj = false) {
		$q = "SELECT	ct.*
				FROM	cmtexts AS ct
			INNER JOIN	cmtxt_type AS ctt 
				ON		ct.fk_id_cmtxt_type = ctt.id
				WHERE	ct.fk_id_craftsman = '".mysql_escape_string($id)."'
				AND		ctt.name = '".mysql_escape_string($typename)."'";
		return $this->run_query($q, false, $obj);
	}
	
	public function get_all_cmimg_types($obj = false) {
		$q = "SELECT	*
				FROM	cmimg_type";
		return $this->run_query($q, true, $obj);
	}
	
	public function get_all_cmtxt_types($obj = false) {
		$q = "SELECT	*
				FROM	cmtxt_type";
		return $this->run_query($q, true, $obj);
	}
	
	public function get_image_by_pdfname($name, $obj = false) {
		$q = "SELECT	*
				FROM	images
				WHERE	pdfname = '".mysql_escape_string($name)."'";
		return $this->run_query($q, false, $obj);
	}
	
	public function get_impressionen_ids() {
		$q = "SELECT	images.id
				FROM	images
			INNER JOIN	map_img_fltentry
				ON		images.id = map_img_fltentry.fk_id_images
				WHERE	map_img_fltentry.fk_id_filter_entry = '".ID_FLT_IMPRESSIONEN."'";
		return $this->run_query($q, true, false, "id");
	}

	public function search_images($search, $obj = false) {
		$q = "SELECT	*
				FROM	images AS i
			INNER JOIN	img_details AS id
				ON		id.fk_id_images = i.id
				WHERE	i.name LIKE '%".mysql_escape_string($search)."%'
				OR		id.title LIKE '%".mysql_escape_string($search)."%'
				OR		id.description LIKE '%".mysql_escape_string($search)."%'
				OR		id.date LIKE '%".mysql_escape_string($search)."%'
			ORDER BY	id.date DESC";
		return $this->run_query($q, true, $obj);
	}
	
	public function search_craftsmen($search, $obj = false) {
		$q = "SELECT	*
				FROM	craftsman AS c
				WHERE	c.code LIKE '%".mysql_escape_string($search)."%'
				OR		c.subdomain LIKE '%".mysql_escape_string($search)."%'
				OR		c.company_name LIKE '%".mysql_escape_string($search)."%'
				OR		c.address LIKE '%".mysql_escape_string($search)."%'
				OR		c.tel LIKE '%".mysql_escape_string($search)."%'
				OR		c.email LIKE '%".mysql_escape_string($search)."%'
				OR		c.date LIKE '%".mysql_escape_string($search)."%'
			ORDER BY	c.date DESC";
		return $this->run_query($q, true, $obj);
	}
	
	public function search_avail_non_ground_views($curid, $search, $obj = false) {
		$q = "SELECT	*
				FROM	images AS i
			INNER JOIN	img_details AS id
				ON		id.fk_id_images = i.id
				WHERE	i.fk_id_ground_view = '".mysql_escape_string($curid)."'
				AND		(i.name LIKE '%".mysql_escape_string($search)."%'
				OR		id.title LIKE '%".mysql_escape_string($search)."%'
				OR		id.description LIKE '%".mysql_escape_string($search)."%')";
		$arr1 = $this->run_query($q, true, $obj);
		$q = "SELECT	*
				FROM	images AS i
			INNER JOIN	img_details AS id
				ON		id.fk_id_images = i.id
				WHERE	i.is_ground_view = false
				AND		(i.name LIKE '%".mysql_escape_string($search)."%'
				OR		id.title LIKE '%".mysql_escape_string($search)."%'
				OR		id.description LIKE '%".mysql_escape_string($search)."%')";
		$arr2 = $this->run_query($q, true, $obj);
		foreach ($arr2 as $record) {
			if ($record["fk_id_ground_view"] != $curid) {
				$arr1[] = $record;
			}
		}
		return $arr1;
	}
	
	public function get_all_filter_categories($obj = false) {
		$q = "SELECT	*
				FROM	filter_category";
		return $this->run_query($q, true, $obj);
	}
	
	public function get_all_filtergroups($obj = false) {
		$q = "SELECT	*
				FROM	filtergroup";
		return $this->run_query($q, true, $obj);
	}
	
	public function get_all_filter_entries($obj = false) {
		$q = "SELECT	*
				FROM	filter_entry";
		return $this->run_query($q, true, $obj);
	}
	
	public function get_filtergroup_by_id($id, $obj = false) {
		$q = "SELECT	*
				FROM	filtergroup
				WHERE	id = '".mysql_escape_string($id)."'";
		return $this->run_query($q, false, $obj);
	}
	
	public function get_filtergroup_by_entry_id($id, $obj = false) {
		$q = "SELECT	fg.*
				FROM	filtergroup as fg, filter_entry as fe
				WHERE	fg.id = fe.fk_id_filtergroup
				AND		fe.id = '".mysql_escape_string($id)."'";
		return $this->run_query($q, false, $obj);
	}
	
	public function get_filtergroups($obj = false) {
		$q = "SELECT	filtergroup.*
				FROM	filtergroup, filter_type
				WHERE	filtergroup.fk_id_filter_type = filter_type.id";
		return $this->run_query($q, true, $obj);
	}
	
	public function get_filtergroups_for_category_id($id, $obj = false) {
		$q = "SELECT	*
				FROM	filtergroup
				WHERE	fk_id_filter_category = '".mysql_escape_string($id)."'";
		return $this->run_query($q, true, $obj);
	}
	
	public function get_filter_entry_by_id($id, $obj = false) {
		$q = "SELECT	*
				FROM	filter_entry
				WHERE	id = '".mysql_escape_string($id)."'
			ORDER BY	ordinal ASC, name ASC";
		return $this->run_query($q, false, $obj);
	}
	
	public function get_filter_entries($obj = false) {
		$q = "SELECT	*
				FROM	filter_entry
			ORDER BY	ordinal ASC, name ASC";
		return $this->run_query($q, true, $obj);
	}
	
	public function get_filter_entries_for_group_id($id, $obj = false) {
		$q = "SELECT	*
				FROM	filter_entry
				WHERE	fk_id_filtergroup = '".mysql_escape_string($id)."'
			ORDER BY	ordinal ASC, name ASC";
		return $this->run_query($q, true, $obj);
	}
	
	public function get_filter_entry_ids_for_image_id($id, $obj = false) {
		$q = "SELECT	filter_entry.id
				FROM	filter_entry
			INNER JOIN	map_img_fltentry
				ON		filter_entry.id = map_img_fltentry.fk_id_filter_entry
			INNER JOIN	images
				ON		map_img_fltentry.fk_id_images = images.id
				WHERE	images.id = '".mysql_escape_string($id)."'";
		return $this->run_query($q, true, $obj, "id");
	}
	
	public function get_filter_type_by_id($id, $obj = false) {
		$q = "SELECT	*
				FROM	filter_type
				WHERE	id = '".mysql_escape_string($id)."'";
		return $this->run_query($q, false, $obj);
	}
	
	public function get_filter_types($obj = false) {
		$q = "SELECT	*
				FROM	filter_type";
		return $this->run_query($q, true, $obj);
	}
	
	public function set_filter($imgid, $fltid) {
		$q = "INSERT INTO map_img_fltentry 
						(fk_id_images, fk_id_filter_entry)
				VALUES 	('".mysql_escape_string($imgid)."', 
						'".mysql_escape_string($fltid)."')";
		return $this->run_query($q);
	}
	
	public function del_filter($imgid, $fltid) {
		$q = "DELETE FROM map_img_fltentry
				WHERE	fk_id_images = '".mysql_escape_string($imgid)."'
				AND		fk_id_filter_entry = '".mysql_escape_string($fltid)."'";
		return $this->run_query($q);
	}
	
	public function del_filter_by_image_id($id) {
		$q = "DELETE FROM map_img_fltentry
				WHERE	fk_id_images = '".mysql_escape_string($id)."'";
		return $this->run_query($q);
	}
	
	public function del_filter_by_id($id) {
		$q = "DELETE FROM map_img_fltentry
				WHERE	id = '".mysql_escape_string($id)."'";
		return $this->run_query($q);
	}
	
	public function create_cmimg($fk_id_craftsman, $fk_id_cmimg_type) {
		if (!is_numeric($fk_id_cmimg_type)) {
			$fk_id_cmimg_type = $this->get_cmimg_type_by_name($fk_id_cmimg_type, true)->id;
		}
		$q = "INSERT INTO cmimgs 
						(fk_id_cmimg_type, fk_id_craftsman)
				VALUES 	('".mysql_escape_string($fk_id_cmimg_type)."',
						'".mysql_escape_string($fk_id_craftsman)."')";
		return $this->run_query($q);
	}
	
	public function create_cmtxt($fk_id_craftsman, $fk_id_cmtxt_type, $text) {
		if (!is_numeric($fk_id_cmtxt_type)) {
			$fk_id_cmtxt_type = $this->get_cmtxt_type_by_name($fk_id_cmtxt_type, true)->id;
		}
		$q = "INSERT INTO cmtexts 
						(fk_id_cmtxt_type, fk_id_craftsman, text)
				VALUES 	('".mysql_escape_string($fk_id_cmtxt_type)."',
						'".mysql_escape_string($fk_id_craftsman)."',
						'".mysql_escape_string($text)."')";
		return $this->run_query($q);
	}
	
	public function create_craftsman($subdomain, $company_name, $address, $tel, $email, $allespasst) {
		$q = "INSERT INTO craftsman 
						(subdomain, company_name, address, tel, email, allespasst)
				VALUES 	('".mysql_escape_string($subdomain)."',
						'".mysql_escape_string($company_name)."',
						'".mysql_escape_string($address)."',  
						'".mysql_escape_string($tel)."',  
						'".mysql_escape_string($email)."',  
						'".mysql_escape_string($allespasst)."')";
		return $this->run_query($q);
	}
	
	public function create_image($name, $pdfname, $fk_id_ground_view = null, $is_ground_view = false) {
		if (($fk_id_ground_view == null) || ($fk_id_ground_view == "")) {
			$fk_id_ground_view = "null";
		}
		if (($pdfname == null) || ($pdfname == "")) {
			$q = "INSERT INTO images 
						(fk_id_ground_view, is_ground_view, name, pdfname)
				VALUES 	(".mysql_escape_string($fk_id_ground_view).",
						'".mysql_escape_string($is_ground_view)."',  
						'".mysql_escape_string($name)."',  
						null)";
		} else {
			$q = "INSERT INTO images 
							(fk_id_ground_view, is_ground_view, name, pdfname)
					VALUES 	(".mysql_escape_string($fk_id_ground_view).",
							'".mysql_escape_string($is_ground_view)."',  
							'".mysql_escape_string($name)."',  
							'".mysql_escape_string($pdfname)."')";
		}
		return $this->run_query($q);
	}
	
	public function update_image($id, $name, $pdfname, $fk_id_ground_view = null, $is_ground_view = false) {
		if (($fk_id_ground_view == null) || ($fk_id_ground_view == "")) {
			$fk_id_ground_view = "null";
		}
		if (($pdfname == null) || ($pdfname == "")) {
			$q = "UPDATE 	images
					SET		fk_id_ground_view = ".mysql_escape_string($fk_id_ground_view).",
							is_ground_view = 	'".mysql_escape_string($is_ground_view)."',
							name = 				'".mysql_escape_string($name)."',
							pdfname = 			null
					WHERE	id = 				'".mysql_escape_string($id)."'";
		} else {
			$q = "UPDATE 	images
					SET		fk_id_ground_view = ".mysql_escape_string($fk_id_ground_view).",
							is_ground_view = 	'".mysql_escape_string($is_ground_view)."',
							name = 				'".mysql_escape_string($name)."',
							pdfname = 			'".mysql_escape_string($pdfname)."'
					WHERE	id = 				'".mysql_escape_string($id)."'";
		}
		return $this->run_query($q);
	}
	
	public function create_img_details($fk_id_images, $title, $description) {
		$q = "INSERT INTO img_details
						(fk_id_images, title, description)
				VALUES 	('".mysql_escape_string($fk_id_images)."',
						'".mysql_escape_string($title)."',  
						'".mysql_escape_string($description)."')";
		return $this->run_query($q);
	}
	
	public function update_img_details($id, $fk_id_images, $title, $description) {
		$q = "UPDATE 	img_details
				SET		fk_id_images = 	'".mysql_escape_string($fk_id_ground_view)."',
						title = 		'".mysql_escape_string($is_ground_view)."',
						description = 	'".mysql_escape_string($name)."'
				WHERE	id = 			'".mysql_escape_string($id)."'";
		return $this->run_query($q);
	}
	
	public function update_craftsman($id, $subdomain, $company_name, $address, $tel, $email, $allespasst) {
		$q = "UPDATE 	craftsman
				SET		company_name = 		'".mysql_escape_string($company_name)."',
						subdomain =			'".mysql_escape_string($subdomain)."',
						address = 			'".mysql_escape_string($address)."',
						tel = 				'".mysql_escape_string($tel)."',
						email = 			'".mysql_escape_string($email)."',
						allespasst =		'".mysql_escape_string($allespasst)."'
				WHERE	id = 				'".mysql_escape_string($id)."'";
		return $this->run_query($q);
	}
	
	public function get_images_for_ground_view_id($id, $obj = false) {
		$q = "SELECT	*
				FROM	images
				WHERE	fk_id_ground_view = '".mysql_escape_string($id)."'";
		return $this->run_query($q, true, $obj);
	}
	
	public function get_image_ids_for_filter_ids($idarr, $obj = false) {
		$grouped = array();
		foreach ($idarr as $fltid) {
			$group = $this->get_filtergroup_by_entry_id($fltid);
			$grouped[$group["id"]][] = $fltid;
		}
		$loopctr = 0;
		foreach ($grouped as $group) {
			$idstr = "(m.fk_id_filter_entry = ".implode(" OR m.fk_id_filter_entry = ", $group).") AND ";
			$idstr = substr($idstr, 0, -4);
			if ($loopctr == 0) {
				$q = "SELECT 	i.id AS id, m.fk_id_filter_entry AS fk_id_filter_entry
						FROM	images as i, map_img_fltentry as m 
						WHERE	i.id = m.fk_id_images
						AND		".$idstr;
			} else {
				$q = "SELECT DISTINCT sub.id AS id, m.fk_id_filter_entry AS fk_id_filter_entry
						FROM	(".$q.") AS sub, map_img_fltentry as m
						WHERE	sub.id = m.fk_id_images
						AND		".$idstr;
			}
			$loopctr++;
		}
		if (isset($q)) {
			return $this->run_query($q, true, $obj, "id");
		} else {
			return array();
		}
		// should never be here
		return null;
	}




	
	
	
	
}	

?>
