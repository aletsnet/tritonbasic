<?php
#Prado::using('System.Util.TVarDumper');
class menu extends TPage
{
    public function onInit($param){
		//Prado::log(TVarDumper::dump($this->master->titulo->Text,2),TLogger::NOTICE,'-');
		$this->master->titulo->Text = "Menú principal";
		$this->master->subtitulo->Text = "Main";
	}
	
	public function onLoad($param)
    {
		//Prado::log(TVarDumper::dump($modulos,2),TLogger::NOTICE,'-');
        if(!$this->IsPostBack)
        {
			foreach($this->User->Roles as $indice => $row){ $roles = $row;}
            $modulos = $this->Application->Modules['query']->Client->queryForList("vwMenuLateral",array('id_roles' => $roles, 'id_modulos' => ''));
            //Prado::log(TVarDumper::dump($modulos,2),TLogger::NOTICE,'-');
            if(count($modulos) > 0){
                $this->RpModulos->DataSource = $modulos;
                $this->RpModulos->dataBind();
            }
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
	
	public function servicios_usuario($idmodulos){
		//LBsServicios
		$roles = "";
		foreach($this->User->Roles as $indice => $row){ $roles = ($roles!=""?',':'').$row;}
		$tabla = $this->Application->Modules['query']->Client->queryForList("vwMenuTab",array('id_roles' => $roles, 'id_modulos' => $idmodulos));
		return $tabla;
	}
	
}