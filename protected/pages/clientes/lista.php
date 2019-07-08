<?php
class lista extends TPage
{
	public $i = 0;
	
	public function onInit($param){
		$this->master->titulo->Text = "Creditos";
		$this->master->subtitulo->Text = "Clientes";
        $this->title = "Creditos - Clientes";
	}
	
    public function onLoad($param)
    {
		$v = $this->User->ServicioActivo("6");
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
            //$inicio = "01/".date('m/Y');
            //$this->fecha_inicio->Text = $inicio;
            //$this->fecha_final->TimeStamp = strtotime(" 1 months  ",$this->fecha_inicio->TimeStamp);
            $this->tpanelAviso->visible = false;
            $this->Formulario->visible = false;
			
			$this->dgTabla->VirtualItemCount = $this->getRowCount();
            $this->dgTabla->DataSource=$this->getDataRows(0,$this->dgTabla->PageSize);
            $this->dgTabla->dataBind();
        }
    }
    
	public function btnBuscar_OnClick($sender, $param){
		$this->getRows(false,($this->dgTabla->CurrentPageIndex*$this->dgTabla->PageSize));
	}
	
	public function btnNuevo_OnClick($sender, $param){
			
        $this->tpanelAviso->visible = false;
		$this->Formulario->visible = true;
        $this->tpDatos->visible = false;
        $this->tpSinDatos->visible = false;
        $this->Buscador->visible = false;
        
        $this->id_clientes->value = "";
        $this->txtBuscar->text = "";
		
		$this->foto->ImageUrl = "image/persona.jpg";
		$this->file->value = "";
		$this->txtRFC->Text = "";
		$this->txtNombre->Text = "";
		$this->txtDireccion->Text = "";
		$this->txtTelefono->Text = "";
		$this->id_clientes->value = "";
        //$this->txtCargo->text = "";
    }
	
    public function btnView_OnClick($sender, $param){
        $this->ModalVenta->open();
        $item = $sender->namingContainer;
		$keyid = $this->dgTabla->DataKeys[$item->itemIndex];
        //$row_venta = LMsVentas::finder()->find(" id_ventas = ?", array($keyid));
        
    }
    
	public function btnGuardar_OnClick($sender, $param){
        $id_clientes = $this->id_clientes->value;
        //$id_inventarios = $this->id_inventarios->value;
		//$id_sucursales = $this->User->idsucursales;
        
		if($this->IsValid)
        {
            //ms_productos
            $row = LMsClientes::finder()->find(" borrado = 0 AND id_clientes = ? ", $id_clientes);
            if($row instanceof LMsClientes){
                if($this->file->value != ""){
                    $row->foto = $this->file->value;
                }
                $row->rfc = $this->txtRFC->Text;
				$row->nombre = $this->txtNombre->Text;
                $row->direccion = $this->txtDireccion->Text;
				$row->telefono = $this->txtTelefono->Text;
                $row->credito = $this->txtCredito->Text;
                $row->tipo_cliente = 2;
                $row->save();
                //$this->id_productos->value = $row->id_productos;
            }else{
                $row = new LMsClientes;
                if($this->file->value != ""){
                    $row->foto = $this->file->value;
                }
                $row->rfc = $this->txtRFC->Text;
				$row->nombre = $this->txtNombre->Text;
                $row->telefono = $this->txtTelefono->Text;
				$row->direccion = $this->txtDireccion->Text;
                $row->credito = $this->txtCredito->Text;
                $row->tipo_cliente = 2;
                $row->save();
                $this->id_clientes->value = $row->id_clientes;
                //$id_productos = $row->id_productos;
            }
            
            $this->foto->ImageUrl = "image/persona.jpg";
			$this->file->value = "";
            $this->txtRFC->Text = "";
			$this->txtNombre->Text = "";
            $this->txtDireccion->Text = "";
            $this->txtTelefono->Text = "";
            $this->id_clientes->value = "";
			
			$this->Formulario->visible = false;
			$this->tpanelAviso->visible = true;
			$this->Buscador->visible = true;
			
			$this->dgTabla->VirtualItemCount = $this->getRowCount();
			$this->dgTabla->DataSource=$this->getDataRows(0,$this->dgTabla->PageSize);
			$this->dgTabla->dataBind();
        }
    }
    
	public function btnEditar_OnClick($sender, $param){
		$this->tpanelAviso->visible = false;
		$this->Formulario->visible = true;
        $this->tpDatos->visible = false;
        $this->tpSinDatos->visible = false;
        $this->Buscador->visible = false;
        
        $this->id_clientes->value = "";
        $this->txtBuscar->text = "";
		
		$item = $sender->namingContainer;
		$keys = $this->dgTabla->DataKeys;
		$this->id_clientes->value = $keys[$item->itemIndex];
		$row  = LMsClientes::finder()->find("borrado = 0 AND id_clientes = ?", $keys[$item->itemIndex]);
		if($row instanceof LMsClientes ){
			//Prado::log(TVarDumper::dump($row->ms_productos,1),TLogger::NOTICE,$this->PagePath);
			if($row->foto != ""){
				$this->foto->ImageUrl = $row->foto;
				$this->file->value = $row->foto;
			}
			$this->txtRFC->Text = $row->rfc;
			$this->txtNombre->Text = $row->nombre;
			$this->txtTelefono->Text = $row->telefono;
			$this->txtDireccion->Text = $row->direccion;
			$this->txtCredito->Text = $row->credito;
		}
		
		//Prado::log(TVarDumper::dump($keys[$item->itemIndex],1),TLogger::NOTICE,$this->PagePath);
	}
	
    protected function getDataRows($offset,$rows)
    {
        $idsucursales = $this->User->idsucursales;
        $where = " borrado = 0 AND tipo_cliente = 2";
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Parameters[':buscar'] = "%".$this->txtBuscar->Text."%";
        $where .= " AND (nombre like :buscar OR rfc like :buscar OR telefono like :buscar)";
		$ct_buscar->Condition = $where;
        
		$ct_buscar->OrdersBy['rfc'] = 'decs';
        $ct_buscar->Limit = $rows;
        $ct_buscar->Offset = $offset;
        
		$tabla = LMsClientes::finder()->findAll($ct_buscar);
        //Prado::log($fecha_inicio . " - " . $fecha_final,TLogger::NOTICE,$this->PagePath);
        //Prado::log(TVarDumper::dump($ct_buscar,2),TLogger::NOTICE,$this->PagePath);
		return $tabla;
    }
    
    protected function getRowCount()
    {
        $idsucursales = $this->User->idsucursales;
        $where = " borrado = 0 AND tipo_cliente = 2 ";
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Parameters[':buscar'] = "%".$this->txtBuscar->Text."%";
        $where .= " AND (nombre like :buscar OR rfc like :buscar OR telefono like :buscar)";
		$ct_buscar->Condition = $where;
		
        $param_pdf = array("buscar" => "%".$this->txtBuscar->Text."%");
        
		//$var = $this->Application->Modules['query']->Client->queryForObject("vwVentas_count",$Parametros);
        $var = LMsClientes::finder()->count($ct_buscar);
		$this->linkPdf->NavigateUrl = $this->Service->constructUrl('clientes.listapdf',$param_pdf);
		//$this->linkPdf2->NavigateUrl = $this->Service->constructUrl('llamadas.listapdf',$Parametros);
        //Prado::log('-'.TVarDumper::dump($param_pdf,1),TLogger::NOTICE,$this->PagePath);
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
        
        //$this->i = $offset;
        
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
			if($row instanceof LMsClientes ){
				$this->i ++;
                $item->rowSaldo->lSaldo->Text = "$" . number_format($this->User->SaldoActual($row->id_clientes),2);
				$item->rowI->lNumero->Text = $this->i;
				$item->rowDeuda->lDeuda->Text = "$" . number_format($row->credito,2);
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
        $this->i = $offset;
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
		$idsucursales = $this->User->idsucursales;
		$where = " borrado = 0 AND tipo_cliente = 2 ";
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Parameters[':buscar'] = "%".$this->txtBuscar->Text."%";
        $where .= " AND (nombre like :buscar OR rfc like :buscar OR telefono like :buscar)";
		$ct_buscar->Condition = $where;
		$ct_buscar->OrdersBy['rfc'] = 'decs';
        
		$tabla = LMsClientes::finder()->findAll($ct_buscar);
		$rows = count($tabla);
		
		Prado::using('Application.modulos.PHPExcel');
		// Create new PHPExcel object
		//echo date('H:i:s') , " Create new PHPExcel object" ;
		$objPHPExcel = new PHPExcel();
		
		// Establecer propiedades
		$fecha_t = date("d m Y H i s");
		$objPHPExcel->getProperties()
					->setCreator("AletsNet")
					->setLastModifiedBy("AletsNet")
					->setTitle("Triton - Clientes")
					->setSubject("Clientes")
					->setDescription("Clientes")
					->setKeywords("Triton, TPV, TPV 2017")
					->setCategory("El sistema 2017");
		
		// Agregar Informacion
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A1', '#')
					->setCellValue('B1', 'RFC')
					->setCellValue('C1', 'Nombre')
					->setCellValue('D1', 'Teléfono')
					->setCellValue('E1', 'Dirección')
					->setCellValue('F1', 'Saldo')
					->setCellValue('G1', 'Tope de Credito');
		$a = 1;
		//Prado::log('-'.TVarDumper::dump($tabla,1),TLogger::NOTICE,$this->PagePath);
		foreach($tabla as $i => $row){
			$a ++;
			$saldo = 0;
			//$tiempovisita = $this->User->tiempotrascurrio($row->fecha_cita,($row->fecha_atencion != '' ? $row->fecha_atencion : date('Y-m-d H:i:s')));
			//Prado::log('-'.TVarDumper::dump($tiempovisita,1),TLogger::NOTICE,$this->PagePath);
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A'.$a, $a-1)
						->setCellValue('B'.$a, $row->rfc)
						->setCellValue('C'.$a, $row->nombre)
						->setCellValue('D'.$a, $row->telefono)
						->setCellValue('E'.$a, $row->direccion)
						->setCellValue('F'.$a, $saldo)
						->setCellValue('G'.$a, $row->credito);
		}
		// Renombrar Hoja
		$objPHPExcel->getActiveSheet()->setTitle('Clientes');
		
		// Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
		$objPHPExcel->setActiveSheetIndex(0);
		
		$fecha = date("d.m.y H:i:s");
		//$pdf->Output('Reporte'.$fecha.'.pdf', 'I');
		// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="ListaUsuarios'.$fecha.'.xlsx"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		
		Prado::log("[".$this->User->idusuarios.
                       "][IP:".
                       $_SERVER['REMOTE_ADDR'].
                       "][Mostrar pagína XLS]",
                       TLogger::NOTICE,
                       $this->Page->PagePath);
		
		exit(0);
	}
    
}