<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

// HowTo: http://rand-mh.sourceforge.net/book/overall/mulmes.html

class HTMLMail {

	private $inline;
	private $attachment;
	private $sender_name;
	private $sender_mail;
	private $receiver_name;
	private $receiver_mail;
	private $replyto_name;
	private $replyto_mail;
	private $cc;
	private $bcc;
	private $subject;
	private $htmlbody;
	private $textbody;
	private $boundary_a;
	private $boundary_m;
	private $boundary_r;
	private $mailtype;
	private $transfertype;
	private $smtpserver;
	private $smtpuser;
	private $smtppass;
	private $mailbody;
	
	const MAIL_TYPE_TEXT = 1;
	const MAIL_TYPE_HTML = 2;
	
	const TRANS_TYPE_MAIL = 1;
	const TRANS_TYPE_SMTP = 2;
	
	public function __construct($type = self::MAIL_TYPE_HTML) {
		$this->mailtype = $type;
		$this->transfertype = self::TRANS_TYPE_MAIL;
		$this->htmlbody = "";
		$this->textbody = "";
		$this->inline = array();
		$this->attachment = array();
		$this->cc = array();
		$this->bcc = array();
		$this->boundary_a = md5(time() + mt_rand());
		$this->boundary_m = md5(time() + mt_rand());
		$this->boundary_r = md5(time() + mt_rand());
	}
	
	public function set_mail_type($type) {
		$this->mailtype = $type;
	}
	
	public function set_transfer_type($type) {
		$this->transfertype = $type;
	}
	
	public function set_smtp_access($server, $user = "", $pass = "") {
		$this->smtpserver = $server;
		$this->smtpuser = $user;
		$this->smtppass = $pass;
	}

	public function set_receiver($email, $name = "") {
		$this->receiver_name = UTF8::encode($name);
		$this->receiver_mail = UTF8::encode($email);
	}

	public function set_sender($email, $name = "") {
		$this->sender_name = UTF8::encode($name);
		$this->sender_mail = UTF8::encode($email);
	}

	public function set_reply_to($email, $name = "") {
		$this->replyto_name = UTF8::encode($name);
		$this->replyto_mail = UTF8::encode($email);
	}

	public function add_cc($email, $name = "") {
		mb_internal_encoding('UTF-8');
		$this->cc[] = mb_encode_mimeheader(UTF8::encode($name), "UTF-8", "Q")." <".$email.">";
	}

	public function add_bcc($email, $name = "") {
		mb_internal_encoding('UTF-8');
		$this->bcc[] = mb_encode_mimeheader(UTF8::encode($name), "UTF-8", "Q")." <".$email.">";
	}

	public function set_subject($subject) {
		$this->subject = UTF8::encode($subject);
	}

	public function set_html_body($body) {
		$this->htmlbody = UTF8::encode($body);
	}

	public function set_text_body($body) {
		$this->textbody = UTF8::encode($body);
	}

	public function add_inline($file) {
		$this->inline[] = UTF8::encode($file);
	}

	public function add_attachment($file) {
		$this->attachment[] = UTF8::encode($file);
	}

	public function send() {
		mb_internal_encoding('UTF-8');
		$this->replace_image_tags();
		$this->auto_text_body();
		if ($this->replyto_name == "") {
			$this->replyto_name = $this->sender_name;
		} 
		if ($this->replyto_mail == "") {
			$this->replyto_mail = $this->sender_mail;
		} 
		$this->assemble_mail();
		switch ($this->transfertype) {
			case self::TRANS_TYPE_MAIL :
				if (!(mail(mb_encode_mimeheader($this->receiver_name, "UTF-8", "Q").' <'.$this->receiver_mail.">",
						mb_encode_mimeheader($this->subject, "UTF-8", "Q"),
						$this->mailbody,
						$this->mailheader,
						'-f'.$this->sender_mail))) {
					throw new Exception("An error occurred. Function mail() returned false.");
				}
				break;
			case self::TRANS_TYPE_SMTP :
				$smtp = new SMTP($this->smtpserver);
				$smtp->set_login($this->smtpuser, $this->smtppass);
				$smtp->set_mail($this->sender_mail, $this->receiver_mail, $this->mailheader.$this->mailbody);
				$smtp->send();
				foreach ($this->cc as $receiver) {
					$tmp = strip_tags($receiver);
					$receiver = str_replace($tmp, "", $receiver);
					$receiver = str_replace("<", "", $receiver);
					$receiver = str_replace(">", "", $receiver);
					$smtp->set_mail($this->sender_mail, $receiver, $this->mailheader.$this->mailbody);
					$smtp->send();
				}
				foreach ($this->bcc as $receiver) {
					$tmp = strip_tags($receiver);
					$receiver = str_replace($tmp, "", $receiver);
					$receiver = str_replace("<", "", $receiver);
					$receiver = str_replace(">", "", $receiver);
					$smtp->set_mail($this->sender_mail, $receiver, $this->mailheader.$this->mailbody);
					$smtp->send();
				}
				break;
		}
	}
	
	private function assemble_mail() {
		mb_internal_encoding('UTF-8');
		$this->mailheader = 'Subject: '.mb_encode_mimeheader($this->subject, "UTF-8", "Q")."\n";
		$this->mailheader .= 'From: '.mb_encode_mimeheader($this->sender_name, "UTF-8", "Q").' <'.$this->sender_mail.">\n";
		if ($this->transfertype != self::TRANS_TYPE_MAIL) {
			$this->mailheader .= 'To: '.mb_encode_mimeheader($this->receiver_name, "UTF-8", "Q").' <'.$this->receiver_mail.">\n";
		}
		$this->mailheader .= 'Reply-To: '.mb_encode_mimeheader($this->replyto_name, "UTF-8", "Q").' <'.$this->replyto_mail.">\n";
		$this->mailheader .= 'CC: '.implode(', ', $this->cc)."\n";
		$this->mailheader .= 'BCC: '.implode(', ', $this->bcc)."\n";
		$this->mailheader .= 'Content-Type: multipart/mixed; boundary="'.$this->boundary_m.'"'."\n";
		$this->mailheader .= 'MIME-Version: 1.0'."\n";
		$this->mailheader .= 'X-Mailer: LibHTMLMail (c) 2011 by HTML Design, Stuttgart, Germany.'."\n"; 
		$this->mailheader .= "\n\n";
		
		$this->mailbody = "--".$this->boundary_m."\n";
		$this->mailbody .= 'Content-Type: multipart/related; type="multipart/alternative"; boundary="'.$this->boundary_r.'"'."\n";
		$this->mailbody .= "\n\n";
		$this->mailbody .= "--".$this->boundary_r."\n";
		$this->mailbody .= 'Content-Type: multipart/alternative; boundary="'.$this->boundary_a.'"'."\n";
		$this->mailbody .= "\n\n";
		$this->mailbody .= "--".$this->boundary_a."\n";
		$this->mailbody .= 'Content-Type: text/plain; charset="utf-8"'."\n";
		$this->mailbody .= "Content-Transfer-Encoding: 8bit\n\n";
		$this->mailbody .= $this->textbody;
		$this->mailbody .= "\n\n";
		if ($this->mailtype == self::MAIL_TYPE_HTML) {
			$this->mailbody .= "--".$this->boundary_a."\n";
			$this->mailbody .= 'Content-Type: text/html; charset="utf-8"'."\n";
			$this->mailbody .= "Content-Transfer-Encoding: 8bit\n\n";
			$this->mailbody .= $this->htmlbody;
			$this->mailbody .= "\n\n";
		}
		$this->mailbody .= "--".$this->boundary_a."--\n\n";
		if (count($this->inline) > 0) {
			foreach ($this->inline as $i) {
				$fcont = "";
				if (strtoupper(substr($i, 0, 4)) == "HTTP") {
					$curl = curl_init($i);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					$fcont = curl_exec($curl);
				} else {
					$fcont = file_get_contents($i);
				}
				$fcont = base64_encode($fcont);
				$fcont = chunk_split($fcont, 76, "\n");
				$bn = basename($i);
				$tmparr = explode('?', $bn);
				$bname = array_shift($tmparr);
				$farr = explode('.', $bname);
				$fext = end($farr);
				$this->mailbody .= "--".$this->boundary_r."\n";
				$this->mailbody .= 'Content-ID: <'.$bname.'>'."\n";
				$this->mailbody .= 'Content-Disposition: inline; filename="'.$bname.'"'."\n";
				$this->mailbody .= 'Content-Type: '.$this->mime_type($fext).'; name="'.$bname.'"'."\n";
				$this->mailbody .= "Content-Transfer-Encoding: base64\n\n";
				$this->mailbody .= $fcont;
				$this->mailbody .= "\n\n";
			}
		}
		$this->mailbody .= "--".$this->boundary_r."--\n\n";
		if (count($this->attachment) > 0) {
			foreach ($this->attachment as $a) {
				$fcont = "";
				if (strtoupper(substr($a, 0, 4)) == "HTTP") {
					$curl = curl_init($a);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					$fcont = curl_exec($curl);
				} else {
					$fcont = file_get_contents($a);
				}
				$fcont = base64_encode($fcont);
				$fcont = chunk_split($fcont, 76, "\n");
				$bn = basename($a);
				$tmparr = explode('?', $bn);
				$bname = array_shift($tmparr);
				$farr = explode('.', $bname);
				$fext = end($farr);
				$this->mailbody .= "--".$this->boundary_m."\n";
				$this->mailbody .= 'Content-Type: '.$this->mime_type($fext).'; name="'.$bname.'"'."\n";
				$this->mailbody .= 'Content-Disposition: attachment; filename="'.$bname.'"'."\n";
				$this->mailbody .= "Content-Transfer-Encoding: base64\n\n";
				$this->mailbody .= $fcont;
				$this->mailbody .= "\n\n";
			}
		}
		$this->mailbody .= "--".$this->boundary_m."--\n\n";
	}
	
	private function auto_text_body() {
		if ($this->mailtype == self::MAIL_TYPE_HTML) {
			if ($this->textbody == "") {
				$this->textbody = str_replace("\r", "", $this->htmlbody);
				$this->textbody = str_replace("\n", "", $this->textbody);
				$this->textbody = preg_replace('/<br(\s+)?\/?>/i', "\n\n", $this->textbody);
				$this->textbody = str_replace("\n\n\n\n", "\n\n\n", $this->textbody);
				$this->textbody = wordwrap(html_entity_decode(strip_tags($this->textbody)));
				$this->textbody = UTF8::encode($this->textbody);
			}
		}
	}
	
	private function replace_image_tags() {
		if (count($this->inline) > 0) {
			foreach ($this->inline as $i) {
				$bn = basename($i);
				$tmparr = explode('?', $bn);
				$bname = array_shift($tmparr);
				$this->htmlbody = str_replace($i, 'cid:'.$bname, $this->htmlbody);
			}
		}
	}

	private function mime_type($ext = '') {
		$mimes = array(
			'hqx'   =>  'application/mac-binhex40',
			'cpt'   =>  'application/mac-compactpro',
			'doc'   =>  'application/msword',
			'bin'   =>  'application/macbinary',
			'dms'   =>  'application/octet-stream',
			'lha'   =>  'application/octet-stream',
			'lzh'   =>  'application/octet-stream',
			'exe'   =>  'application/octet-stream',
			'class' =>  'application/octet-stream',
			'psd'   =>  'application/octet-stream',
			'so'    =>  'application/octet-stream',
			'sea'   =>  'application/octet-stream',
			'dll'   =>  'application/octet-stream',
			'oda'   =>  'application/oda',
			'pdf'   =>  'application/pdf',
			'ai'    =>  'application/postscript',
			'eps'   =>  'application/postscript',
			'ps'    =>  'application/postscript',
			'smi'   =>  'application/smil',
			'smil'  =>  'application/smil',
			'mif'   =>  'application/vnd.mif',
			'xls'   =>  'application/vnd.ms-excel',
			'ppt'   =>  'application/vnd.ms-powerpoint',
			'wbxml' =>  'application/vnd.wap.wbxml',
			'wmlc'  =>  'application/vnd.wap.wmlc',
			'dcr'   =>  'application/x-director',
			'dir'   =>  'application/x-director',
			'dxr'   =>  'application/x-director',
			'dvi'   =>  'application/x-dvi',
			'gtar'  =>  'application/x-gtar',
			'php'   =>  'application/x-httpd-php',
			'php4'  =>  'application/x-httpd-php',
			'php3'  =>  'application/x-httpd-php',
			'phtml' =>  'application/x-httpd-php',
			'phps'  =>  'application/x-httpd-php-source',
			'js'    =>  'application/x-javascript',
			'swf'   =>  'application/x-shockwave-flash',
			'sit'   =>  'application/x-stuffit',
			'tar'   =>  'application/x-tar',
			'tgz'   =>  'application/x-tar',
			'xhtml' =>  'application/xhtml+xml',
			'xht'   =>  'application/xhtml+xml',
			'zip'   =>  'application/zip',
			'mid'   =>  'audio/midi',
			'midi'  =>  'audio/midi',
			'mpga'  =>  'audio/mpeg',
			'mp2'   =>  'audio/mpeg',
			'mp3'   =>  'audio/mpeg',
			'aif'   =>  'audio/x-aiff',
			'aiff'  =>  'audio/x-aiff',
			'aifc'  =>  'audio/x-aiff',
			'ram'   =>  'audio/x-pn-realaudio',
			'rm'    =>  'audio/x-pn-realaudio',
			'rpm'   =>  'audio/x-pn-realaudio-plugin',
			'ra'    =>  'audio/x-realaudio',
			'rv'    =>  'video/vnd.rn-realvideo',
			'wav'   =>  'audio/x-wav',
			'bmp'   =>  'image/bmp',
			'gif'   =>  'image/gif',
			'jpeg'  =>  'image/jpeg',
			'jpg'   =>  'image/jpeg',
			'jpe'   =>  'image/jpeg',
			'png'   =>  'image/png',
			'tiff'  =>  'image/tiff',
			'tif'   =>  'image/tiff',
			'css'   =>  'text/css',
			'html'  =>  'text/html',
			'htm'   =>  'text/html',
			'shtml' =>  'text/html',
			'txt'   =>  'text/plain',
			'text'  =>  'text/plain',
			'log'   =>  'text/plain',
			'rtx'   =>  'text/richtext',
			'rtf'   =>  'text/rtf',
			'xml'   =>  'text/xml',
			'xsl'   =>  'text/xml',
			'mpeg'  =>  'video/mpeg',
			'mpg'   =>  'video/mpeg',
			'mpe'   =>  'video/mpeg',
			'qt'    =>  'video/quicktime',
			'mov'   =>  'video/quicktime',
			'avi'   =>  'video/x-msvideo',
			'movie' =>  'video/x-sgi-movie',
			'doc'   =>  'application/msword',
			'word'  =>  'application/msword',
			'xl'    =>  'application/excel',
			'eml'   =>  'message/rfc822');
		return (!isset($mimes[strtolower($ext)])) ? 'application/octet-stream' : $mimes[strtolower($ext)];
	}


}



?>