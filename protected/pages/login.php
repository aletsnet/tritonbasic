<?php
class login extends TPage
{
    public function onInit($param){
        $this->lProyecto->Text    = $this->Application->Parameters["lproyect"];
        $this->title              = $this->Application->Parameters["lproyect"];
        $this->MasterClass        = "Application.layouts.ModalLayout";
        $this->lImgUser->ImageUrl = $this->Application->Parameters["imgProyect"];
        $this->lTema->Text        = '<h3 class="profile-username text-center">'.$this->Application->Parameters["proyect"].'</h3>';
        
	}
    
    public function onLoad($param)
    {   
        if(!$this->IsPostBack)
        {
            $this->lnkRecordar->NavigateUrl = $this->Service->constructUrl("public.recuperar");
        }
    }
    
    /**
     * Validates whether the username and password are correct.
     * This method responds to the TCustomValidator's OnServerValidate event.
     * @param mixed event sender
     * @param mixed event parameter
     */
    public function validateUser($sender,$param)
    {
        $authManager=$this->Application->getModule('auth');
        if(!$authManager->login($this->Username->Text,$this->Password->Text)){
            $param->IsValid=false;  // tell the validator that validation fails
        }else{
            $row = LBsUsuarios::finder()->find("borrado = 0 AND user = ? AND pass = ?", $this->Username->Text,sha1($this->Password->Text));
            if(!$row instanceof LBsUsuarios){
                $param->IsValid=false;
            }
        }
    }
 
    /**
     * Redirects the user's browser to appropriate URL if login succeeds.
     * This method responds to the login button's OnClick event.
     * @param mixed event sender
     * @param mixed event parameter
     */
    public function loginButtonClicked($sender,$param)
    {
        if($this->IsValid)  // all validations succeed
        {
            //$cookie=new THttpCookie("tipo_acceso",$this->cmdAccesos->Text);
            //$this->Response->Cookies[]=$cookie;
            $this->Response->redirect($this->Service->constructUrl("sistema.menu"));
            
            
            
        }else{
            //$this->User->bitacora("usuario",1,"acceso denegado:".$this->Username->Text);
            //$this->captcha->text = "";
        }
    }
}