<?php
class recovery extends TPage
{
    public function onLoad($param)
    {   
        if(!$this->IsPostBack)
        {
			if($this->request['ref'] != ""){
				$row = LBsUsuarios::finder()->find(" cadena_referencia = ?", $this->request['ref']);
				if($row instanceof LBsUsuarios){
					$this->txtNewPassword->Text = "";
					$this->txtPasswordOtro->Text = "";
					
					$this->Formulario->visible = true;
					$this->Respuesta->visible  = false;
				}else{
					$this->Formulario->visible = false;
					$this->Respuesta->visible  = true;
					$this->lRespuesta->Text = '<label class="label bg-red"><i class="fa fa-close"></i></label> No se reconoce la referencia';
				}
			}
        }
    }
	
	public function Validador_txtPassword($sender, $param){
		$valido = ($this->txtNewPassword->text == $this->txtPasswordOtro->Text);
		$param->IsValid = $valido;
	}
	
	public function BtnGuardar_click($sender, $param){
		if($this->IsValid){
			$row = LBsUsuarios::finder()->find(" cadena_referencia = ?", $this->request['ref']);
			if($row instanceof LBsUsuarios){
				
				$referencia = rand(100000,999999);
				$referencia2 = rand(100000,999999);
				$referencia = sha1($referencia);
				$referencia2 = sha1($referencia);
				$referencia = $referencia.$referencia2;
				
				$row->cadena_referencia = $referencia;
				$row->fecha_actualizado = date("Y-m-d H:i:s");
				$row->pass              = sha1($this->txtNewPassword->Text);
				$row->save();
				
				$this->Formulario->visible = false;
				$this->Respuesta->visible  = true;
				$this->lRespuesta->Text = '<label class="label bg-green"><i class="fa fa-check"></i></label> Se actualizó correctamente, <a href="http://proyectos.megatec.mx/gestion">Click para continuar</a>';
			}else{
				$this->Formulario->visible = false;
				$this->Respuesta->visible  = true;
				$this->lRespuesta->Text = '<label class="label bg-red"><i class="fa fa-close"></i></label> No se reconoce la referencia';
			}
		}
	}
}