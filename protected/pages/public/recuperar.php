<?php
class recuperar extends TPage
{
    public function onLoad($param)
    {   
        if(!$this->IsPostBack)
        {
            //$this->lnkRecordar->NavigateUrl = $this->Service->constructUrl("public.recuperar");
			$this->Formulario->Visible = true;
			$this->Respuesta->Visible  = false;
        }
    }
	
	function BtnEnviar_click($sender, $param){
		if($this->IsValid){
			$row = LBsUsuarios::finder()->find(" correo = ?", $this->txtEmail->Text);
			if($row instanceof LBsUsuarios){
				$referencia = rand(100000,999999);
				$referencia2 = rand(100000,999999);
				$referencia = sha1($referencia);
				$referencia2 = sha1($referencia);
				$referencia = $referencia.$referencia2;
				
				$lnk = "".$this->Application->Parameters["urlProyecto"].$this->Service->constructUrl("public.recovery", array("ref" => $referencia));
				
				$row->cadena_referencia = $referencia;
				$row->save();
				
				$email = $row->correo;
				$nombre = $row->user;
				
				//$message = file_get_contents('protected/pages/public/email.html');
				
				//use PHPMailer\PHPMailer;
				//use PHPMailer\Exception;
				//require 'PHPMailerAutoload.php';
				$mail = new PHPMailer\PHPMailer\PHPMailer(true);                              // Passing `true` enables exceptions
				try {
					//Server settings
					//$mail->isSMTP();
					$mail->SMTPDebug = 0;
					$mail->Host = 'mail.prihidalgo.mx';
					$mail->Port = 587;
					$mail->SMTPAuth = true;
					$mail->Username = 'info@prihidalgo.mx';
					$mail->Password = '1nf0prihidalgo';
					$mail->SMTPSecure = 'tls';
				
					//Recipients
					$mail->setFrom('info@prihidalgo.mx', 'prihidalgo.mx');
					//$mail->Subject = 'PHPMailer SMTP test';
					$mail->addAddress($email, $nombre);
					//Content
					$mail->isHTML(true);                                  // Set email format to HTML
					$mail->Subject = utf8_decode("Recuperar contraseña");
					$mail->Body    = $this->Application->Parameters["proyecto"].' <a href="'.$lnk.'"> click aquí para cambiar contraseña </a> ';
					$mail->AltBody = 'url aletrnativo: <a href="'.$lnk.'">'.$lnk.'</a>';
					//$mail->Body = $message;
					//$mail->AltBody = strip_tags($message);
				
					if (!$mail->send()) {
						//echo 'Mailer Error: ' . $mail->ErrorInfo;
						Prado::log("Error al enviar: ".$mail->ErrorInfo,TLogger::NOTICE,$this->PagePath);
					} else {
						Prado::log("Correo de recuperación de contraseña enviado",TLogger::NOTICE,$this->PagePath);
					}
					
				} catch (Exception $e) {
					Prado::log("Error al enviar el correo de recuperación de contraseña",TLogger::NOTICE,$this->PagePath);
					Prado::log($e,TLogger::NOTICE,$this->PagePath);
				}
				
				
				//$this->lRespuesta->Text = $referencia;
				$this->lRespuesta->Text = '<label class="label bg-green"><i class="fa fa-check"></i> </label> E-mail enviado, revice su cuenta de correo (rebice la bandeja de spam)';
			}else{
				$this->lRespuesta->Text = '<label class="label bg-red"><i class="fa fa-close"></i></label> E-mail no encontrado';
			}
			$this->Formulario->Visible = false;
			$this->Respuesta->Visible  = true;
		}
	}
}