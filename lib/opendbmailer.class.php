<?php
/* 	
 	Open Media Collectors Database
	Copyright (C) 2001,2013 by Jason Pell

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/
require_once ('./lib/phpmailer/class.phpmailer.php');

include_once("./lib/config.php");
include_once("./lib/logging.php");

/**
	If set to email.mailer = 'mail', then must provide extra details:
		email.smtp.host
        email.smtp.port
		email.smtp.username
		email.smtp.password
*/
class OpenDbMailer extends PHPMailer {

	function __construct($mailer) {
		$this->PluginDir = './lib/phpmailer/';
		
		if (get_opendb_config_var ( 'email', 'windows_smtp_server' ) === TRUE) {
			$this->LE = "\r\n";
		} else {
			$this->LE = "\n";
		}
		
		$this->Mailer = $mailer;
		$this->Priority = "3"; // in case we want to change it
		$this->Sender = get_opendb_config_var ( 'email', 'noreply_address' );
		
		if ($this->Mailer == 'smtp') {
			$email_smtp_r = get_opendb_config_var ( 'email.smtp' );
			
			// at least host should be defined.
			if (is_not_empty_array ( $email_smtp_r ) && strlen ( $email_smtp_r ['host'] ) > 0) {
				$this->Host = $email_smtp_r ['host'];
				
				if (strlen ( $email_smtp_r ['port'] ) > 0)
					$this->Port = $email_smtp_r ['port'];
				
				if ($email_smtp_r ['secure'] != 'none') {
					$this->SMTPSecure = $email_smtp_r ['secure']; // sets the prefix to the server
				}
				
				if (strlen ( $email_smtp_r ['username'] ) > 0 && strlen ( $email_smtp_r ['password'] ) > 0) {
					$this->Username = $email_smtp_r ['username'];
					$this->Password = $email_smtp_r ['password'];
					$this->SMTPAuth = TRUE;
				}
			} else {
				// set to 'mail' mailer as default, and log configuration error.
				opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Email SMTP Configuration missing', array (
						$mailer ) );
				
				// override, because mailer smtp is misconfigured.
				$this->Mailer = 'mail';
			}
		}
	}
}
?>
