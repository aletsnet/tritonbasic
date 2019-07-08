<?php
class datosfiscales extends TPage
{
	public function onInit($param){
		$this->master->titulo->Text    = "Datos fiscales";
		$this->master->subtitulo->Text = "contratos";
		$this->Page->Title             = "Datos fiscales - Sistema";
        $this->logo->ImageUrl          = $this->Application->Parameters["imgProyect"];
	}
    
    public function onLoad($param)
    {
		$v = $this->User->ServicioActivo("19");
		//$this->perimiso_actualizar = $this->User->ServicioActivo(23);
        if(!$v){
			Prado::log(
				"[".$_SERVER['REMOTE_ADDR']."][".$this->User->idusuarios.'][Permiso denegado]',
				TLogger::NOTICE,
				$this->PagePath
			);
            $url = $this->Service->constructUrl('sistema.403');
            $this->Response->redirect($url);
        }
        if(!$this->IsPostBack){
            
            $this->cmdRegimen->DataSource = LBsCatalogosGenericos::finder()->findAll(" catalogo = 11 AND activo = 1 ");
            $this->cmdRegimen->dataBind();
            
            $rowContrato = LMsContratos::finder()->find("");
            if($rowContrato instanceof LMsContratos){
                $this->idcontratos->value = $rowContrato->id_contratos;
                $this->idtipo->value      = $rowContrato->id_tipo;
                if($rowContrato->logo != ""){
                    $this->logo->ImageUrl     = $rowContrato->logo;
                    $this->file->value        = $rowContrato->logo;
                }
                $this->txtNombre->Text    = $rowContrato->nombre;
                $this->txtRfc->Text       = $rowContrato->rfc;
                $this->cmdRegimen->Text   = $rowContrato->regimen_fiscal;
                $this->txtRazonSocial->Text= $rowContrato->razon_social;
                $this->txtTelefono->Text  = $rowContrato->telefono;
            }
        }
    }
    
    public function btnGuardar_OnClick($sender, $param){
        if($this->IsValid){
            $rowContrato = LMsContratos::finder()->find(" id_contratos = ? ",$this->idcontratos->value);
            if(!$rowContrato instanceof LMsContratos){
                $rowContrato = new LMsContratos;
                $rowContrato->id_tipo = 2;
                $rowContrato->fecha_inicio = date('Y-m-d H:i:s');
            }
            
            if($rowContrato instanceof LMsContratos){
                if($this->file->value){
                    $rowContrato->logo    = $this->file->value;
                }
                $rowContrato->id_usuarios = $this->User->idusuarios;
                $rowContrato->nombre  = $this->txtNombre->Text;
                $rowContrato->rfc     = $this->txtRfc->Text;
                $rowContrato->regimen_fiscal = $this->cmdRegimen->Text;
                $rowContrato->razon_social = $this->txtRazonSocial->Text;
                $rowContrato->telefono = $this->txtTelefono->Text;
                $rowContrato->save();
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
                    $nombrearchivo = "Logo".rand(1,200).rand(201,500).date('dmyhisA').".".$original[count($original)-1];
                    copy($sender->LocalName,"docs/logo/".$nombrearchivo);
                }
                $this->file->value = "docs/logo/".$nombrearchivo;
                $this->logo->ImageUrl = $this->file->value;
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