<?php
class allticket extends TPage
{
	public $i = 0;
    public $j = 0;
	public $perimiso_actualizar = false;
	public $total_venta = 0;
	
	public function onInit($param){
		$this->master->titulo->Text = "Ticket's";
		$this->master->subtitulo->Text = "Movimieto de mercancia vendida";
        $this->title = "Ventas - Ticket's";
	}
	
    public function onLoad($param)
    {
		$v = $this->User->ServicioActivo("12");
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
			
			$this->btnActivarFolio->visible = false;
        }
    }
    
    public function btnBuscar_OnClick($sender, $param){
        $id_ventas = $this->txtFolio->Text;
        $row_venta = LMsVentas::finder()->find(" id_ventas = ?", array($id_ventas));
		if($row_venta instanceof LMsVentas){
			$this->lCliente->Text    = $row_venta->ms_clientes->nombre;
			$this->lTotal->Text      = "$ ".number_format($row_venta->total, 2);
			$this->lDescuento->Text  = round($row_venta->descuento * 100)." %";
			$this->id_clientes->value= $row_venta->id_clientes;
			$this->id_ventas->value  = $row_venta->id_ventas;
			$this->lfolioventa->Text = $row_venta->id_ventas;
            $this->lMesanje->Text    = '';
            $this->lid_cortes->Text  = $row_venta->id_cortes;
            $fecha                   = ($row_venta->fecha_termina != '' ? $row_venta->fecha_termina : $row_venta->fecha_inicio);
            $this->lTiempo->Text     = $this->User->tiempopasado($fecha);
            $catalogo3 = LBsCatalogosGenericos::finder()->find(" catalogo = ? AND valor = ?", array(3, $row_venta->estatus));
            $this->lestatus->Text    = '<label class="'.$catalogo3->cssclass.'"><i class="'.$catalogo3->icon.'"></i> '. $catalogo3->opcion.'</label>';
			$this->linkTicket->NavigateUrl = $this->Service->constructUrl('ventas.ticketfolio', array("ticket" => $row_venta->id_ventas));
		}else{
            $this->lMesanje->Text    = '<div class="callout callout-warning"><h5><i class="icon fa fa-warning"></i> No se encontro el folio</h5> </div>';
            $this->lCliente->Text    = "";
			$this->lTotal->Text      = "";
			$this->lDescuento->Text  = "";
			$this->id_clientes->value= "";
			$this->id_ventas->value  = "";
			$this->lfolioventa->Text = "";
            $this->lid_cortes->Text  = "";
            $this->lTiempo->Text     = "";
            $this->lestatus->Text    = "";
        }
        $rows_ventasdetalle = LCtVentasDetalle::finder()->findAll(" id_ventas = ? AND borrado = 0",$id_ventas);
		$crows = count($rows_ventasdetalle);
		$this->ListaBasia->Visible = (!$crows > 0);
		$this->lnproductos->Text = $crows;
		$this->RpListaCompra->DataSource = $rows_ventasdetalle;
        $this->RpListaCompra->dataBind();
        //
        $rows_CortesMovimientos = LCtCorteMovimientos::finder()->findAll(" id_ventas = ? AND borrado = 0",$id_ventas);
		$arows = count($rows_CortesMovimientos);
		$this->SinDatos1->Visible = (!$arows > 0);
		$this->RpListaDevolucion->DataSource = $rows_CortesMovimientos;
        $this->RpListaDevolucion->dataBind();
        
        //
        $rows_folioscreditos = LCtFoliosCreditos::finder()->find(" id_ventas = ? AND borrado = 0",$id_ventas);
        if($rows_folioscreditos instanceof LCtFoliosCreditos){
            $this->id_folios_creditos->text = $rows_folioscreditos->id_folios_creditos;
            $this->lsaldo->text             = "$ ".number_format($rows_folioscreditos->monto,2);
			$catalogo5 = LBsCatalogosGenericos::finder()->find(" catalogo = ? AND valor = ?", array(5, $rows_folioscreditos->estatus));
			if($catalogo5 instanceof LBsCatalogosGenericos){
				$this->folio_estatus->Text = '<label class="'.$catalogo5->cssclass.'"><i class="'.$catalogo5->icon.'"></i> '. $catalogo5->opcion.'</label>';
			}else{
				$this->folio_estatus->Text = '';
			}
			//$this->lsaldo->text             = "$ ".number_format($rows_folioscreditos->monto,2);
			$this->btnActivarFolio->visible = ($rows_folioscreditos->estatus == 1);
        }else{
			$this->id_folios_creditos->text = "";
            $this->lsaldo->text             = "$ -";
			$this->folio_estatus->Text      = '';
			$this->btnActivarFolio->visible = false;
		}
    }
    
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
                
                if($row->estatus > 1){
                    $catalogo3 = LBsCatalogosGenericos::finder()->find(" catalogo = ? AND valor = ?", array(4, $row->estatus));
                    $item->lestatus->text = '<label class="'.$catalogo3->cssclass.'"><i class="'.$catalogo3->icon.'"></i> '. $catalogo3->opcion.'</label>';
                }
                $valido = ($row->ms_ventas->estatus == 4 || $row->ms_ventas->estatus == 2) && $row->ms_ventas->ms_cortes->estatus == 1 ;
                $js = 'if(!confirm(\'¿Confirme la cancelación de esta venta '.$row->ms_ventas->id_ventas.'?\')) return false;';
                $item->btnDevolver->Attributes->onclick = (!$valido?$js:'');
                $item->btnDevolver->enabled = !$valido;
                $item->btnDevolver->visible  = ($row->estatus == 1);
				
				if($row->id_inventarios == 1){
					$item->btnDevolver->Attributes->onclick = '';
					$item->btnDevolver->enabled = false;
					$item->btnDevolver->visible  = false;
				}
				//Prado::log(TVarDumper::dump($row,1),TLogger::NOTICE,$this->PagePath);
			}
		}
		if($item->ItemType === 'Footer'){
			$item->total->Text  = "$ ".number_format($this->total_venta,2);
			$this->lTotal->Text = "$ ".number_format($this->total_venta,2);
		}
    }
    
    public function RpListaDevolucion_DataBound($sender, $param){
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
			$row = $item->Data;
			if($row instanceof LCtFoliosCreditos ){
				$this->j ++;
				$item->lnumero->Text = $this->j;
			}
		}
    }
    
    public function btnDevolver_OnClick($sender, $param){
        $item = $sender->namingContainer;
		$keyid = $this->RpListaCompra->DataKeys[$item->itemIndex];
		$row = LCtVentasDetalle::finder()->find(" borrado = 0 AND id_ventas_detalle = ? ", $keyid);
        if($row instanceof LCtVentasDetalle){
            if($row->estatus == 1){
                $id_ventas = $row->id_ventas;
				$row_corte = $this->User->idCorte();
				$id_cortes = $row_corte->id_cortes;
                $row->estatus = 2;
                $row->save();
                //inventario
                $inventario = $row->ms_inventarios;
                
                //Actualiza el inventario
                $inventario->stock = $inventario->stock + $row->cantidad;
                $inventario->save();
                
                //baja de producto
                $corte_movimientos = LCtCorteMovimientos::finder()->find(" borrado = 0 AND id_ventas = ? AND id_ventasdetalle = ? ", array($id_ventas, $row->id_ventas_detalle));
                if(!$corte_movimientos instanceof LCtCorteMovimientos){
                    $corte_movimientos = new LCtCorteMovimientos;
                    //$corte_movimientos->id_corte_movimientos
                    $corte_movimientos->id_cortes        = $id_cortes;
                    $corte_movimientos->id_ventas        = $row->id_ventas;
                    $corte_movimientos->id_ventasdetalle = $row->id_ventas_detalle;
                    //$corte_movimientos->borrado        = 0
                    $corte_movimientos->fecha_movimiento = date('Y-m-d H:i:s');
                    $corte_movimientos->save();
                }
                
                //folio de credito ct_folios_creditos
                $folio_credito = LCtFoliosCreditos::finder()->find(" borrado = 0 AND id_ventas = ? ", $id_ventas);
                if($folio_credito instanceof LCtFoliosCreditos){
                    $montoa = $folio_credito->monto;
                    $montob = $row->precio_vendido * $row->cantidad;
                    $folio_credito->monto       = $montoa + $montob;
                    $folio_credito->estatus     = 1;
					$folio_credito->id_usuarios = $this->User->idusuarios;
                    $folio_credito->save();
                }else{
                    $folio_credito = new LCtFoliosCreditos;
                    //$corte_movimientos->id_corte_movimientos
                    $dias = $this->Application->Parameters["folio_caducidad"];
                    $caducidad = strtotime(date('Y-m-d')." +".$dias." day");
                    
                    $folio_credito->id_ventas       = $id_ventas;
					$folio_credito->id_cortes       = $id_cortes;
                    $folio_credito->fecha_inicia    = date('Y-m-d');
                    $folio_credito->fecha_caducidad = date('Y-m-d', $caducidad);
                    $folio_credito->fecha_creado    = date('Y-m-d H:i:s');
                    $folio_credito->monto           = $row->precio_vendido * $row->cantidad;
                    $folio_credito->estatus         = 1;
					$folio_credito->id_usuarios     = $this->User->idusuarios;
                    $folio_credito->save();
                }
                //Prado::log(TVarDumper::dump($folio_credito,1),TLogger::NOTICE,$this->PagePath);
            }
            
            $this->btnBuscar_OnClick(null, null);
		}
    }
	
	public function btnActivarFolio_OnClick($sender, $param){
		$id_ventas = $this->txtFolio->Text;
		$folio_credito = LCtFoliosCreditos::finder()->find(" borrado = 0 AND id_ventas = ? ", $id_ventas);
		if($folio_credito instanceof LCtFoliosCreditos){
			$folio_credito->estatus     = 2;
			$folio_credito->id_usuarios = $this->User->idusuarios;
			$folio_credito->save();
		}
		//imprimir
		$this->orden_js->Text = '<script>$("#'.$this->linkTicket->ClientID.'")[0].click();</script>';
		
		$this->btnBuscar_OnClick(null, null);
		
		$this->btnActivarFolio->visible = false;
	}
}