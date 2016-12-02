<?php
if (file_exists('../../include/libcompactmvc.php')) include_once('../../include/libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * applicationmapper.php
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     digimap
 * @copyright   Copyright (c) Media Impression Unit 08
 * @link		http://www.miu08.de
 */
class ApplicationMapper extends ActionMapper {
	private $mapping2;
	private $mapping3;

	protected function __construct() {
		$langs = array("de");
		foreach ($langs as $lang) {
			$this->mapping2[$lang]["account"] = new LinkProperty("/" . $lang . "/account", false);
			$this->mapping2[$lang]["adminpanel"] = new LinkProperty("/" . $lang . "/adminpanel", false);
			$this->mapping2[$lang]["ajaxdba"] = new LinkProperty("/" . $lang . "/ajaxdba", false);
			$this->mapping2[$lang]["anschreiben"] = new LinkProperty("/" . $lang . "/anschreiben", false);
			$this->mapping2[$lang]["applicationmanagement"] = new LinkProperty("/" . $lang . "/applicationmanagement", false);
			$this->mapping2[$lang]["cli"] = new LinkProperty("/" . $lang . "/cli", false);
			$this->mapping2[$lang]["converterendpoint"] = new LinkProperty("/" . $lang . "/converterendpoint", false);
			$this->mapping2[$lang]["cverstellen"] = new LinkProperty("/" . $lang . "/cverstellen", false);
			$this->mapping2[$lang]["cvgesteuert"] = new LinkProperty("/" . $lang . "/cvgesteuert", false);
			$this->mapping2[$lang]["cvupload"] = new LinkProperty("/" . $lang . "/cvupload", false);
			$this->mapping2[$lang]["design"] = new LinkProperty("/" . $lang . "/design", false);
			$this->mapping2[$lang]["designedit"] = new LinkProperty("/" . $lang . "/designedit", false);
			$this->mapping2[$lang]["designedittest"] = new LinkProperty("/" . $lang . "/bewerbungsvorlagen-testen", true);
			$this->mapping2[$lang]["dokumente"] = new LinkProperty("/" . $lang . "/dokumente", false);
			$this->mapping2[$lang]["objects"] = new LinkProperty("/" . $lang . "/objects", false);
			$this->mapping2[$lang]["foto"] = new LinkProperty("/" . $lang . "/foto", false);
			$this->mapping2[$lang]["invoice"] = new LinkProperty("/" . $lang . "/invoice", false);
			$this->mapping2[$lang]["lebenslauf"] = new LinkProperty("/" . $lang . "/lebenslauf", false);
			$this->mapping2[$lang]["letter"] = new LinkProperty("/" . $lang . "/letter", false);
			$this->mapping2[$lang]["letterupload"] = new LinkProperty("/" . $lang . "/letterupload", false);
			$this->mapping2[$lang]["paymentend"] = new LinkProperty("/" . $lang . "/paymentend", false);
			$this->mapping2[$lang]["paymentnotify"] = new LinkProperty("/" . $lang . "/paymentnotify", false);
			$this->mapping2[$lang]["paymentstart"] = new LinkProperty("/" . $lang . "/paymentstart", false);
			$this->mapping2[$lang]["pdf"] = new LinkProperty("/" . $lang . "/pdf", false);
			$this->mapping2[$lang]["pdfgenerator"] = new LinkProperty("/" . $lang . "/pdfgenerator", false);
			$this->mapping2[$lang]["register"] = new LinkProperty("/" . $lang . "/register", true);
			$this->mapping2[$lang]["staticcontent"] = new LinkProperty("/" . $lang . "/s", false);

			$this->mapping3[$lang]["adminpanel"]["cmpconfirmrestrictedusers"] = new LinkProperty("/" . $lang . "/adminpanel/cmpconfirmrestrictedusers", false);
			$this->mapping3[$lang]["ajaxdba"]["application"] = new LinkProperty("/" . $lang . "/ajaxdba/application", false);
			$this->mapping3[$lang]["ajaxdba"]["application_has_cvupload"] = new LinkProperty("/" . $lang . "/ajaxdba/application_has_cvupload", false);
			$this->mapping3[$lang]["ajaxdba"]["application_has_document"] = new LinkProperty("/" . $lang . "/ajaxdba/application_has_document", false);
			$this->mapping3[$lang]["ajaxdba"]["application_has_letter_ul"] = new LinkProperty("/" . $lang . "/ajaxdba/application_has_letter_ul", false);
			$this->mapping3[$lang]["ajaxdba"]["application_has_profile_image"] = new LinkProperty("/" . $lang . "/ajaxdba/application_has_profile_image", false);
			$this->mapping3[$lang]["ajaxdba"]["application_parts"] = new LinkProperty("/" . $lang . "/ajaxdba/application_parts", false);
			$this->mapping3[$lang]["ajaxdba"]["civil_state"] = new LinkProperty("/" . $lang . "/ajaxdba/civil_state", false);
			$this->mapping3[$lang]["ajaxdba"]["country"] = new LinkProperty("/" . $lang . "/ajaxdba/country", false);
			$this->mapping3[$lang]["ajaxdba"]["current_signature"] = new LinkProperty("/" . $lang . "/ajaxdba/current_signature", false);
			$this->mapping3[$lang]["ajaxdba"]["cv_has_photo"] = new LinkProperty("/" . $lang . "/ajaxdba/cv_has_photo", false);
			$this->mapping3[$lang]["ajaxdba"]["cvorder"] = new LinkProperty("/" . $lang . "/ajaxdba/cvorder", false);
			$this->mapping3[$lang]["ajaxdba"]["cvtemplate"] = new LinkProperty("/" . $lang . "/ajaxdba/cvtemplate", false);
			$this->mapping3[$lang]["ajaxdba"]["cvulorder"] = new LinkProperty("/" . $lang . "/ajaxdba/cvulorder", false);
			$this->mapping3[$lang]["ajaxdba"]["documentorder"] = new LinkProperty("/" . $lang . "/ajaxdba/documentorder", false);
			$this->mapping3[$lang]["ajaxdba"]["has_uni_cust_code"] = new LinkProperty("/" . $lang . "/ajaxdba/has_uni_cust_code", false);
			$this->mapping3[$lang]["ajaxdba"]["include_signature"] = new LinkProperty("/" . $lang . "/ajaxdba/include_signature", false);
			$this->mapping3[$lang]["ajaxdba"]["imageorder"] = new LinkProperty("/" . $lang . "/ajaxdba/imageorder", false);
			$this->mapping3[$lang]["ajaxdba"]["imageupload"] = new LinkProperty("/" . $lang . "/ajaxdba/imageupload", false);
			$this->mapping3[$lang]["ajaxdba"]["letter"] = new LinkProperty("/" . $lang . "/ajaxdba/letter", false);
			$this->mapping3[$lang]["ajaxdba"]["letterorder"] = new LinkProperty("/" . $lang . "/ajaxdba/letterorder", false);
			$this->mapping3[$lang]["ajaxdba"]["letterulorder"] = new LinkProperty("/" . $lang . "/ajaxdba/letterulorder", false);
			$this->mapping3[$lang]["ajaxdba"]["lettertemplate"] = new LinkProperty("/" . $lang . "/ajaxdba/lettertemplate", false);
			$this->mapping3[$lang]["ajaxdba"]["login"] = new LinkProperty("/" . $lang . "/ajaxdba/login", false);
			$this->mapping3[$lang]["ajaxdba"]["logout"] = new LinkProperty("/" . $lang . "/ajaxdba/logout", false);
			$this->mapping3[$lang]["ajaxdba"]["nationality"] = new LinkProperty("/" . $lang . "/ajaxdba/nationality", false);
			$this->mapping3[$lang]["ajaxdba"]["numpages"] = new LinkProperty("/" . $lang . "/ajaxdba/numpages", false);
			$this->mapping3[$lang]["ajaxdba"]["partorder"] = new LinkProperty("/" . $lang . "/ajaxdba/partorder", false);
			$this->mapping3[$lang]["ajaxdba"]["pdfpagecount"] = new LinkProperty("/" . $lang . "/ajaxdba/pdfpagecount", false);
			$this->mapping3[$lang]["ajaxdba"]["pdfsize"] = new LinkProperty("/" . $lang . "/ajaxdba/pdfsize", false);
			$this->mapping3[$lang]["ajaxdba"]["pol"] = new LinkProperty("/" . $lang . "/ajaxdba/pol", false);
			$this->mapping3[$lang]["ajaxdba"]["phase_of_life"] = new LinkProperty("/" . $lang . "/ajaxdba/phase_of_life", false);
			$this->mapping3[$lang]["ajaxdba"]["register"] = new LinkProperty("/" . $lang . "/ajaxdba/register", false);
			$this->mapping3[$lang]["ajaxdba"]["request_tooltip"] = new LinkProperty("/" . $lang . "/ajaxdba/request_tooltip", false);
			$this->mapping3[$lang]["ajaxdba"]["request_uni_cust_code"] = new LinkProperty("/" . $lang . "/ajaxdba/request_uni_cust_code", false);
			$this->mapping3[$lang]["ajaxdba"]["scale_signature"] = new LinkProperty("/" . $lang . "/ajaxdba/scale_signature", false);
			$this->mapping3[$lang]["ajaxdba"]["sendmail"] = new LinkProperty("/" . $lang . "/ajaxdba/sendmail", false);
			$this->mapping3[$lang]["ajaxdba"]["tooltip"] = new LinkProperty("/" . $lang . "/ajaxdba/tooltip", false);
			$this->mapping3[$lang]["ajaxdba"]["user"] = new LinkProperty("/" . $lang . "/ajaxdba/user", false);
			$this->mapping3[$lang]["ajaxdba"]["validator"] = new LinkProperty("/" . $lang . "/ajaxdba/validator", false);
			$this->mapping3[$lang]["anschreiben"]["cmptexteditor"] = new LinkProperty("/" . $lang . "/anschreiben/cmptexteditor", false);
			$this->mapping3[$lang]["applicationmanagement"]["list"] = new LinkProperty("/" . $lang . "/applicationmanagement/list", false);
			$this->mapping3[$lang]["captcha"]["image"] = new LinkProperty("/" . $lang . "/captcha/image", false);
			$this->mapping3[$lang]["captcha"]["text"] = new LinkProperty("/" . $lang . "/captcha/text", false);
			$this->mapping3[$lang]["cverstellen"]["cmptexteditor"] = new LinkProperty("/" . $lang . "/cverstellen/cmptexteditor", false);
			$this->mapping3[$lang]["cvgesteuert"]["list"] = new LinkProperty("/" . $lang . "/cvgesteuert/list", true);
			$this->mapping3[$lang]["cvupload"]["cmpcvupload"] = new LinkProperty("/" . $lang . "/cvupload/cmpcvupload", false);
			$this->mapping3[$lang]["designedit"]["cmpprofileimageoverlay"] = new LinkProperty("/" . $lang . "/designedit/cmpprofileimageoverlay", false);
			$this->mapping3[$lang]["designedittest"]["cmpprofileimageoverlay"] = new LinkProperty("/" . $lang . "/bewerbungsvorlagen-testen/cmpprofileimageoverlay", false);
			$this->mapping3[$lang]["dokumente"]["cmpdocuments"] = new LinkProperty("/" . $lang . "/dokumente/cmpdocuments", false);
			$this->mapping3[$lang]["foto"]["cmpsignatureupload"] = new LinkProperty("/" . $lang . "/foto/cmpsignatureupload", false);
			$this->mapping3[$lang]["foto"]["cmpprofileimage"] = new LinkProperty("/" . $lang . "/foto/cmpprofileimage", false);
			$this->mapping3[$lang]["letterupload"]["cmpletterupload"] = new LinkProperty("/" . $lang . "/letterupload/cmpletterupload", false);
			$this->mapping3[$lang]["pdfgenerator"][PART_TYPE_FRONTCOVER] = new LinkProperty("/" . $lang . "/pdfgenerator/frontcover", false);
			$this->mapping3[$lang]["pdfgenerator"][PART_TYPE_ANSCHREIBEN] = new LinkProperty("/" . $lang . "/pdfgenerator/anschreiben", false);
			$this->mapping3[$lang]["pdfgenerator"][PART_TYPE_CVERSTELLEN] = new LinkProperty("/" . $lang . "/pdfgenerator/cverstellen", false);
			$this->mapping3[$lang]["pdfgenerator"][PART_TYPE_CVGUIDED] = new LinkProperty("/" . $lang . "/pdfgenerator/cvguided", false);
			$this->mapping3[$lang]["pdfgenerator"][PART_TYPE_DOCUMENTS] = new LinkProperty("/" . $lang . "/pdfgenerator/documents", false);
			$this->mapping3[$lang]["pdfgenerator"][PART_TYPE_BACKCOVER] = new LinkProperty("/" . $lang . "/pdfgenerator/backcover", false);
		}

		// static content with translated templates
		$this->mapping3["de"]["staticcontent"]["aboutus"] = new LinkProperty("/de/s/ueber-uns", true);
		$this->mapping3["de"]["staticcontent"]["agb"] = new LinkProperty("/de/s/agb", true);
		$this->mapping3["de"]["staticcontent"]["aktuell"] = new LinkProperty("/de/s/bewerbung-aktuell", true);
		$this->mapping3["de"]["staticcontent"]["bewerbungsmappe-lp-2"] = new LinkProperty("/de/s/bewerbungsmappe-lp-2", true);
		$this->mapping3["de"]["staticcontent"]["bewerbungsmappe-lp-3"] = new LinkProperty("/de/s/bewerbungsmappe-lp-3", true);
		$this->mapping3["de"]["staticcontent"]["bewerbungsmappe-lp-4"] = new LinkProperty("/de/s/bewerbungsmappe-lp-4", true);
		$this->mapping3["de"]["staticcontent"]["bewerbungsmappe-lp-5"] = new LinkProperty("/de/s/bewerbungsmappe-lp-5", true);
		$this->mapping3["de"]["staticcontent"]["bewerbungsmappen"] = new LinkProperty("/de/s/bewerbungsmappen", true);
		$this->mapping3["de"]["staticcontent"]["datenschutz"] = new LinkProperty("/de/s/datenschutz", true);
		$this->mapping3["de"]["staticcontent"]["downloads"] = new LinkProperty("/de/s/downloads-de", true);
		$this->mapping3["de"]["staticcontent"]["erstellen"] = new LinkProperty("/de/s/bewerbungsmappe-erstellen", true);
		$this->mapping3["de"]["staticcontent"]["faq"] = new LinkProperty("/de/s/faq-de", true);
		$this->mapping3["de"]["staticcontent"]["hochschulen"] = new LinkProperty("/de/s/hochschulen", true);
		$this->mapping3["de"]["staticcontent"]["home"] = new LinkProperty("/de/s/home", true);
		$this->mapping3["de"]["staticcontent"]["impressum"] = new LinkProperty("/de/s/impressum", true);
		$this->mapping3["de"]["staticcontent"]["kontakt"] = new LinkProperty("/de/s/kontakt", true);
		$this->mapping3["de"]["staticcontent"]["kontakt-danke"] = new LinkProperty("/de/s/kontakt-danke", false);
		$this->mapping3["de"]["staticcontent"]["kontakt-fehler"] = new LinkProperty("/de/s/kontakt-fehler", false);
		$this->mapping3["de"]["staticcontent"]["muster"] = new LinkProperty("/de/s/bewerbungsmappe-muster", true);
		$this->mapping3["de"]["staticcontent"]["nutzermeinungen"] = new LinkProperty("/de/s/nutzermeinungen", true);
		$this->mapping3["de"]["staticcontent"]["online-bewerbung"] = new LinkProperty("/de/s/online-bewerbung", true);
		$this->mapping3["de"]["staticcontent"]["pdf-reader"] = new LinkProperty("/de/s/pdfreader-de", true);
		$this->mapping3["de"]["staticcontent"]["portal-news"] = new LinkProperty("/de/s/portalnews-de", true);
		$this->mapping3["de"]["staticcontent"]["preis"] = new LinkProperty("/de/s/bewerbungsmappe-preis", true);
		$this->mapping3["de"]["staticcontent"]["sitemap"] = new LinkProperty("/de/s/sitemap-de", true);
		$this->mapping3["de"]["staticcontent"]["video"] = new LinkProperty("/de/s/video-de", true);

		parent::__construct();
	}

	public function get_base_url() {
		return BASE_URL;
	}

	protected function get_mapping_2() {
		return $this->mapping2;
	}

	protected function get_mapping_3() {
		return $this->mapping3;
	}

}
