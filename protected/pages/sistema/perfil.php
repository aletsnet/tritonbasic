<?php

class perfil extends TPage
{
    public function onInit($param){
		$this->master->titulo->Text = "Menú principal";
		$this->master->subtitulo->Text = "Main";
        $this->foto->ImageUrl = $this->Application->Parameters["imgProyect"];
	}
	
	public function onLoad($param)
    {
		//Prado::log(TVarDumper::dump($modulos,2),TLogger::NOTICE,'-');
        if(!$this->IsPostBack)
        {
			$this->tpanelAviso->visible = false;
			$row = LBsUsuarios::finder()->findByPk($this->User->idusuarios);
			if($row instanceof LBsUsuarios){
				if($row->foto != ""){
					$this->foto->ImageUrl = $row->foto;
					$this->file->value = $row->foto;
				}
				
				$this->txtNombre->Text    = $row->nombre;
				$this->txtCargo->Text     = $row->cargo;
				$this->txtEmail->Text     = $row->correo;
				$this->txtTelefono->Text  = $row->telefono;
				$this->txtUsuario->text   = $row->user;
                $this->loginvalido->value = 1;
				$this->txtPassword->text  = ""; //$row->pass;
				$this->lRol->text         = $row->bs_roles->nombre;
				$this->lDependencia->text = $row->ct_sucursales->sucursal;
				$this->lActivo->text      = ($row->activo?'Activo':'Inactivo');
				$this->lJsScript->Text = "";
			}
		}
    }
	
	public function btnPerfilGuardar($sender, $param){
        if($this->IsValid){
            $row = LBsUsuarios::finder()->findByPk($this->User->idusuarios);
            if($row instanceof LBsUsuarios){
				$row->nombre   = $this->txtNombre->Text;
				$row->cargo    = $this->txtCargo->Text;
                $row->correo   = $this->txtEmail->Text;
                $row->telefono = $this->txtTelefono->Text;
				if($this->file->value != ""){
					$row->foto = $this->file->value;
				}
				$row->user = $this->txtUsuario->text;
				if($this->txtPassword->text != ""){
					$row->pass = sha1($this->txtPassword->text);
				}
				$row->fecha_actualizado = date('Y-m-d H:i:s');
                $row->save();
				//$this->User->bitacora_usuario(7,2,"El actualizó usuario [".$row->id_usuarios."]");
                //$this->lJsScript->Text = "";
				$this->tpanelAviso->visible = true;
            }
        }
    }
	
	
    public function BtnCamara($sender,$param){
        $this->camara->visible = true;
        $this->imagenfija->visible = false;
        $this->btnTomar->visible = true;
        $this->btnCamara->visible = false;
    }
    
    public function Validador_txtPassword($sender, $param){
		$valido = false;
		if($this->txtPassword->text != "" && $this->id_usuarios->value == ""){
            $valido = true;
		}elseif($this->id_usuarios->value != ""){
			$valido = true;
		}	
		$param->IsValid = $valido;
	}
	
	public function btnCapturaCamara($sender,$param){
		$nombrearchivo = "Perfil".rand(1,200).rand(201,500).date('dmyhisA').".png";
		$urldestino = 'docs/avatar/';
		$rawData = $this->imagetxt->value;//$_POST['imgBase64'];
		//Prado::log(TVarDumper::dump($this->imagetxt->value,1),TLogger::NOTICE,$this->PagePath);
		if($rawData != ""){
			$filteredData = explode(',', $rawData);
			$unencoded = base64_decode($filteredData[1]);
			$fp = fopen($urldestino.$nombrearchivo, 'w');
			fwrite($fp, $unencoded);
			fclose($fp);
			$this->file->value = $urldestino.$nombrearchivo;
			//$_SESSION['foto'] = "imagen/fotos/".$nombrearchivo;
			$this->foto->ImageUrl = $this->file->value;
			$this->camara->visible = false;
		}
		
		//$this->file->value = $nombrearchivo;
        //$this->foto->ImageUrl = $this->imagetxt->value; //$this->file->value;
        //$this->camara->visible = false;
        $this->imagenfija->visible = true;
        $this->btnTomar->visible = false;
        $this->btnCamara->visible = true;
    }
	
	public function fileUploaded($sender,$param)
    {
        if($sender->HasFile)
        {
            $nombrearchivo = "";
            if($sender->FileName!=""){
                if(is_file($sender->LocalName)){
                    $original = explode(".",$sender->FileName);
                    $nombrearchivo = "Perfil".rand(1,200).rand(201,500).date('dmyhisA').".".$original[count($original)-1];
                    copy($sender->LocalName,"docs/avatar/".$nombrearchivo);
                }
                $this->file->value = "docs/avatar/".$nombrearchivo;
                //$_SESSION['foto'] = "docs/avatar/".$nombrearchivo;
                $this->foto->ImageUrl = $this->file->value;
                //$this->leyenda->text = "Se Guardado correctamente el archivo";
                //$this->leyenda->CssClass = "show_correct";
				$this->imagenfija->visible = true;
				$this->btnTomar->visible = false;
				$this->btnCamara->visible = true;
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
    
    public function txtUsuario_OnTextChanged($sender, $param){
		$rows = LBsUsuarios::finder()->count("borrado = 0 AND user = ?", $sender->Text);
        if($rows == 0){
			//$this->lbstatus->cssclass = 'label label-success';
			$this->lbstatus->text = '<label class="label label-success"><i class="fa fa-check"></i></label>';
            $this->loginvalido->value = 1;
		}else{
			//$this->lbstatus->cssclass = 'btn btn-danger';
			$this->lbstatus->text = '<label class="label label-danger"><i class="fa fa-close"></i></label>';
            $this->loginvalido->value = "";
		}
	}
}