<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class historial extends TPage
{
	public $i = 0;
    public $total_venta = 0;
    public $catalogo3 = array();
	
	public function onInit($param){
		$this->master->titulo->Text = "Ventas realizadas";
		$this->master->subtitulo->Text = "Historial de ventas";
        $this->title = "Ventas realizadas - Ventas";
	}
    
    public function onLoad($param)
    {
		$v = $this->User->ServicioActivo("5");
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
            //$this->Formulario->visible = false;
            
            $this->fecha_inicio->text = date("d/m/Y");
            $this->fecha_final->text = date("d/m/Y");
            
            $this->dgTabla->VirtualItemCount = $this->getRowCount();
            $this->dgTabla->DataSource=$this->getDataRows(0,$this->dgTabla->PageSize);
            $this->dgTabla->dataBind();
        }
    }
    
    public function btnBuscar_OnClick($sender, $param){
        $this->dgTabla->VirtualItemCount = $this->getRowCount();
        $this->dgTabla->DataSource=$this->getDataRows(0,$this->dgTabla->PageSize);
        $this->dgTabla->dataBind();
    }
    
    /***
     *Boton para mostrar la venta
    */
    public function btnView_OnClick($sender, $param){
        //$this->ModalVenta->open();
        $item = $sender->namingContainer;
		$keyid = $this->dgTabla->DataKeys[$item->itemIndex];
        $row_venta = LMsVentas::finder()->find(" id_ventas = ?", array($keyid));
        $time = strtotime($row_venta->fecha_termina);
		$this->lidventa->Text   = $keyid;
        $this->id_ventas->value = $keyid;
        $this->lCliente->Text   = $row_venta->ms_clientes->nombre;
        $this->lTotal->Text     = "$ ".number_format($row_venta->total, 2);
		$d = round($row_venta->descuento * 100);
        $this->lDescuento->Text = ($d >= 0 ? $d : 0)." %";
        $this->lUser->Text      = $row_venta->bs_usuarios->user;
        $this->lFecha->Text     = date("d/M/y h:i a",$time);
        $catalogo3 = LBsCatalogosGenericos::finder()->find(" catalogo = ? AND valor = ?", array(3, $row_venta->estatus));
        $this->lEstatus->cssclass = $catalogo3->cssclass;
        $this->lEstatus->Text = '<i class="'.$catalogo3->icon.'"></i> '. $catalogo3->opcion;
        $this->linkTicket->NavigateUrl = $this->Service->constructUrl('ventas.ticket', array("ticket" => $row_venta->id_ventas, "status" => "2"));
        $activo = $row_venta->ms_cortes->estatus;
		//forma de pago
		$formapago = "";
		$catalogoFormaPago = LBsCatalogosGenericos::finder()->find(" catalogo = ? AND valor = ?", array(9, $row_venta->modo_pago));
		$formapago = "<label style=\"font-size: 14px;\" class=\"label bg-green\"><i class=\"".$catalogoFormaPago->icon."\"></i> ".$catalogoFormaPago->opcion."</label>";
		$formapago .= ($activo==1 ? "<a id='editar' class=\"btn btn-primary\" onclick='$(\"#tpFormaPago\").show();$(\"#lFormaPago\").hide();'><i class=\"fa fa-edit\"></i></a>":"");
		$formapago .= " <script> formapago({$row_venta->modo_pago}) </script>	";
		$this->lFormapago->Text = $formapago;
		$this->btnBorrar_Ventas->enabled = ($activo==1);
        $rows_ventasdetalle = LCtVentasDetalle::finder()->findAll(" id_ventas = ? AND borrado = 0",$keyid);
		//$crows = count($rows_ventasdetalle);
		//$this->lnproductos->Text = $crows;
		$this->RpListaCompra->DataSource = $rows_ventasdetalle;
        $this->RpListaCompra->dataBind();
		//$this->inactividad->value = 0;
        //Prado::log(TVarDumper::dump($row_venta,1),TLogger::NOTICE,$this->PagePath);
    }
    
    public function btnBorrar_Ventas_OnClick($sender, $param){
        $item = $sender->namingContainer;
		$keyid = $this->dgTabla->DataKeys[$item->itemIndex];
        $row_venta = LMsVentas::finder()->find(" id_ventas = ?", array($keyid));
        if($row_venta instanceof LMsVentas){
			$row_Lista = LCtVentasDetalle::finder()->findAll(" id_ventas = ?", array($keyid));
			foreach($row_Lista as $row){
				$actual = $row->ms_inventarios->stock;
				$row->ms_inventarios->stock = $actual + $row->cantidad;
				$row->ms_inventarios->save();
				//Prado::log(TVarDumper::dump($row->cantidad,2),TLogger::NOTICE,$this->PagePath);
			}
            $row_venta->estatus = 4;
            $row_venta->save();
        }
		$this->dgTabla->VirtualItemCount = $this->getRowCount();
		$this->dgTabla->DataSource=$this->getDataRows(0,$this->dgTabla->PageSize);
		$this->dgTabla->dataBind();
    }
    
	public function btnGuardarModoPago_OnClick($sender, $param){
		$row_venta = LMsVentas::finder()->find(" id_ventas = ?", $this->id_ventas->value);
        if($row_venta instanceof LMsVentas){
            $row_venta->modo_pago = $this->formadepago->value;
            $row_venta->save();
			//forma de pago
			$formapago = "";
			$catalogoFormaPago = LBsCatalogosGenericos::finder()->find(" catalogo = ? AND valor = ?", array(9, $row_venta->modo_pago));
			$formapago = "<label style=\"font-size: 14px;\" class=\"label bg-green\"><i class=\"".$catalogoFormaPago->icon."\"></i> ".$catalogoFormaPago->opcion."</label> <a id='editar' class=\"btn btn-primary\" onclick='$(\"#tpFormaPago\").show();$(\"#lFormaPago\").hide();'><i class=\"fa fa-edit\"></i></a> ";
			$this->lFormapago->Text = $formapago;
        }
		$this->dgTabla->VirtualItemCount = $this->getRowCount();
		$this->dgTabla->DataSource=$this->getDataRows(0,$this->dgTabla->PageSize);
		$this->dgTabla->dataBind();
	}
	
	public function btnCerrar_OnClick($sender, $param){
		$this->ModalVenta->close();
	}
	
    /**
     *Tabla de Busqueda
    */
    
    protected function getDataRows($offset,$rows)
    {
        $idsucursales = $this->User->idsucursales;
		$Parametros = array("fechainicio" => $this->User->fecha($this->fecha_inicio->text) . " 00:00:00",
							"fechafinal"  => $this->User->fecha($this->fecha_final->text)  . " 23:59:59",
							"tbuscar"     => "%".$this->txtBuscar->Text."%",
                            "idsucursales"=> $idsucursales,
							"rows"        => $rows,
							"offset"      => $offset);
		$tabla = $this->Application->Modules['query']->Client->queryForList("vwVentas",$Parametros);
		return $tabla;
    }
    
    protected function getRowCount()
    {
        $idsucursales = $this->User->idsucursales;
        $Parametros = array("fechainicio" => $this->User->fecha($this->fecha_inicio->text) . " 00:00:00",
							"fechafinal"  => $this->User->fecha($this->fecha_final->text)  . " 23:59:59",
							"tbuscar"     => "%".$this->txtBuscar->Text."%",
                            "idsucursales"=> $idsucursales);
		
        $param_pdf = array("fechainicio" => $this->User->fecha($this->fecha_inicio->text),
							"fechafinal"  => $this->User->fecha($this->fecha_final->text),
							"tbuscar"     => $this->txtBuscar->Text);
        
		$var = $this->Application->Modules['query']->Client->queryForObject("vwVentas_count",$Parametros);
        //$var = LMsVentas::finder()->count($ct_buscar);
		$this->linkPdf->NavigateUrl = $this->Service->constructUrl('ventas.historialpdf',$param_pdf);
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
			if($row instanceof LMsVentas ){
				//$this->i ++;
                $time = strtotime($row->fecha_termina);
				//$item->rowI->lNumero->Text = $this->i;
				$item->rowFecha->lFecha->Value = $row->fecha_termina;
				//$descuento = round($row->descuento * 100);
                $item->rowDescuento->lDescuento->Value = $row->subtotal - $row->total;
				$item->rowVenta->lVenta->Value         = $row->subtotal;
                $item->rowTotalVenta->lTotal->Value    = $row->total;
				//Forma de pago
				$catFormaPago = LBsCatalogosGenericos::finder()->find(" catalogo = ? AND valor = ?", array(9, $row->modo_pago));
				$item->rowFormaPago->lFormaPago->Text = '<i class="'.$catFormaPago->icon.'"></i> '.$catFormaPago->opcion;
				//Estado de la venta
                $catalogo3 = LBsCatalogosGenericos::finder()->find(" catalogo = ? AND valor = ?", array(3, $row->estatus));
                $item->rowEstatus->lestatus->text = '<label class="'.$catalogo3->cssclass.'"><i class="'.$catalogo3->icon.'"></i> '. $catalogo3->opcion.'</label>';
                // cancelar venta
				$valido = ($row->estatus == 4 || $row->estatus == 2); //&& $row->ms_cortes->estatus == 1);
                $js = 'if(!confirm(\'¿Confirme la cancelación de esta venta '.$row->id_ventas.' - '.date('d/M/y h:i a',$time).' ?\')) return false;';
                $item->rowBotonos->btnBorrar_Ventas->Attributes->onclick = (!$valido?$js:'');
                $item->rowBotonos->btnBorrar_Ventas->enabled = ($row->ms_cortes->estatus==1? !$valido : false );
                
				$item->rowBotonos->linkTicket->NavigateUrl = $this->Service->constructUrl('ventas.ticket', array("ticket" => $row->id_ventas, "status" => "2"));
                $item->rowBotonos->linkTicket->enabled = !$valido;
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
    
    /*
     *Lista de productos
    **/
    public function RpListaCompra_DataBound($sender, $param){
		$item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
			$row = $item->Data;
			if($row instanceof LCtVentasDetalle ){
				$this->i ++;
				$item->lnumero->Text = $this->i;
				$item->precio->Text = "$ ".number_format($row->precio_vendido,2);
				$descuento = ($row->descuento < 1 && $row->descuento > 0 ? $row->descuento : 0) * 100;
				$item->descuento->Text = round($descuento) . " %";
				$subtotal = $row->precio_vendido * $row->cantidad; // - ($row->precio_publico * $row->descuento))* $row->cantidad;
				$this->total_venta += $subtotal;
				$item->subtotal->Text = "$ ".number_format($subtotal,2);
				//Prado::log(TVarDumper::dump($row,1),TLogger::NOTICE,$this->PagePath);
			}
		}
		if($item->ItemType === 'Footer'){
			$row_folios = LCtFoliosVentaCobrado::finder()->find(" id_ventas = ? AND borrado = 0", array($this->id_ventas->value));
			if($row_folios instanceof LCtFoliosVentaCobrado){
				$row_folio = $row_folios->ct_folios_creditos;
				$this->lExtra->Text = '<label class="label label-success" style="font-size: 15px;"><i class="fa fa-ticket"></i> Credito es de $ ' . number_format($row_folio->monto, 2) .'</label>';
				//Prado::log(TVarDumper::dump($tiempo,1),TLogger::NOTICE,$this->PagePath);
				$this->total_venta = $this->total_venta - $row_folio->monto;
			}
			$item->total->Text  = "$ ".number_format($this->total_venta,2);
		}
	}
    
    /**
     *Exportar XLS
    **/
    public function btnExportarXLS_OnClick($sender, $param){
        $idsucursales = $this->User->idsucursales;
		$Parametros = array("fechainicio" => $this->User->fecha($this->fecha_inicio->text) . " 00:00:00",
							"fechafinal"  => $this->User->fecha($this->fecha_final->text)  . " 23:59:59",
							"tbuscar"     => "%".$this->txtBuscar->Text."%",
                            "idsucursales"=> $idsucursales);
		$tabla = $this->Application->Modules['query']->Client->queryForList("vwVentas_exportar",$Parametros);
		$rows = count($tabla);
		
		Prado::log('-'.TVarDumper::dump($rows,1),TLogger::NOTICE,$this->PagePath);
		
		//Prado::using('Application.modulos.PHPExcel');
		// Create new PHPExcel object
		//echo date('H:i:s') , " Create new PHPExcel object" ;
		$objPHPExcel = new Spreadsheet();
		
		// Establecer propiedades
		$fecha_t = date("d m Y H i s");
		$objPHPExcel->getProperties()
					->setCreator("AletsNet")
					->setLastModifiedBy("AletsNet")
					->setTitle("Triton - Ventas")
					->setSubject("Ventas Realizadas")
					->setDescription("Ventas Realizadas")
					->setKeywords("Triton, TPV, TPV 2017")
					->setCategory("El sistema 2017");
		
		// Agregar Informacion
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A1', 'Folio')
					->setCellValue('B1', 'Corte')
					->setCellValue('C1', 'Atendio')
					->setCellValue('D1', 'Cliente')
					->setCellValue('E1', 'Fecha')
					->setCellValue('F1', 'Descuento')
					->setCellValue('G1', 'Total')
					->setCellValue('H1', 'Estatus');
		$a = 1;
		//Prado::log('-'.TVarDumper::dump($tabla,1),TLogger::NOTICE,$this->PagePath);
		foreach($tabla as $i => $row){
			$a ++;
            $time = strtotime($row->fecha_termina);
			$catalogo3 = LBsCatalogosGenericos::finder()->find(" catalogo = ? AND valor = ?", array(3, $row->estatus));
            
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A'.$a, $row->id_ventas)
						->setCellValue('B'.$a, $row->id_cortes)
						->setCellValue('C'.$a, $row->bs_usuarios->nombre)
						->setCellValue('D'.$a, $row->ms_clientes->nombre)
						->setCellValue('E'.$a, date('d/m/y h:i a',$time))
						->setCellValue('F'.$a, $row->descuento)
						->setCellValue('G'.$a, $row->total)
						->setCellValue('H'.$a, $catalogo3->opcion);
		}
		// Renombrar Hoja
		$objPHPExcel->getActiveSheet()->setTitle('Ventas');
		
		// Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
		$objPHPExcel->setActiveSheetIndex(0);
		
		$fecha = date("d.m.y H:i:s");
		$namefile = 'Ventas'.$fecha.'.xlsx';
		//$pdf->Output('Reporte'.$fecha.'.pdf', 'I');
		// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$namefile.'"');
		header('Cache-Control: max-age=0');
		$writer = new Xlsx($objPHPExcel);
		$writer->save('php://output');
		
		Prado::log("[".$this->User->idusuarios.
                       "][IP:".
                       $_SERVER['REMOTE_ADDR'].
                       "][Mostrar pagína XLS]",
                       TLogger::NOTICE,
                       $this->Page->PagePath);
		
		exit(0);
    }
}