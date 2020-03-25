<?php
!defined('IN_HDWIKI') && exit('Access Denied');
require HDWIKI_ROOT.'/lib/PHPMailer-5.2.28/PHPMailerAutoload.php';

$maildelimiter = $mail_setting['maildelimiter'] == 1 ? "\r\n" : ($mail_setting['maildelimiter'] == 2 ? "\r" : "\n");
$mailusername = isset($mail_setting['mailusername']) ? $mail_setting['mailusername'] : 1;
$sitename = $this->base->setting['site_name'];
$mail['subject'] = '=?'.WIKI_CHARSET.'?B?'.base64_encode(str_replace("\r", '', str_replace("\n", '', $mail['subject']))).'?=';
$mail['message'] = chunk_split(base64_encode(str_replace("\r\n.", " \r\n..", str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $mail['message'])))))));

$email_from = $mail['frommail'] == '' ? '=?'.WIKI_CHARSET.'?B?'.base64_encode($sitename)."?= <{$mail_setting['maildefault']}>" : (preg_match('/^(.+?) \<(.+?)\>$/',$email_from, $from) ? '=?'.WIKI_CHARSET.'?B?'.base64_encode($from[1])."?= <{$from[2]}>" : $mail['frommail']);

foreach(explode(',', $mail['email_to']) as $touser) {
	$tousers[] = preg_match('/^(.+?) \<(.+?)\>$/',$touser, $to) ? ($mailusername ? '=?'.WIKI_CHARSET.'?B?'.base64_encode($to[1])."?= <$to[2]>" : $to[2]) : $touser;
}

$mail['email_to'] = implode(',', $tousers);

$headers = "From: $email_from{$maildelimiter}X-Priority: 3{$maildelimiter}X-Mailer: HDwiki ".HDWIKI_VERSION."{$maildelimiter}MIME-Version: 1.0{$maildelimiter}Content-type: text/".($mail['html'] ? 'html' : 'plain')."; charset=".WIKI_CHARSET."{$maildelimiter}Content-Transfer-Encoding: base64{$maildelimiter}";

$mail_setting['mailport'] = $mail_setting['mailport'] ? $mail_setting['mailport'] : 25;

if($mail_setting['mailsend'] == 1 && function_exists('mail')) {

	 return @mail($mail['email_to'], $mail['subject'], $mail['message'], $headers);

} elseif($mail_setting['mailsend'] == 2) {

	// Instantiation and passing `true` enables exceptions
	$mailer = new PHPMailer(true);
	
	try {
		//Server settings
		$mailer->setLanguage('zh-cn', '/lib/PHPMailer-5.2.28/language');
		$mailer->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
		$mailer->isSMTP();                                            // Send using SMTP
		$mailer->Host       = $mail_setting['mailserver'];                    // Set the SMTP server to send through
		
		if($mail_setting['mailauth']){
			$mailer->SMTPAuth   = true;                                   // Enable SMTP authentication
			$mailer->Username   = $mail_setting['mailauth_username'];                     // SMTP username
			$mailer->Password   = $mail_setting['mailauth_password'];                               // SMTP password
			$mailer->Port       = $mail_setting['mailport'];                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
		}else{
			$mailer->SMTPAuth   = false;
		}
		
		//是否启用SSL
		if($mail_setting['ssl']){
			$mailer->SMTPSecure = 'ssl';
		}
	
		//Recipients
		$mailer->setFrom($mail_setting['mailfrom'],$sitename);
		$mailer->addAddress($mail['email_to']);     // Add a recipient
		//$mailer->addAddress('ellen@example.com');               // Name is optional
		//$mailer->addReplyTo('info@example.com', 'Information');
		//抄送
		$mailer->addCC($mail_setting['mailfrom'],$sitename);
		//$mailer->addBCC('bcc@example.com');
	
		// Attachments
		//$mailer->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
		//$mailer->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
	
		// Content
		$mailer->isHTML(true);                                  // Set email format to HTML
		$mailer->Subject = $mail['subject'];
		$mailer->Body    = $mail['message'];
		//$mailer->AltBody = 'This is the body in plain text for non-HTML mail clients';
	
		$mailer->send();
		echo 'Message has been sent';
		return true;
	} catch (Exception $e) {
		echo "Message could not be sent. Mailer Error: {$mailer->ErrorInfo}";
		return false
	}
	

} elseif($mail_setting['mailsend'] == 3) {

	ini_set('SMTP', $mail_setting['mailserver']);
	ini_set('smtp_port', $mail_setting['mailport']);
	ini_set('sendmail_from', $email_from);

	return @mail($mail['email_to'], $mail['subject'], $mail['message'], $headers);

}

?>