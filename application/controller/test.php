<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Test page
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum 11.02.2014
 * @link		http://www.adrodev.de
 */
class Test extends Page {
	
	private $email_receiver;
	private $mail;
	private $tmail;
	private $mailview;
	
	protected function retrieve_data() {
		$this->email_receiver = $this->request("email_receiver");
	}
	
	protected function run_page_logic() {
		$this->view->add_template("header.tpl");
		$this->view->add_template("test.tpl");
		$this->view->add_template("footer.tpl");
		$this->view->set_value("email_receiver", $this->email_receiver);
		
		if ($this->email_receiver != "") {
			$this->mailview = new View();
			$this->mailview->set_template(1, "mail.tpl");
			
//			$this->mail = new HTMLMail(HTMLMail::MAIL_TYPE_HTML);
//			$this->mail->set_transfer_type(HTMLMail::TRANS_TYPE_SMTP);
//			$this->mail->set_smtp_access(SMTP_SERVER, SMTP_USER, SMTP_PASS);
//			$this->mail->set_html_body($this->mailview->render());
//			$this->mail->set_sender("Absender", "dynamitexxl@gmx.de");
//			$this->mail->set_receiver("Empf�nger", $this->email_receiver);
//			$this->mail->set_subject("Test-Mail im HTML-Format");
//			$this->mail->add_attachment("/var/www/2011-57032 NKHR Folge1.pdf");
//			$this->mail->add_attachment("/var/www/2011-57033 NKHR Folge 2.pdf");
//			$this->mail->add_inline("/var/www/php_logo.jpg");
//			$this->mail->send();
			
//			$this->tmail = new HTMLMail(HTMLMail::MAIL_TYPE_TEXT);
//			$this->tmail->set_transfer_type(HTMLMail::TRANS_TYPE_SMTP);
//			$this->tmail->set_smtp_access(SMTP_SERVER, SMTP_USER, SMTP_PASS);
//			$this->tmail->set_text_body($this->mailview->render());
//			$this->tmail->set_sender("Absender", "dynamitexxl@gmx.de");
//			$this->tmail->set_receiver("Empf�nger", $this->email_receiver);
//			$this->tmail->set_subject("Test-Mail im Text-Format");
//			$this->tmail->add_attachment("/var/www/libcompactmvc/index.php");
//			$this->tmail->add_inline("/var/www/php_logo.jpg");
//			$this->tmail->send();
			
//			$this->mailview->set_template(1, "mail2.tpl");
			
//			$m1 = new HTMLMail(HTMLMail::MAIL_TYPE_HTML);
//			$m1->set_html_body($this->mailview->render());
//			$m1->set_sender("dynamitexxl@gmx.de", "Absender");
//			$m1->set_receiver($this->email_receiver, "Empf�nger");
//			$m1->set_subject("Test-Mail via mail() ������");
////			$m1->add_attachment("./files/2011-57032 NKHR Folge1.pdf");
////			$m1->add_attachment("./files/2011-57033 NKHR Folge 2.pdf");
//			$m1->add_inline("./files/php_logo.jpg");
//			$m1->add_inline("http://michaelhanley.ie/elearningcurve/wp-content/uploads/2009/12/php_logo_thumb.jpg");
//			try {
//				$m1->send();
//			} catch (Exception $e) {
//				echo("<pre>".$e->getTraceAsString());
//			}
			
			$m1 = new HTMLMail(HTMLMail::MAIL_TYPE_HTML);
			$m1->set_transfer_type(HTMLMail::TRANS_TYPE_MAIL);
			$m1->set_smtp_access("localhost", "", "");
			$m1->set_html_body($this->mailview->render());
			$m1->set_sender("dynamitexxl@gmx.de", "Absender");
			$m1->set_receiver($this->email_receiver, "Empfänger");
			$m1->add_cc("dynamitexxl@gmx.de", "DynamiteXXL");
			$m1->add_cc("bhohbaum@googlemail.com", "bhohbaum");
			$m1->add_bcc("dynamitexxl@hotmail.com", "DynamiteXXL - Hotmail");
			$m1->set_subject("Test-Mail via mail() öäüÖÄÜ");
//			$m1->add_attachment("./files/2011-57032 NKHR Folge1.pdf");
//			$m1->add_attachment("./files/2011-57033 NKHR Folge 2.pdf");
			$m1->add_inline("./files/php_logo.jpg");
			$m1->add_inline("http://michaelhanley.ie/elearningcurve/wp-content/uploads/2009/12/php_logo_thumb.jpg");
			try {
				$m1->send();
			} catch (Exception $e) {
				echo("<pre>".$e->getTraceAsString());
			}
						
			
			$m2 = new HTMLMail(HTMLMail::MAIL_TYPE_HTML);
			$m2->set_transfer_type(HTMLMail::TRANS_TYPE_SMTP);
			$m2->set_smtp_access("localhost", "", "");
			$m2->set_html_body($this->mailview->render());
			$m2->set_sender("dynamitexxl@gmx.de", "Absender");
			$m2->set_receiver($this->email_receiver, "Empfänger");
			$m2->add_cc("dynamitexxl@gmx.de", "DynamiteXXL");
			$m2->add_cc("bhohbaum@googlemail.com", "bhohbaum");
			$m2->add_bcc("dynamitexxl@hotmail.com", "DynamiteXXL - Hotmail");
			$m2->set_subject("Test-Mail via local SMTP öäüÖÄÜ");
//			$m2->add_attachment("./files/2011-57032 NKHR Folge1.pdf");
//			$m2->add_attachment("./files/2011-57033 NKHR Folge 2.pdf");
			$m2->add_inline("./files/php_logo.jpg");
			$m2->add_inline("http://michaelhanley.ie/elearningcurve/wp-content/uploads/2009/12/php_logo_thumb.jpg");
			try {
				$m2->send();
			} catch (Exception $e) {
				echo("<pre>".$e->getTraceAsString());
			}
						
			
			
		}
	}
	

	
}

?>