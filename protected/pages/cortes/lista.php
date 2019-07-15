<?php
class lista extends TPage
{
	public $i = 0;
	
	public function onInit($param){
		$this->master->titulo->Text = "Corte";
		$this->master->subtitulo->Text = "Corte actual";
        $this->title = "Corte - Actual";
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
            $inicio = "01/".date('m/Y');
            $this->fecha_inicio->Text = $inicio;
            $this->fecha_final->TimeStamp = strtotime(" 1 months  ",$this->fecha_inicio->TimeStamp);
            
			$this->dgTabla->VirtualItemCount = $this->getRowCount();
            $this->dgTabla->DataSource=$this->getDataRows(0,$this->dgTabla->PageSize);
            $this->dgTabla->dataBind();
        }
    }
    
	public function btnBuscar_OnClick($sender, $param){
		$this->getRows(false,($this->dgTabla->CurrentPageIndex*$this->dgTabla->PageSize));
	}
	
    public function btnView_OnClick($sender, $param){
        $this->ModalVenta->open();
        $item = $sender->namingContainer;
		$keyid = $this->dgTabla->DataKeys[$item->itemIndex];
        //$row_venta = LMsVentas::finder()->find(" id_ventas = ?", array($keyid));
        
    }
    
    protected function getDataRows($offset,$rows)
    {
        $idsucursales = $this->User->idsucursales;
        $where = " ";
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Parameters[':id_sucursales'] = $idsucursales;
        $where .= " id_sucursal = :id_sucursales ";
        
        $fecha_inicio = date('Y-m-d',$this->fecha_inicio->TimeStamp) . " 00:00:00";
        $fecha_final  = date('Y-m-d',$this->fecha_final->TimeStamp)  . " 23:59:59";
		$ct_buscar->Parameters[':fecha_inicio'] = $fecha_inicio;
        $ct_buscar->Parameters[':fecha_final']  = $fecha_final;
        $where .= " AND (fecha_inicio BETWEEN :fecha_inicio AND :fecha_final ";
        $where .= " OR   fecha_final  BETWEEN :fecha_inicio AND :fecha_final )";
		$ct_buscar->Condition = $where;
        
		$ct_buscar->OrdersBy['id_cortes'] = 'desc';
        $ct_buscar->Limit = $rows;
        $ct_buscar->Offset = $offset;
        
		$tabla = LMsCortes::finder()->findAll($ct_buscar);
        //Prado::log($fecha_inicio . " - " . $fecha_final,TLogger::NOTICE,$this->PagePath);
        //Prado::log(TVarDumper::dump($ct_buscar,2),TLogger::NOTICE,$this->PagePath);
		return $tabla;
    }
    
    protected function getRowCount()
    {
        $idsucursales = $this->User->idsucursales;
        $where = " ";
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Parameters[':id_sucursales'] = $idsucursales;
        $where .= " id_sucursal = :id_sucursales ";
        
        $fecha_inicio = date('Y-m-d',$this->fecha_inicio->TimeStamp) . " 00:00:00";
        $fecha_final  = date('Y-m-d',$this->fecha_final->TimeStamp)  . " 23:59:59";
		$ct_buscar->Parameters[':fecha_inicio'] = $fecha_inicio;
        $ct_buscar->Parameters[':fecha_final']  = $fecha_final;
        $where .= " AND (fecha_inicio BETWEEN :fecha_inicio AND :fecha_final ";
        $where .= " OR   fecha_final  BETWEEN :fecha_inicio AND :fecha_final )";
        
		$ct_buscar->Condition = $where;
		
        $param_pdf = array("fechainicio" => date('Y-m-d',$this->fecha_inicio->TimeStamp),
						   "fechafinal"  => date('Y-m-d',$this->fecha_final->TimeStamp));
        
        $var = LMsCortes::finder()->count($ct_buscar);
		$this->linkPdf->NavigateUrl = $this->Service->constructUrl('cortes.listapdf',$param_pdf);
		
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
			if($row instanceof LMsCortes ){
				//$this->i ++;
                //$time = strtotime($row->fecha_inicio);
				//$item->rowI->lNumero->Text = $this->i;
				$item->rowFecha->lFecha->Value = ($row->fecha_final == NULL ? $row->fecha_inicio: $row->fecha_final);
                $item->rowDuracion->lDuracion->Text = $this->User->tiempotrascurrio($row->fecha_inicio,($row->fecha_final != ""? $row->fecha_final : "now"));
                $item->rowGastos->lGastos->Text     = "$ ".number_format($row->gastos_realizados,2);
                $item->rowTotal->lTotal->Text       = "$ ".number_format($row->total,2);
                $item->rowCreditos->lCreditos->Text = "$ ".number_format($row->creditos,2);
				$item->rowInicioCaja->lInicioCaja->Text = "$ ".number_format($row->inicio_caja,2);
                $item->rowRetiro->lRetiro->Text     = "$ ".number_format($row->retiro_deposito,2);
                
                $catalogo6 = LBsCatalogosGenericos::finder()->find(" catalogo = ? AND valor = ?", array(6, $row->estatus));
				if($catalogo6 instanceof LBsCatalogosGenericos)
					$item->rowEstatus->lestatus->text = '<label class="'.$catalogo6->cssclass.'"><i class="'.$catalogo6->icon.'"></i> '. $catalogo6->opcion.'</label>';
                
				
				$item->rowBotonos->linkTicket->NavigateUrl = $this->Service->constructUrl('cortes.ticket', array("ticket" => $row->id_cortes, "status" => "2"));
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
    
	public function btnExportarXLS_OnClick($sender,$param){
		$idsucursales = $this->User->idsucursales;
        $where = " ";
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Parameters[':id_sucursales'] = $idsucursales;
        $where .= " id_sucursal = :id_sucursales ";
        
        $fecha_inicio = date('Y-m-d',$this->fecha_inicio->TimeStamp) . " 00:00:00";
        $fecha_final  = date('Y-m-d',$this->fecha_final->TimeStamp)  . " 23:59:59";
		$ct_buscar->Parameters[':fecha_inicio'] = $fecha_inicio;
        $ct_buscar->Parameters[':fecha_final']  = $fecha_final;
        $where .= " AND (fecha_inicio BETWEEN :fecha_inicio AND :fecha_final ";
        $where .= " OR   fecha_final  BETWEEN :fecha_inicio AND :fecha_final )";
		$ct_buscar->Condition = $where;
        
		$ct_buscar->OrdersBy['id_cortes'] = 'asc';
        
		$tabla = LMsCortes::finder()->findAll($ct_buscar);
		//$tabla = $this->Application->Modules['query']->Client->queryForList("vwInventarios_exportar",$Parametros);
		$rows = count($tabla);
		
		$catalogo6 = LBsCatalogosGenericos::finder()->findAll(" catalogo = ? ", 6);
		$estatus = array();
		foreach($catalogo6 as $ii => $jj){ $estatus[$jj->valor] = $jj; }
		
		Prado::using('Application.modulos.PHPExcel');
		// Create new PHPExcel object
		//echo date('H:i:s') , " Create new PHPExcel object" ;
		$objPHPExcel = new PHPExcel();
		
		// Establecer propiedades
		$fecha_t = date("d m Y H i s");
		$objPHPExcel->getProperties()
					->setCreator("AletsNet")
					->setLastModifiedBy("AletsNet")
					->setTitle("Triton - Cortes")
					->setSubject("Cortes")
					->setDescription("Cortes")
					->setKeywords("Triton, TPV, TPV 2017")
					->setCategory("El sistema 2017");
		
		// Agregar Informacion
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A1', '#')
					->setCellValue('B1', 'Usuario')
					->setCellValue('C1', 'Fecha')
					->setCellValue('D1', 'Duración')
					->setCellValue('E1', 'Gasto realizados')
					->setCellValue('F1', 'Total')
					->setCellValue('G1', 'Creditos realizados')
					->setCellValue('H1', 'Inicio caja')
					->setCellValue('I1', 'Retiro a depositar')
					->setCellValue('J1', 'Estatus');
		$a = 1;
		//Prado::log('-'.TVarDumper::dump($tabla,1),TLogger::NOTICE,$this->PagePath);
		foreach($tabla as $i => $row){
			$a ++;
			$fecha = strtotime($row->fecha_inicio);
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A'.$a, $a-1)
						->setCellValue('B'.$a, $row->bs_usuarios->nombre)
						->setCellValue('C'.$a, date('d/m/Y h:i a',$fecha))
						->setCellValue('D'.$a, $this->User->tiempotrascurrio($row->fecha_inicio,($row->fecha_final != ""? $row->fecha_final : "now")))
						->setCellValue('E'.$a, "$ ".number_format($row->gastos_realizados,2))
						->setCellValue('F'.$a, "$ ".number_format($row->total,2))
						->setCellValue('G'.$a, "$ ".number_format($row->creditos,2))
						->setCellValue('H'.$a, "$ ".number_format($row->inicio_caja,2))
						->setCellValue('I'.$a, "$ ".number_format($row->retiro_deposito,2))
						->setCellValue('J'.$a, $estatus[$row->estatus]);
		}
		// Renombrar Hoja
		$objPHPExcel->getActiveSheet()->setTitle('Cortes');
		
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