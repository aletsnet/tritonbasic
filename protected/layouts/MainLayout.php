<?php
class MainLayout extends TTemplateControl
{
    public $menu = "";
    
    public function onLoad($param)
    {
		$this->User->Actividad($this->User->idusuarios);
        if(!$this->User->IsGuest)
		{
            //menu lateral
            foreach($this->User->Roles as $indice => $row){ $roles = $row;}
            $modulos = $this->Application->Modules['query']->Client->queryForList("vwMenuLateral",array('id_roles' => $roles, 'id_modulos' => ''));
            
            if(count($modulos) > 0){
                $this->RpModulos->DataSource = $modulos;
                $this->RpModulos->dataBind();
            }
			//Usuario activo            
            $this->tImgUser->ImageUrl      = $this->Application->Parameters["imgProyect"];
            $this->imgUser->ImageUrl       = $this->Application->Parameters["imgProyect"];
            $this->ImgUserPerfil->ImageUrl = $this->Application->Parameters["imgProyect"];
            
            $usuarios = LBsUsuarios::finder()->findByPk($this->User->idusuarios);
            if($usuarios instanceof LBsUsuarios){
                //$this->lFuente->Text = $usuarios->dependencia;
                $this->lFuente->Text = $usuarios->ct_sucursales->sucursal;
                
                if(!$usuarios->activo){
                    $this->logoutButtonClicked(null,null);
                }
                if($usuarios->pass != $this->User->pass ){
                    $this->logoutButtonClicked(null,null);
                }
                
                if($usuarios->foto != ""){
                    $this->tImgUser->ImageUrl = $usuarios->foto;
                    $this->imgUser->ImageUrl =  $usuarios->foto;
					$this->ImgUserPerfil->ImageUrl =  $usuarios->foto;
                }
                
                $this->tnombre->Text = $usuarios->nombre;
                $this->nombreUser->Text = $usuarios->nombre;
				$this->lbPerfilNombre->Text = $usuarios->nombre;
				$this->lbPerfilCargo->Text = $usuarios->cargo;
				$this->lbPerfilAcceso->Text = $usuarios->bs_roles->nombre;
                /*
                $this->cargo->text = $usuarios->cargo;
                $this->nombre->text = $usuarios->nombre;
                $this->cargo2->text = $usuarios->cargo;
                $this->nombre2->text = $usuarios->nombre;
                */
                /*if(!isset($usuarios->iddependecia)){
                 *
                    $this->logoutButtonClicked(null,null);
                }*/
                
                $this->lVersion->Text         = $this->Application->Parameters["version"];
                $this->lAplication->Text      = $this->Application->Parameters["proyect"];
                $this->lProyecto->Text        = $this->Application->Parameters["proyect"];
                $this->lProyect->Text         = $this->Application->Parameters["proyect"];
                $this->lProyecto->NavigateUrl = "http://".$this->Application->Parameters["urlProyecto"];
                $this->lnkAutor->Text         = $this->Application->Parameters["desarrollo"];
                $this->lnkAutor->NavigateUrl  = "#";
                
            }else{
                $this->logoutButtonClicked(null,null);
            }
        }
		if(!$this->getPage()->IsPostBack)
		{
            Prado::log(
				" [".$_SERVER['REMOTE_ADDR']."] [".$this->getPage()->getPagePath() . "] [" . $this->User->Name . "] [" . $this->User->idusuarios . "]",
				TLogger::NOTICE,
				$this->getPage()->getPagePath()
			);
		}
    }
    
    public function repeaterDataBound($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            if($item->Data instanceof LBsModulos){
				//$item->opcion->text = '<i class="'.$item->Data->icon.'"></i><span>'.$item->Data->nombre.' </span>';
				$item->RpServicios->DataSource =  $this->servicios_usuario($item->Data->id_modulos);
				$item->RpServicios->dataBind();
			}
        }
    }
    
    public function logoutButtonClicked($sender,$param)
    {
        $this->Application->getModule('auth')->logout();
        $url=$this->Service->constructUrl($this->Service->DefaultPage);
        $this->Response->redirect($url);
    }
    
	public function servicios_usuario($idmodulos){
		//LBsServicios
		$roles = "";
		foreach($this->User->Roles as $indice => $row){ $roles = ($roles!=""?',':'').$row;}
		$tabla = $this->Application->Modules['query']->Client->queryForList("vwMenuTab",array('id_roles' => $roles, 'id_modulos' => $idmodulos));
		return $tabla;
	}
    
}
?>
