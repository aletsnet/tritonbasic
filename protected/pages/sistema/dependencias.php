<?php

class dependencias extends TPage
{
	public $i = 0;
	
	public function onInit($param){
		$this->master->titulo->Text = "Dependencias";
		$this->master->subtitulo->Text = "Sistema";
		$this->Page->Title="Dependencias - Sistema";
	}
    
    public function onLoad($param)
    {
		$v = $this->User->ServicioActivo("2");
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
            $this->tpanelAviso->visible = false;
            $this->Formulario->visible = false;
            
            $this->dgTabla->VirtualItemCount = $this->getRowCount();
            $this->dgTabla->DataSource=$this->getDataRows(0,$this->dgTabla->PageSize);
            $this->dgTabla->dataBind();
        }
    }
    
    public function btnNuevo_OnClick($sender, $param){
		$this->tpanelAviso->visible = false;
		$this->Formulario->visible = true;
        $this->tpDatos->visible = false;
        $this->tpSinDatos->visible = false;
        $this->Buscador->visible = false;
	}
	
	public function btnGuardar_OnClick($sender, $param){
		if($this->IsValid)
        {
			$row  = LBsDependencias::finder()->find("borrado = 0 AND id_dependencias = ?", $this->id_dependencias->value);
			if($row instanceof LBsDependencias){
				if($this->file->value != ""){
					$row->ticket_logo = $this->file->value;
				}
				$row->id_dependencias = $this->id_dependencias->value;
				
				$row->dependencia     = $this->txtDependencia->Text;
				$row->direccion       = $this->txtDireccion->Text;
				$row->ticket_head     = $this->TareaEncabezado->text;
				$row->ticket_fool     = $this->TareaPie->text;
				$row->save();
			}else{
				$row = new LBsDependencias;
				if($this->file->value != ""){
					$row->ticket_logo = $this->file->value;
				}
				$row->id_dependencias = $this->id_dependencias->value;
				
				$row->dependencia     = $this->txtDependencia->Text;
				$row->direccion       = $this->txtDireccion->Text;
				$row->ticket_head     = $this->TareaEncabezado->text;
				$row->ticket_fool     = $this->TareaPie->text;
				$row->save();
			}
			$this->tpanelAviso->visible = true;
			$this->Formulario->visible  = false;
			$this->tpDatos->visible     = false;
			$this->tpSinDatos->visible  = false;
			$this->Buscador->visible    = true;
			
			$this->dgTabla->VirtualItemCount = $this->getRowCount();
            $this->dgTabla->DataSource=$this->getDataRows(0,$this->dgTabla->PageSize);
            $this->dgTabla->dataBind();
			
			$this->file->value            = "";
			$this->id_dependencias->value = "";
			$this->txtDependencia->Text   = "";
			$this->txtDireccion->Text     = "";
			$this->TareaEncabezado->text  = "";
			$this->TareaPie->text         = "";
		}
	}
	
	public function btnEditar_OnClick($sender, $param){
		$item = $sender->namingContainer;
		$keys = $this->dgTabla->DataKeys;
		
		$this->id_dependencias->value =  $keys[$item->itemIndex];
		$row  = LBsDependencias::finder()->find("borrado = 0 AND id_dependencias = ?", $this->id_dependencias->value);
		if($row instanceof LBsDependencias){
			if($row->ticket_logo != ""){
				$this->foto->ImageUrl = $row->ticket_logo;
				$this->file->value = $row->ticket_logo;
			}
			$this->id_dependencias->value = $row->id_dependencias;
			$this->txtDependencia->Text = $row->dependencia;
			$this->txtDireccion->Text = $row->direccion;
			$this->TareaEncabezado->text = $row->ticket_head;
			$this->TareaPie->text = $row->ticket_fool;
		}
		
		$this->tpanelAviso->visible = false;
		$this->Formulario->visible = true;
        $this->tpDatos->visible = false;
        $this->tpSinDatos->visible = false;
        $this->Buscador->visible = false;
		//Prado::log(TVarDumper::dump($keys[$item->itemIndex],1),TLogger::NOTICE,$this->PagePath);
	}
    
    public function btnEliminarAcceso_OnClick($sender, $param){
		$item = $sender->namingContainer;
		$keys = $this->dgAccesos->DataKeys;
		$row  = LCtAccesos::finder()->find("activo = 1 AND id_accesos = ?", $keys[$item->itemIndex]);
		if($row instanceof LCtAccesos){
			$row->activo = 0;
			$row->save();
		}
        $this->dgAccesos->DataSource = LCtAccesos::finder()->findAll(" activo = 1 AND id_sucursal = ? ",$this->id_sucursales->value);
		$this->dgAccesos->dataBind();
    }
    
    public function btnBuscar_OnClick($sender, $param){
		$this->dgTabla->VirtualItemCount = $this->getRowCount();
		$this->dgTabla->DataSource=$this->getDataRows(0,$this->dgTabla->PageSize);
		$this->dgTabla->dataBind();
	}
	
	protected function getDataRows($offset,$rows)
    {
        //$idareas = $this->User->idarea;
		$where = " borrado = 0 ";
		$ct_buscar = new TActiveRecordCriteria;
		if($this->txtBuscar->text != ""){
			$where .= " AND (dependencia LIKE :buscar
						OR direccion LIKE :buscar )";
			$ct_buscar->Parameters[':buscar'] = "%".$this->txtBuscar->text."%";
			//$Parametros['buscar'] = "%".$this->txtBuscar->text."%";
		}
		
		$ct_buscar->Condition = $where;
		$ct_buscar->OrdersBy['id_dependencias'] = 'asc';
        $ct_buscar->Limit = $rows;
        $ct_buscar->Offset = $offset;
		
        $tabla = LBsDependencias::finder()->findAll($ct_buscar);
		//Prado::log(TVarDumper::dump($tabla,1),TLogger::NOTICE,$this->PagePath);
        return $tabla;
    }
    
    protected function getRowCount()
    {
        //$idareas = $this->User->idarea;
		$where = " borrado = 0 ";
		$ct_buscar = new TActiveRecordCriteria;
		$Parametros = array();
		
		if($this->txtBuscar->text != ""){
			$where .= " AND (dependencia LIKE :buscar
						OR direccion LIKE :buscar )";
			$ct_buscar->Parameters[':buscar'] = "%".$this->txtBuscar->text."%";
			$Parametros['buscar'] = "%".$this->txtBuscar->text."%";
		}
		
		$ct_buscar->Condition = $where;
		
		//$var = $this->Application->Modules['query']->Client->queryForObject("vwListaLlamadas_count",$Parametros);
        $var = LBsDependencias::finder()->count($ct_buscar);
		$this->linkPdf->NavigateUrl = $this->Service->constructUrl('sistema.sucursalespdf',$Parametros);
		//$this->linkPdf2->NavigateUrl = $this->Service->constructUrl('llamadas.listapdf',$Parametros);
		//$var = 0;
		$visible = $var > 0;
		$this->tpDatos->Visible = $visible;
		$this->tpSinDatos->Visible = !$visible;
		//$this->lelementos->Text = $var;
		
        return $var;
    }
	
	
	public function getRows($deforder=true,$offset=0)
    {
        // Si se consulta desde el principio el indice
        // de GridDatos debe cambiar a 0
        if($offset==0)
        {
            $this->dgTabla->CurrentPageIndex=0;
        }
        
		
        // Llena de datos GridDatos
        //$this->visita->setVirtualItemCount(TableVisitas::finder()->count());
        $this->dgTabla->VirtualItemCount = $this->getRowCount();
        $this->dgTabla->DataSource=$this->getDataRows($offset,$this->dgTabla->PageSize);
        $this->dgTabla->dataBind();
    }
    
	public function itemCreated($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
			$row = $item->Data;
			if($row instanceof LBsDependencias ){
				$this->i ++;
				$item->rowI->lNumero->Text = $this->i;
				$item->rowImagen->Style="width: 64px;";
				if($row->ticket_logo != "")
					$item->rowImagen->logo->ImageUrl = $row->ticket_logo;
				
			}
		}
		
        if($item->ItemType==='EditItem')
        {
            
        }
	}
	
    public function changePage($sender,$param)
    {
        $this->dgTabla->CurrentPageIndex=$param->NewPageIndex;
        $offset=$param->NewPageIndex*$this->dgTabla->PageSize;
        $this->dgTabla->DataSource=$this->getDataRows($offset,$this->dgTabla->PageSize);
        $this->dgTabla->dataBind();
    }
	
	public function pagerCreated($sender,$param)
    {
        $param->Pager->Controls->insertAt(0,'Pagina(s): ');
		$this->i = $this->dgTabla->CurrentPageIndex * 10;
		//Prado::log(TVarDumper::dump($param,2),TLogger::NOTICE,$this->PagePath);
    }
    
	public function dgAccesos_itemCreated($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
			$row = $item->Data;
			if($row instanceof LCtAccesos ){
				$this->i ++;
				$item->rowJ->lNumero->Text = $this->i;
                $item->rowAcceso->lAcceso->Text = $item->Data->acceso;
				$item->rowBoton->btnEliminarAcceso->Attributes->onclick='if(!confirm(\'¿Esta seguro de eliminar este registro?\')) return false;';
				//$item->rowImagen->Style="width: 64px;";
				//if($row->foto != "")
				//	$item->rowImagen->foto->ImageUrl = $row->foto;
				
			}
		}
		
        if($item->ItemType==='EditItem')
        {
            
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
                    $nombrearchivo = "Perfil".rand(1,200).rand(201,500).date('dmyhisA').".".$original[count($original)-1];
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
?>