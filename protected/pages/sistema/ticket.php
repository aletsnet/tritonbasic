<?php
 
class ticket extends TPage
{
	public $i = 0;
	
	public function onInit($param){
		$this->master->titulo->Text = "Configuración de Ticket";
		$this->master->subtitulo->Text = "Plantilla general de impreción";
        $this->Title = "Configuración de Ticket - Sistema";
	}
	
    public function onLoad($param)
    {
		$v = $this->User->ServicioActivo("14");
		//$this->perimiso_actualizar = $this->User->ServicioActivo(23);
        if(!($v)){
			Prado::log(
				"[".$_SERVER['REMOTE_ADDR']."][".$this->User->idusuarios.'][Permiso denegado]',
				TLogger::NOTICE,
				$this->PagePath
			);
            $url = $this->Service->constructUrl('sistema.403');
            $this->Response->redirect($url);
        }
        if(!$this->IsPostBack)
        {
            $row = LCtSucursales::finder()->find(" borrado = 0");
			if($row instanceof LCtSucursales){
				if($row->ticket_logo != ""){
					$this->foto->ImageUrl = $row->ticket_logo;
					$this->file->value = $row->ticket_logo;
				}
			}
			$this->txtNombre->Text     = $row->sucursal;
			$this->txtDireccion->Text  = $row->direccion;
			$this->ckCorte->checked    = $row->corte_compartido;
			$this->txtEncabezado->Text = $row->ticket_head;
			$this->txtPiePagina->Text  = $row->ticket_fool;
			$this->ckTicket->checked   = $row->ticket_automatico;
			$this->ckMostrar->checked  = $row->ticket_imprimir;
        }
    }
	
	public function btnGuardar_OnClick($sender, $param){
		if($this->IsValid){
            $row = LCtSucursales::finder()->find(" borrado = 0");
			if($row instanceof LCtSucursales){
				if($this->file->value != ""){
					$row->ticket_logo = $this->file->value;
				}
				$row->sucursal = $this->txtNombre->Text;
				$row->direccion = $this->txtDireccion->Text;
				$row->corte_compartido = $this->ckCorte->checked;
				$row->ticket_head = $this->txtEncabezado->Text;
				$row->ticket_fool = $this->txtPiePagina->Text;
				$row->ticket_automatico = $this->ckTicket->checked;
				$row->ticket_imprimir = $this->ckMostrar->checked;
				$row->save();
			}
        }
	}
	
	public function fileUploaded($sender,$param)
    {
        if($sender->HasFile)
        {
            $nombrearchivo = "";
            if($sender->FileName!=""){
                if(is_file($sender->LocalName)){
                    $original = explode(".",$sender->FileName);
                    $nombrearchivo = "Ticket".rand(1,200).rand(201,500).date('dmyhisA').".".$original[count($original)-1];
                    copy($sender->LocalName,"docs/avatar/".$nombrearchivo);
                }
                $this->file->value = "docs/avatar/".$nombrearchivo;
                //$_SESSION['foto'] = "docs/avatar/".$nombrearchivo;
                $this->foto->ImageUrl = $this->file->value;
                //$this->leyenda->text = "Se Guardado correctamente el archivo";
                //$this->leyenda->CssClass = "show_correct";
				$this->imagenfija->visible = true;
				//$this->btnTomar->visible = false;
				//$this->btnCamara->visible = true;
            }
        }else{
            switch($sender->ErrorCode){
                case 0:
                    //
                    break;
                case 1:
                    $this->leyenda->text = 'El archivo excede el tamaño de 2MB';
                    $this->leyenda->CssClass = "error";
                    break;
                case 2:
                    $this->leyenda->text = 'El archivo excede el tamaño de 2MB';
                    $this->leyenda->CssClass = "error";
                    break;
                case 3:
                    $this->leyenda->text = 'Temporalmente no disponible';
                    $this->leyenda->CssClass = "error";
                    break;
                case 4:
                    $this->leyenda->text = ' ';
                    $this->leyenda->CssClass = "error";
                    break;
                default:
                    $this->leyenda->text = 'Error:'.$sender->ErrorCode;
                    $this->leyenda->CssClass = "error";
                    break;
            }
        }
    }
}