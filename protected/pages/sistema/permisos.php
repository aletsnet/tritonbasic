<?php
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
//use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
 
class Permisos extends TPage
{
	public $i = 0;
	public $perimiso_actualizar = false;
	
	public function onInit($param){
		$this->master->titulo->Text = "Usuario y permisos";
		$this->master->subtitulo->Text = "Sistema";
		$this->foto->ImageUrl = $this->Application->Parameters["imgProyect"];
	}
	
    public function onLoad($param)
    {
		$v = $this->User->ServicioActivo("1");
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
		$this->cmdRol->DataSource = LBsRoles::finder()->findAll(" borrado = 0 ");
		$this->cmdRol->dataBind();
		
		$this->cmdDescuentas->DataSource = LBsCatalogosGenericos::finder()->findAll(" catalogo = 8 AND activo = 1 ");
		$this->cmdDescuentas->dataBind();
		
        $this->tpanelAviso->visible = false;
		$this->Formulario->visible = true;
        $this->tpDatos->visible = false;
        $this->tpSinDatos->visible = false;
        $this->Buscador->visible = false;
        
        $this->id_usuarios->value = "";
        $this->txtNombre->text = "";
        $this->txtCargo->text = "";
		//$this->txtDependencia->Text = "";
    }
    
	public function btnEditar_OnClick($sender, $param){
		$item = $sender->namingContainer;
		$keys = $this->dgTabla->DataKeys;
		
		$row  = LBsUsuarios::finder()->find("borrado = 0 AND id_usuarios = ?", $keys[$item->itemIndex]);
		if($row instanceof LBsUsuarios){
			if($row->foto != ""){
				$this->foto->ImageUrl = $row->foto;
				$this->file->value = $row->foto;
			}
			
			$this->cmdRol->DataSource = LBsRoles::finder()->findAll(" borrado = 0 ");
			$this->cmdRol->dataBind();
			
			$this->cmdDescuentas->DataSource = LBsCatalogosGenericos::finder()->findAll(" catalogo = 8 AND activo = 1 ");
			$this->cmdDescuentas->dataBind();
			
			//$this->txtDependencia->Text = $row->dependencia;
			
			$this->txtNombre->Text     = $row->nombre;
			$this->txtCargo->Text      = $row->cargo;
			$this->txtUsuario->text    = $row->user;
			$this->txtPassword->text   = ""; //$row->pass;
			$this->cmdRol->text        = $row->id_roles;
			$this->cmdDescuentas->Text = $row->descuento;
			$this->ChActivo->checked   = $row->activo;
			$this->ChInactivo->checked = !$row->activo;
			$this->id_usuarios->value  = $row->id_usuarios;
		}
		
		$this->tpanelAviso->visible = false;
		$this->Formulario->visible = true;
        $this->tpDatos->visible = false;
        $this->tpSinDatos->visible = false;
        $this->Buscador->visible = false;
	}
	
	public function Validador_ChActivo($sender, $param){
		$valido = false;
		if($this->ChActivo->checked == true || $this->ChInactivo->checked == true)
            $valido = true;
		
		$param->IsValid = $valido;
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
	
	public function txtUsuario_OnTextChanged($sender, $param){
		$rows = LBsUsuarios::finder()->count("borrado = 0 AND user = ?", $sender->Text);
        if($rows == 0){
			//$this->lbstatus->cssclass = 'label label-success';
			$this->lbstatus->text = '<label class="label label-success"><i class="fa fa-check"></i></label>';
		}else{
			//$this->lbstatus->cssclass = 'btn btn-danger';
			$this->lbstatus->text = '<label class="label label-danger"><i class="fa fa-close"></i></label>';
		}
	}
	
	
    public function btnGuardar_OnClick($sender, $param){
        //$idareas = $this->User->idarea;
		//$idusuario = $this->User->idusuarios;
		if($this->IsValid)
        {
            $row = LBsUsuarios::finder()->find("borrado = 0 AND id_usuarios = ?", $this->id_usuarios->value);
            if($row instanceof LBsUsuarios){
                //$row->id_invitaciones
                //$row->id_usuario = $idusuario;
				$row->nombre    = $this->txtNombre->Text;
				$row->cargo     = $this->txtCargo->Text;
				if($this->file->value != ""){
					$row->foto  = $this->file->value;
				}
				$row->user              = $this->txtUsuario->text;
				if($this->txtPassword->text != ""){
					$row->pass  = sha1($this->txtPassword->text);
				}
				$row->id_roles          = $this->cmdRol->text;				
				//$row->dependencia = $this->txtDependencia->Text;
				$row->descuento = $this->cmdDescuentas->Text;
				$row->activo    = $this->ChActivo->checked;
				$row->fecha_actualizado = date('Y-m-d H:i:s');
                $row->save();
            }else{
                $row = new LBsUsuarios;
                $row->nombre     = $this->txtNombre->Text;
				$row->cargo      = $this->txtCargo->Text;
				if($this->file->value != ""){
					$row->foto   = $this->file->value;
				}
				$row->user       = $this->txtUsuario->text;
				$row->pass       = sha1($this->txtPassword->text);
				$row->id_roles   = $this->cmdRol->text;
				//$row->dependencia = $this->txtDependencia->Text;
				$row->descuento  = $this->cmdDescuentas->Text;
				$row->activo     = $this->ChActivo->checked;
				$row->fecha_alta = date('Y-m-d H:i:s');
				//$row->fecha_actualizado = date('Y-m-d H:i:s');
                $row->save();
                
            }
			
			$this->foto->ImageUrl       = "image/persona.jpg";
			$this->file->value          = "";
			$this->txtNombre->Text      = "";
			$this->txtCargo->Text       = "";
			$this->txtUsuario->text     = "";
			$this->txtPassword->text    = "";
			$this->cmdRol->text         = "";
			//$this->cmdAccesos->text = "";
			$this->ChActivo->checked    = false;
			$this->ChInactivo->checked  = false;
			$this->id_usuarios->value   = "";
        
			$this->Formulario->visible  = false;
			$this->tpanelAviso->visible = true;
			$this->Buscador->visible    = true;
			
			$this->dgTabla->VirtualItemCount = $this->getRowCount();
			$this->dgTabla->DataSource  = $this->getDataRows(0,$this->dgTabla->PageSize);
			$this->dgTabla->dataBind();
        }
    }
    
	protected function btnBuscardor_OnClick($sender, $param){
		$this->Formulario->visible = false;
        $this->tpanelAviso->visible = false;
        $this->Buscador->visible = true;
        
        $this->dgTabla->VirtualItemCount = $this->getRowCount();
		$this->dgTabla->DataSource=$this->getDataRows(0,$this->dgTabla->PageSize);
		$this->dgTabla->dataBind();
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
			$where .= " AND ( nombre LIKE :buscar
						OR user LIKE :buscar )";
			$ct_buscar->Parameters[':buscar'] = "%".$this->txtBuscar->text."%";
			//$Parametros['buscar'] = "%".$this->txtBuscar->text."%";
		}
		
		$ct_buscar->Condition = $where;
		$ct_buscar->OrdersBy['nombre'] = 'asc';
        $ct_buscar->Limit = $rows;
        $ct_buscar->Offset = $offset;
		
        $tabla = LBsUsuarios::finder()->findAll($ct_buscar);
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
			$where .= " AND ( nombre LIKE :buscar
						OR user LIKE :buscar )";
			$ct_buscar->Parameters[':buscar'] = "%".$this->txtBuscar->text."%";
			$Parametros['buscar'] = "%".$this->txtBuscar->text."%";
		}
		
		$ct_buscar->Condition = $where;
		
		//$var = $this->Application->Modules['query']->Client->queryForObject("vwListaLlamadas_count",$Parametros);
        $var = LBsUsuarios::finder()->count($ct_buscar);
		$this->linkPdf->NavigateUrl = $this->Service->constructUrl('sistema.permisospdf',$Parametros);
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
			if($row instanceof LBsUsuarios ){
				$this->i ++;
				$item->rowI->lNumero->Text = $this->i;
				$item->rowImagen->Style="width: 64px;";
				if($row->foto != ""){
					$item->rowImagen->foto->ImageUrl = $row->foto;
				}
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
		//Prado::log(TVarDumper::dump($param,2),TLogger::NOTICE,$this->PagePath);
    }
	
	public function BtnCamara($sender,$param){
        $this->camara->visible = true;
        $this->imagenfija->visible = false;
        $this->btnTomar->visible = true;
        $this->btnCamara->visible = false;
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
	
	public function btnExportarXLS_OnClick($sender,$param){
		//use PhpOffice\PhpSpreadsheet\Helper\Sample;
		//require 'vendor/autoload.php';
		//use = PhpOffice\PhpSpreadsheet\IOFactory;
		//use PhpOffice\PhpSpreadsheet\Spreadsheet;
		//Prado::log('-'.TVarDumper::dump($sender,1),TLogger::NOTICE,$this->PagePath);
		//$idareas = $this->User->idarea;
		
		$where = " borrado = 0 ";
		$ct_buscar = new TActiveRecordCriteria;
		
		if($this->txtBuscar->text != ""){
			$where .= " AND ( nombre LIKE :buscar
						OR user LIKE :buscar )";
			$ct_buscar->Parameters[':buscar'] = "%".$this->txtBuscar->text."%";
			//$Parametros['buscar'] = "%".$this->txtBuscar->text."%";
		}
		
		$ct_buscar->Condition = $where;
		$ct_buscar->OrdersBy['nombre'] = 'asc';
		
        $tabla = LBsUsuarios::finder()->findAll($ct_buscar);
		$rows = count($tabla);
		
		//Prado::using('Application.modulos.PHPExcel');
		// Create new PHPExcel object
		//echo date('H:i:s') , " Create new PHPExcel object" ;
		$objPHPExcel = new Spreadsheet();
		
		// Establecer propiedades
		$fecha_t = date("d m Y H i s");
		$objPHPExcel->getProperties()
					->setCreator("AletsNet")
					->setLastModifiedBy("AletsNet")
					->setTitle("Incidencias - Usuarios")
					->setSubject("Lista de Usuarios")
					->setDescription("Lista de usuarios del sistema")
					->setKeywords("Incidencias, Triton, Incidencias, Incidencias 2018")
					->setCategory("Incidencias 2018");
		
		// Agregar Informacion
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A1', '#')
					->setCellValue('B1', 'Usuario')
					->setCellValue('C1', 'Nombre')
					->setCellValue('D1', 'Cargo')
					->setCellValue('E1', 'Rol')
					->setCellValue('F1', 'Acceso');
		$a = 1;
		//Prado::log('-'.TVarDumper::dump($tabla,1),TLogger::NOTICE,$this->PagePath);
		foreach($tabla as $i => $row){
			$a ++;
			//$tiempovisita = $this->User->tiempotrascurrio($row->fecha_cita,($row->fecha_atencion != '' ? $row->fecha_atencion : date('Y-m-d H:i:s')));
			//Prado::log('-'.TVarDumper::dump($tiempovisita,1),TLogger::NOTICE,$this->PagePath);
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A'.$a, $a-1)
						->setCellValue('B'.$a, $row->user)
						->setCellValue('C'.$a, $row->nombre)
						->setCellValue('D'.$a, $row->cargo)
						->setCellValue('E'.$a, $row->bs_roles->nombre)
						->setCellValue('F'.$a, $row->dependencia);
		}
		// Renombrar Hoja
		$objPHPExcel->getActiveSheet()->setTitle('Usuarios');
		
		// Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
		$objPHPExcel->setActiveSheetIndex(0);
		
		$fecha = date("d.m.y H:i:s");
		//$pdf->Output('Reporte'.$fecha.'.pdf', 'I');
		// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="ListaUsuarios'.$fecha.'.xlsx"');
		header('Cache-Control: max-age=0');
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
		$objWriter->save('php://output');
		
		Prado::log("[".$this->User->idusuarios.
                       "][xlsx][IP:".
                       $_SERVER['REMOTE_ADDR'].
                       "][Mostrar pagína XLS]",
                       TLogger::NOTICE,
                       $this->Page->PagePath);
		
		exit(0);
	}
}