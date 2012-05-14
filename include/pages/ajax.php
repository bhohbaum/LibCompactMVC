<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * AJAX request handler
 *
 * @author      Botho Hohbaum <b.hohbaum@compactmvc.de>
 * @package     VWA Classrooms
 * @copyright   Copyright (c) HTML Design 09.12.2011
 * @link		https://redmine.hive2.compactmvc.net/projects/vwa-classrooms
 */


class Ajax extends Page {

	private $sub0;
	private $data;
	
	private $ajaxview;
	private $surveylib;
	
	private $return;
	
	protected function retrieve_data() {
		$this->ajaxview = new View();
		$this->ajaxview->add_template("umfrage_ajax.php");
		$this->ajaxview->activate("AJAX");
		$this->sub1 = isset($_REQUEST['sub1']) ? $_REQUEST['sub1'] : "";
		$this->data = isset($_REQUEST['data']) ? $_REQUEST['data'] : NULL;
		if ($this->data != NULL) {
			$this->data = json_decode($this->data, true);
		}
		$this->surveylib = new SurveyLib($this->ajaxview, $this->db);
	}
	
	protected function run_page_logic() {
		$this->view->add_template("umfrage_ajax.php");
		$this->view->activate("AJAX_OUT");
		$this->return['handler'] = "set_target_content()";
	
		switch ($this->sub1) {
			case "get_surveys":
				$this->get_surveys();
				break;
			case "get_survey_form":
				$this->get_survey_form();
				break;
			case "save_survey":
				$this->save_survey();
				break;
			case "delete_survey":
				$this->delete_survey();
				break;
			case "edit_survey":
				$this->edit_survey();
				break;
				
			case "get_sections":
				$this->get_sections();
				break;
			case "get_section_form":
				$this->get_section_form();
				break;
			case "save_section":
				$this->save_section();
				break;
			case "delete_section":
				$this->delete_section();
				break;
			case "edit_section":
				$this->edit_section();
				break;
				
			case "get_questions":
				$this->get_questions();
				break;
			case "get_question_form":
				$this->get_question_form();
				break;
			case "save_question":
				$this->save_question();
				break;
			case "delete_question":
				$this->delete_question();
				break;
			case "edit_question":
				$this->edit_question();
				break;
				
			case "get_answers":
				$this->get_answers();
				break;
			case "get_answer_form":
				$this->get_answer_form();
				break;
			case "save_answer":
				$this->save_answer();
				break;
			case "delete_answer":
				$this->delete_answer();
				break;
			case "edit_answer":
				$this->edit_answer();
				break;
			case "add_default_answers":
				$this->add_default_answers();
				break;
				
			case "show_survey":
				$this->show_survey();
				break;
				
				
		}
		if (isset($this->data['target'])) {
			$this->return['target'] = $this->data['target'];
		}
		$this->view->set_value("AJAX_OB", json_encode($this->return));
	}


	private function get_surveys() {
		$this->ajaxview->activate("surveys");
		$this->ajaxview->set_value("surveys", $this->db->get_surveys_by_classroom_id($this->data['fk_id_classroom']));
		$this->return['content'] = $this->ajaxview->render();
	}
	
	private function get_survey_form() {
		$this->ajaxview->activate("survey_form");
		$this->ajaxview->set_value("fk_id_classroom", $this->data['fk_id_classroom']);
		$this->return['content'] = $this->ajaxview->render();
	}

	private function save_survey() {
		if ($this->data['id'] != "") {
			$this->db->update_survey(
						$this->data['id'],
						$this->data['fk_id_classroom'],
						$_SESSION['userID'], 
						$this->data['name'],
						$this->data['bold_title'],
						$this->data['study_course'],
						$this->data['semester'],
						$this->data['num_participants'],
						$this->data['description'],
						$this->data['start_date'],
						$this->data['end_date'],
						$this->data['status'],
						$this->surveylib->get_timestamp()
					);
		} else {
			$this->db->add_survey(
						$this->data['fk_id_classroom'],
						$_SESSION['userID'], 
						$this->data['name'],
						$this->data['bold_title'],
						$this->data['study_course'],
						$this->data['semester'],
						$this->data['num_participants'],
						$this->data['description'],
						$this->data['start_date'],
						$this->data['end_date'],
						$this->data['status'],
						$this->surveylib->get_timestamp()
					);
		}
		$this->ajaxview->activate("surveys");
		$this->ajaxview->set_value("surveys", $this->db->get_surveys());
		$this->return['content'] = $this->ajaxview->render();
	}
	
	private function delete_survey() {
		$this->db->delete_survey($this->data['id']);
		$this->ajaxview->activate("surveys");
		$this->ajaxview->set_value("surveys", $this->db->get_surveys());
		$this->return['content'] = $this->ajaxview->render();
	}
	
	private function edit_survey() {
		$survey = $this->db->get_survey_by_id($this->data['id']);
		$this->ajaxview->activate("survey_form");
		$this->ajaxview->set_value("id", $survey[0]['id']);
		$this->ajaxview->set_value("fk_id_classroom", $survey[0]['fk_id_classroom']);
		$this->ajaxview->set_value("fk_id_user", $survey[0]['fk_id_user']);
		$this->ajaxview->set_value("name", $survey[0]['name']);
		$this->ajaxview->set_value("bold_title", $survey[0]['bold_title']);
		$this->ajaxview->set_value("study_course", $survey[0]['study_course']);
		$this->ajaxview->set_value("semester", $survey[0]['semester']);
		$this->ajaxview->set_value("num_participants", $survey[0]['num_participants']);
		$this->ajaxview->set_value("description", $survey[0]['description']);
		$this->ajaxview->set_value("start_date", $survey[0]['start_date']);
		$this->ajaxview->set_value("end_date", $survey[0]['end_date']);
		$this->ajaxview->set_value("status", $survey[0]['status']);
		$this->ajaxview->set_value("lastchange", $survey[0]['lastchange']);
		$this->return['content'] = $this->ajaxview->render();
	}
	
	
	
	private function get_sections() {
		$this->ajaxview->activate("sections");
		$this->ajaxview->set_value("sections", $this->db->get_sections_by_survey_id($this->data['fk_id_survey']));
		$this->ajaxview->set_value("fk_id_survey", $this->data['fk_id_survey']);
		$this->return['content'] = $this->ajaxview->render();
	}
	
	private function get_section_form() {
		$this->ajaxview->activate("section_form");
		$this->ajaxview->set_value("fk_id_survey", $this->data['fk_id_survey']);
		$this->return['content'] = $this->ajaxview->render();
	}

	private function save_section() {
		if ($this->data['id'] != "") {
			$this->db->update_section(
						$this->data['id'],
						$this->data['fk_id_survey'],
						$_SESSION['userID'], 
						$this->data['name'],
						$this->data['title'],
						$this->data['description'],
						$this->data['comment'],
						$this->data['type'],
						$this->data['position'],
						$this->data['grading'],
						$this->surveylib->get_timestamp()
					);
		} else {
			$this->db->add_section(
						$this->data['fk_id_survey'],
						$_SESSION['userID'], 
						$this->data['name'],
						$this->data['title'],
						$this->data['description'],
						$this->data['comment'],
						$this->data['type'],
						$this->data['position'],
						$this->data['grading'],
						$this->surveylib->get_timestamp()
					);
		}
		$this->ajaxview->activate("sections");
		$this->ajaxview->set_value("sections", $this->db->get_sections_by_survey_id($this->data['fk_id_survey']));
		$this->ajaxview->set_value("fk_id_survey", $this->data['fk_id_survey']);
		$this->return['content'] = $this->ajaxview->render();
	}
	
	private function delete_section() {
		$section = $this->db->get_section_by_id($this->data['id']);
		$this->db->delete_section($this->data['id']);
		$this->ajaxview->activate("sections");
		$this->ajaxview->set_value("sections", $this->db->get_sections_by_survey_id($section[0]['fk_id_survey']));
		$this->ajaxview->set_value("fk_id_survey", $section[0]['fk_id_survey']);
		$this->return['content'] = $this->ajaxview->render();
	}
	
	private function edit_section() {
		$section = $this->db->get_section_by_id($this->data['id']);
		$this->ajaxview->activate("section_form");
		$this->ajaxview->set_value("id", $section[0]['id']);
		$this->ajaxview->set_value("fk_id_survey", $section[0]['fk_id_survey']);
		$this->ajaxview->set_value("fk_id_user", $section[0]['fk_id_user']);
		$this->ajaxview->set_value("name", $section[0]['name']);
		$this->ajaxview->set_value("title", $section[0]['title']);
		$this->ajaxview->set_value("description", $section[0]['description']);
		$this->ajaxview->set_value("comment", $section[0]['comment']);
		$this->ajaxview->set_value("type", $section[0]['type']);
		$this->ajaxview->set_value("position", $section[0]['position']);
		$this->ajaxview->set_value("grading", $section[0]['grading']);
		$this->ajaxview->set_value("lastchange", $section[0]['lastchange']);
		$this->return['content'] = $this->ajaxview->render();
	}
	

	private function get_questions() {
		$this->ajaxview->activate("questions");
		$this->ajaxview->set_value("questions", $this->db->get_questions_by_section_id($this->data['fk_id_section']));
		$this->ajaxview->set_value("fk_id_section", $this->data['fk_id_section']);
		$this->return['content'] = $this->ajaxview->render();
	}
	
	private function get_question_form() {
		$this->ajaxview->activate("question_form");
		$this->ajaxview->set_value("fk_id_section", $this->data['fk_id_section']);
		$this->return['content'] = $this->ajaxview->render();
	}

	private function save_question() {
		if ($this->data['id'] != "") {
			$this->db->update_question(
						$this->data['id'],
						$this->data['fk_id_section'],
						$_SESSION['userID'], 
						$this->data['name'],
						$this->data['description'],
						$this->data['type'],
						$this->data['showname'],
						$this->data['position'],
						$this->surveylib->get_timestamp()
					);
		} else {
			$this->db->add_question(
						$this->data['fk_id_section'],
						$_SESSION['userID'], 
						$this->data['name'],
						$this->data['description'],
						$this->data['type'],
						$this->data['showname'],
						$this->data['position'],
						$this->surveylib->get_timestamp()
					);
		}
		$this->ajaxview->activate("questions");
		$this->ajaxview->set_value("questions", $this->db->get_questions_by_section_id($this->data['fk_id_section']));
		$this->ajaxview->set_value("fk_id_section", $this->data['fk_id_section']);
		$this->return['content'] = $this->ajaxview->render();
	}
	
	private function delete_question() {
		$question = $this->db->get_question_by_id($this->data['id']);
		$this->db->delete_question($this->data['id']);
		$this->ajaxview->activate("questions");
		$this->ajaxview->set_value("questions", $this->db->get_questions_by_section_id($question[0]['fk_id_section']));
		$this->ajaxview->set_value("fk_id_section", $question[0]['fk_id_section']);
		$this->return['content'] = $this->ajaxview->render();
	}
	
	private function edit_question() {
		$question = $this->db->get_question_by_id($this->data['id']);
		$this->ajaxview->activate("question_form");
		$this->ajaxview->set_value("id", $question[0]['id']);
		$this->ajaxview->set_value("fk_id_section", $question[0]['fk_id_section']);
		$this->ajaxview->set_value("fk_id_user", $question[0]['fk_id_user']);
		$this->ajaxview->set_value("name", $question[0]['name']);
		$this->ajaxview->set_value("description", $question[0]['description']);
		$this->ajaxview->set_value("type", $question[0]['type']);
		$this->ajaxview->set_value("showname", $question[0]['showname']);
		$this->ajaxview->set_value("position", $question[0]['position']);
		$this->ajaxview->set_value("lastchange", $question[0]['lastchange']);
		$this->return['content'] = $this->ajaxview->render();
	}
	
	
	private function get_answers() {
		$this->ajaxview->activate("answers");
		$this->ajaxview->set_value("answers", $this->db->get_answers_by_question_id($this->data['fk_id_question']));
		$this->ajaxview->set_value("fk_id_question", $this->data['fk_id_question']);
		$this->return['content'] = $this->ajaxview->render();
	}
	
	private function get_answer_form() {
		$this->ajaxview->activate("answer_form");
		$this->ajaxview->set_value("fk_id_question", $this->data['fk_id_question']);
		$this->return['content'] = $this->ajaxview->render();
	}

	private function save_answer() {
		if ($this->data['id'] != "") {
			$this->db->update_answer(
						$this->data['id'],
						$this->data['fk_id_question'],
						$_SESSION['userID'], 
						$this->data['name'],
						$this->data['description'],
						$this->data['preselection'],
						$this->data['show_name'],
						$this->data['plus_text'],
						$this->data['plus_text_caption'],
						$this->data['position'],
						$this->surveylib->get_timestamp()
					);
		} else {
			$this->db->add_answer(
						$this->data['fk_id_question'],
						$_SESSION['userID'], 
						$this->data['name'],
						$this->data['description'],
						$this->data['preselection'],
						$this->data['show_name'],
						$this->data['plus_text'],
						$this->data['plus_text_caption'],
						$this->data['position'],
						$this->surveylib->get_timestamp()
					);
		}
		$this->ajaxview->activate("answers");
		$this->ajaxview->set_value("answers", $this->db->get_answers_by_question_id($this->data['fk_id_question']));
		$this->ajaxview->set_value("fk_id_question", $this->data['fk_id_question']);
		$this->return['content'] = $this->ajaxview->render();
	}
	
	private function delete_answer() {
		$answer = $this->db->get_answer_by_id($this->data['id']);
		$this->db->delete_answer($this->data['id']);
		$this->ajaxview->activate("answers");
		$this->ajaxview->set_value("answers", $this->db->get_answers_by_question_id($answer[0]['fk_id_question']));
		$this->ajaxview->set_value("fk_id_question", $answer[0]['fk_id_question']);
		$this->return['content'] = $this->ajaxview->render();
	}
	
	private function edit_answer() {
		$answer = $this->db->get_answer_by_id($this->data['id']);
		$this->ajaxview->activate("answer_form");
		$this->ajaxview->set_value("id", $answer[0]['id']);
		$this->ajaxview->set_value("fk_id_question", $answer[0]['fk_id_question']);
		$this->ajaxview->set_value("fk_id_user", $answer[0]['fk_id_user']);
		$this->ajaxview->set_value("name", $answer[0]['name']);
		$this->ajaxview->set_value("description", $answer[0]['description']);
		$this->ajaxview->set_value("preselection", $answer[0]['preselection']);
		$this->ajaxview->set_value("show_name", $answer[0]['show_name']);
		$this->ajaxview->set_value("plus_text", $answer[0]['plus_text']);
		$this->ajaxview->set_value("plus_text_caption", $answer[0]['plus_text_caption']);
		$this->ajaxview->set_value("position", $answer[0]['position']);
		$this->ajaxview->set_value("lastchange", $answer[0]['lastchange']);
		$this->return['content'] = $this->ajaxview->render();
	}
	
	private function add_default_answers() {
		$this->ajaxview->activate("answers");
		$this->surveylib->add_default_answers($this->data['fk_id_question']);
		$this->ajaxview->set_value("answers", $this->db->get_answers_by_question_id($this->data['fk_id_question']));
		$this->ajaxview->set_value("fk_id_question", $this->data['fk_id_question']);
		$this->return['content'] = $this->ajaxview->render();
	}
	
	private function show_survey() {
		$questions = array();
		$answers = array();
		$this->ajaxview->activate("show_survey");
		$sections = $this->db->get_sections_by_survey_id($this->data['id']);
//		echo($this->data['id']);
//		var_dump($sections);
		foreach ($sections as $s) {
			$questions[$s['id']] = $this->db->get_questions_by_section_id($s['id']);
			foreach ($questions[$s['id']] as $q) {
				$answers[$q['id']] = $this->db->get_answers_by_question_id($q['id']);
			}
		}
		$this->ajaxview->set_value("sections", $sections);
		$this->ajaxview->set_value("questions", $questions);
		$this->ajaxview->set_value("answers", $answers);
		$this->return['content'] = $this->ajaxview->render();
	}
	
	
	
		
	
}

?>