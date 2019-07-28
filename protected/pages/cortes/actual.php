<?php
class actual extends TPage
{
	public $i = 0;
	public $x = 0;
	public $perimiso_actualizar = false;
	public $total_venta = 0;
	
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
			$this->MostrarCorteActual();
        }
    }
	
	public function MostrarCorteActual(){
		$id_corte = 0;
		$row_sucursal = LCtSucursales::finder()->find("id_sucursales = ? ", $this->User->idsucursales);
		if($row_sucursal instanceof LCtSucursales){
			//Recupera el corte activo
			$row_corte = array();
			if($row_sucursal->corte_compartido == 1){
				$row_corte = LMsCortes::finder()->find(" estatus = ? AND id_sucursal = ? ", array(1, $this->User->idsucursales));
			}else{
				$row_corte = LMsCortes::finder()->find(" estatus = ? AND id_sucursal = ? AND id_usuarios = ?", array(1, $this->User->idsucursales, $this->User->idusuarios));
			}
			if(!$row_corte instanceof LMsCortes){
				$row_corte = new LMsCortes;
				$row_corte->estatus        = 1;
				$row_corte->id_sucursal    = $this->User->idsucursales;
				$row_corte->id_usuarios    = $this->User->idusuarios;
				$row_corte->fecha_inicio   = date("Y-m-d H:i:s");
				$row_corte->fecha_registro = date("Y-m-d H:i:s");
				$row_corte->save();
			}
			
			$id_corte = $row_corte->id_cortes;
			$this->lid_cortes->Text   = $id_corte;
			$this->id_cortes->value   = $id_corte;
			//$this->lFechaInicio->Text = $this->User->fecha($row_corte->fecha_inicio);
			//$this->lFechaFinal->Text  = $this->User->tiempopasado($row_corte->fecha_inicio);
			$this->lInicioCaja->Text  = "$ " . number_format($row_corte->inicio_caja,2);
			
			//ventas
			$rows_ventas = LMsVentas::finder()->findAll(" id_cortes = ? AND estatus = ?", array($id_corte,3));
			$total = 0;
			$fecha_apertura = "";
			$fecha_final = "";
			$cEfectivo     = 0;
			$cTarjeta      = 0;
			$cCheque       = 0;
			$cTrasferencia = 0;
			$cOtro         = 0;
			foreach($rows_ventas as $index => $value){
				//if($value->fecha_termina != ""){
					$fecha = strtotime($value->fecha_termina);
					$fecha_apertura = ($fecha_apertura == "" ? $fecha : $fecha_apertura);
					$fecha_final = $fecha;
				//}
				switch($value->modo_pago){
					case 1:
						$cEfectivo += $value->total;
						break;
					case 2:
						$cTarjeta += $value->total;
						break;
					case 3:
						$cCheque += $value->total;
						break;
					case 4:
						$cTrasferencia += $value->total;
						break;
					case 5:
						$cOtro += $value->total;
						break;
				}
				
				$total = $total + $value->total;
			}
			$row_corte->ventas_realizadas = $total;
			$this->lVentasRealizadas->Text = "$ " . number_format($row_corte->ventas_realizadas,2);
			//Fechas
			$fecha_apertura = ($fecha_apertura == ""?strtotime('now'):$fecha_apertura);
			$this->lFechaInicio->Text = date("d/m/Y h:i a",$fecha_apertura);
			$this->lFechaFinal->Text  = $this->User->tiempopasado(date("Y-m-d H:i:s",$fecha_apertura));
			//Gastos
			$rows_retiros = LCtCorteRetiros::finder()->findAll(" id_cortes = ? AND borrado = 0 AND tipo = 1", array($id_corte));
			$retiros = 0;
			foreach($rows_retiros as $indexg => $valueg){
				$retiros = $retiros + $valueg->monto;
			}
			$this->SinDatos0->Visible = !(count($rows_retiros) > 0);
			$this->RpListaRetiros->DataSource = $rows_retiros;
			$this->RpListaRetiros->dataBind();
			$row_corte->gastos_realizados = $retiros;
			$this->lGastosRealizados->Text = "$ " . number_format($row_corte->gastos_realizados,2);
			
			//entradas adicionales
			$rows_entradas = LCtCorteRetiros::finder()->findAll(" id_cortes = ? AND borrado = 0 AND tipo = 2", array($id_corte));
			$entradas = 0;
			foreach($rows_entradas as $indexe => $valuee){
				$entradas = $entradas + $valuee->monto;
			}
			$this->SinDatos5->Visible = !(count($rows_entradas) > 0);
			$this->RpListaEntradas->DataSource = $rows_entradas;
			$this->RpListaEntradas->dataBind();
			$row_corte->entradas_adicionales = $entradas;
			$this->lEntradasAdicionales->Text = "$ " . number_format($row_corte->entradas_adicionales,2);
			
			//Creditos otorgados
			$rows_ventas = LMsVentas::finder()->findAll(" id_cortes = ? AND estatus = ?", array($id_corte,5));
			$totalc = 0;
			foreach($rows_ventas as $indexc => $valuec){
				$totalc = $totalc + $value->total;
			}
			$row_corte->creditos = $totalc;
			$this->lCreditosRealizados->Text = "$ " . number_format($row_corte->creditos,2);
			
			//Pagos a creditos
			$aportado = $this->Application->Modules['query']->Client->queryForObject("vwCreditos_recaudado",array('idcorte' => $id_corte));
			$row_corte->aportado_creditos = $aportado;
			$this->lPagosCredito->Text  = "$ " . number_format($row_corte->aportado_creditos,2);
			
			//efectivo
			if($row_corte->efectivo == 0){
				$row_corte->efectivo     = $cEfectivo;
			}
			$row_corte->tarjeta      = $cTarjeta;
			$row_corte->cheque       = $cCheque;
			$row_corte->trasferencia = $cTrasferencia;
			$row_corte->otro         = $cOtro;
			
			$this->lEfectivo->Text     = number_format($row_corte->efectivo,2);
			$this->lTarjeta->Text      = "$ " . number_format($cTarjeta,2);
			$this->lCheque->Text       = "$ " . number_format($cCheque,2);
			$this->lTrasferencia->Text = "$ " . number_format($cTrasferencia,2);
			$this->lOtros->Text        = "$ " . number_format($cOtro,2);
			
			//$this->lEfectivo->Text    = $row_corte->efectivo;
			$row_corte->total        = $row_corte->inicio_caja + $row_corte->ventas_realizadas + $row_corte->aportado_creditos + $row_corte->entradas_adicionales - $row_corte->gastos_realizados;
			$this->lTotal->Text       = "$ " . number_format($row_corte->total,2);
			$row_corte->diferencia_efectivo    = $row_corte->efectivo - $row_corte->total;
			$this->lDiferencia->Text = "$ " . number_format($row_corte->diferencia_efectivo ,2);
			$this->total->value = $row_corte->total;
			//$row_corte->diferencia    = $row_corte->total - ($row_corte->efectivo + $row_corte->bauchers + $row_corte->vales);
			//$this->lDiferencia->Text  = $row_corte->diferencia;
			
			//$row_corte->encaja = $row_corte->total - $row_corte->retiro_deposito;
			$this->lEnCaja->Text       = number_format($row_corte->encaja);
			$this->lRetiroDeposito->Text   = "$ " . number_format($row_corte->retiro_deposito,2);
			
			$this->lid_cortes->text = $id_corte;
			$this->id_cortes->value = $id_corte;
			
			$row_corte->fecha_inicio = date("Y-m-d H:i:s",$fecha_apertura);
			
			$row_corte->save();
			
			
			//Productos Vendidos
			$Parametros = array("id_corte" => $id_corte);
			$rows_listaproductos = $this->Application->Modules['query']->Client->queryForList("vwProductosVendidos",$Parametros);
			$xrows = count($rows_listaproductos);
			$this->SinDatosX->Visible = (!$xrows > 0);
			$this->RpListaProductos->DataSource = $rows_listaproductos;
			$this->RpListaProductos->dataBind();
			
			//Efectivo
			//$this->lEfectivo->Text = $total - $retiros;
			//$this->efectivo->value  = $total - $retiros;
			
			//Devoluciones
			$rows_devoluciones = LCtCorteMovimientos::finder()->findAll(" id_cortes = ? AND borrado = 0", array($id_corte));
			$arows = count($rows_devoluciones);
			$this->SinDatos1->Visible = (!$arows > 0);
			$this->RpListaDevolucion->DataSource = $rows_devoluciones;
			$this->RpListaDevolucion->dataBind();
			
			//RpListaFolios
			//$Parametros = array("id_corte" => $id_corte);
			//$rows_listafolios = $this->Application->Modules['query']->Client->queryForList("vwFoliosCortes",$Parametros);
			$rows_listafolios = LCtFoliosCreditos::finder()->findAll(" id_cortes = ? ", array($id_corte));
			$brows = count($rows_devoluciones);
			$this->SinDatos2->Visible = (!$brows > 0);
			$this->RpListaFolios->DataSource = $rows_listafolios;
			$this->RpListaFolios->dataBind();
			
			//Ventas canceladas
			$rows_ventas = LMsVentas::finder()->findAll(" estatus = 4 and id_cortes = ? ", array($id_corte));
			$crows = count($rows_ventas);
			$this->SinDatos3->Visible = (!$crows > 0);
			$this->RpListaVentas->DataSource = $rows_ventas;
			$this->RpListaVentas->dataBind();
			
			//Ventas canceladas
			$rows_departamentos = $rows_listaproductos = $this->Application->Modules['query']->Client->queryForList("vwDepartamentosVendidos",["idcorte" => $id_corte]);
			$drows = count($rows_departamentos);
			$this->SinDatosD->Visible = (!$drows > 0);
			$this->rpDepartamentos->DataSource = $rows_departamentos;
			$this->rpDepartamentos->dataBind();
			
			//actualiza lista de inventario
			$this->ActualizarCorteInventario($this->id_cortes->value);
			
			//link
			$this->linkTicket->NavigateUrl = $this->Service->constructUrl('cortes.ticket', ["ticket" => $this->id_cortes->value , "departamentos" => $this->hdDesglose->value]);
			$this->linkInventario->NavigateUrl = $this->Service->constructUrl('inventarios.corte', ["ticket" => $this->id_cortes->value ]);
		}
	}
    
	public function ActualizarCorteInventario($id){
		$id_cortes = $id;
		$row_corte = LMsCortes::finder()->find(" id_cortes = ? ", $id_cortes);
		$dbnet = $this->Application->Modules['query']->Client;
		if($row_corte instanceof LMsCortes){
			$rows_inventarios = $dbnet->queryForList("vwListaInventario");
			foreach($rows_inventarios as $index_inventario => $row_inventario){
				$inicia = 0;
				$abasto = 0;
				$mermas = 0;
				$ventas = 0;
				$actual = 0;
				//total de ventas
				$params = ["idcorte" => $id_cortes,
						   "idinventario" => $row_inventario->id_inventarios];
				$rows_listaproductos = $dbnet->queryForObject("vwInventarioCorte",$params);
				if(count($rows_listaproductos) > 0){
					$ventas = $rows_listaproductos['cantidad'];
				}
				//movimientos
				$params = ["idcorte" => $id_cortes,
						   "idinventario" => $row_inventario->id_inventarios];
				$rows_listaMovimientos = $dbnet->queryForList("vwMovimientosInventariosCorte",$params);
				if(count($rows_listaMovimientos)){
					foreach($rows_listaMovimientos as $imovimiento => $rowMovimiento){
						$obj = (object) $rowMovimiento;
						switch($obj->tipo_movimiento){
							case 1:
								$abasto = $obj->cantidad;
								break;
							case 2:
								$mermas = $obj->cantidad;
								break;
							case 3:
								
								break;
						}
					}
				}
				
				//$rows_ventasdetalle = LCtVentasDetalle::finder()->findAll(" id_ventas = ? ",array($id_ventas));
				//actualizar lista de inventario del corte
				$row_invcorte = LCtCorteInventario::finder()->find(" id_cortes = ? AND id_inventario = ? ", [$id_cortes, $row_inventario->id_inventarios]);
				if($row_invcorte instanceof LCtCorteInventario){
					//$row_invcorte->inicia = $inicia;
					$row_invcorte->abasto = $abasto;
					$row_invcorte->merma = $mermas;
					$row_invcorte->venta = $ventas;
					$row_invcorte->termina = $row_inventario->stock;
					$row_invcorte->save();
				}else{
					$inicia = $row_inventario->stock;// + ($abasto + $ventas - $mermas);
					$row_invcorte = new LCtCorteInventario;
					$row_invcorte->id_cortes = $id_cortes;
					$row_invcorte->id_inventario = $row_inventario->id_inventarios;
					$row_invcorte->id_productos = $row_inventario->id_productos;
					$row_invcorte->inicia = $inicia;
					$row_invcorte->abasto = $abasto;
					$row_invcorte->merma = $mermas;
					$row_invcorte->venta = $ventas;
					$row_invcorte->termina = $row_inventario->stock;
					$row_invcorte->save();
				}
			}
		}
	}
	
	public function btnGuardar_OnClick($sender, $param){
		if($this->IsValid){
			$row_corte = LMsCortes::finder()->find(" estatus = 1 AND id_cortes = ? ", $this->id_cortes->value);
			
			if($row_corte instanceof LMsCortes){
				$id_corte = $row_corte->id_cortes;
				$efectivo = $row_corte->inicio_caja + $row_corte->aportado_creditos + $row_corte->efectivo + $row_corte->entradas_adicionales - $row_corte->gastos_realizados;
				$row_corte->encaja          = (double) $this->lEnCaja->Text;
				$row_corte->total = (double) $efectivo;
				$row_corte->observaciones   = $this->txtObservaciones->Text;
				$row_corte->efectivo = (double) $this->lEfectivo->Text;
				$row_corte->retiro_deposito = $row_corte->efectivo - $row_corte->encaja;
				$row_corte->diferencia_efectivo = $row_corte->efectivo - $row_corte->total;
				$row_corte->save();
				$this->MostrarCorteActual();
			}else{
				Prado::log('Esta tratado de guardar en un corte no abierto',TLogger::NOTICE,$this->PagePath);
			}
		}
	}
	
	public function btnAnadirGasto_OnClick($sender, $param){
		if($this->IsValid){
			//$this->id_cortes->value
			$rows_retiros = new LCtCorteRetiros;
			$rows_retiros->id_cortes = $this->id_cortes->value;
			$rows_retiros->tipo = 1;
			$rows_retiros->descripcion = $this->txtDescripcion->Text;
			$rows_retiros->monto = $this->txtRetiros->Text;
			$rows_retiros->save();
			
			$this->txtDescripcion->Text = "";
			$this->txtRetiros->Text = "";
			
			/*$rows_retiros = LCtCorteRetiros::finder()->findAll(" id_cortes = ? AND borrado = 0 AND tipo = 1", $this->id_cortes->value);
			$retiros = 0;
			foreach($rows_retiros as $indexg => $valueg){
				$retiros = $retiros + $valueg->monto;
			}
			$this->SinDatos0->Visible = !(count($rows_retiros) > 0);
			$this->RpListaRetiros->DataSource = $rows_retiros;
			$this->RpListaRetiros->dataBind();
			$this->lGastosRealizados->value = $retiros;*/
			$this->MostrarCorteActual();
		}
	}
	
	public function btnQuitarGasto_onClick($sender, $param){
		$item = $sender->namingContainer;
		$keyid = $this->RpListaRetiros->DataKeys[$item->itemIndex];
		$row = LCtCorteRetiros::finder()->find(" id_corte_retiros = ? ",$keyid);
		if($row instanceof LCtCorteRetiros){
			$row->delete();
		}
		$this->MostrarCorteActual();
		//Prado::log(TVarDumper::dump($row,2),TLogger::NOTICE,$this->PagePath);
	}
	
	public function btnAnadirEntradas_OnClick($sender, $param){
		if($this->IsValid){
			//$this->id_cortes->value
			$rows_retiros = new LCtCorteRetiros;
			$rows_retiros->id_cortes   = $this->id_cortes->value;
			$rows_retiros->descripcion = $this->txtDescripcion2->Text;
			$rows_retiros->monto       = $this->txtEntradas2->Text;
			$rows_retiros->tipo        = 2;
			$rows_retiros->save();
			
			$this->txtDescripcion2->Text = "";
			$this->txtEntradas2->Text = "";
			
			/*$rows_retiros = LCtCorteRetiros::finder()->findAll(" id_cortes = ? AND borrado = 0 AND tipo = 2", $this->id_cortes->value);
			$this->SinDatos5->Visible = !(count($rows_retiros) > 0);
			$this->RpListaEntradas->DataSource = $rows_retiros;
			$this->RpListaEntradas->dataBind();*/
			
			$this->MostrarCorteActual();
		}
	}
	
	public function btnQuitarEntrada_onClick($sender, $param){
		$item = $sender->namingContainer;
		$keyid = $this->RpListaEntradas->DataKeys[$item->itemIndex];
		$row = LCtCorteRetiros::finder()->find(" id_corte_retiros = ? ",$keyid);
		if($row instanceof LCtCorteRetiros){
			$row->delete();
		}
		$this->MostrarCorteActual();
		//Prado::log(TVarDumper::dump($row,2),TLogger::NOTICE,$this->PagePath);
	}
	
	public function btnTerminar_OnClick($sender, $param){
		$row_corte = LMsCortes::finder()->find(" id_cortes = ?", array($this->id_cortes->value));
		if($row_corte instanceof LMsCortes){
			//$id_corte = $row_corte->id_cortes;
			$row_corte->fecha_final = date('Y-m-d H:i:s');
			$id_corte = $row_corte->id_cortes;
			$efectivo = $row_corte->inicio_caja + $row_corte->aportado_creditos + $row_corte->efectivo + $row_corte->entradas_adicionales - $row_corte->gastos_realizados;
			$row_corte->encaja          = (double) $this->lEnCaja->Text;
			$row_corte->retiro_deposito = $efectivo - $row_corte->encaja;
			$row_corte->observaciones   = $this->txtObservaciones->Text;
			$row_corte->estatus         = 2;
			$row_corte->save();
			
			$row_corte_new = new LMsCortes;
			$row_corte_new->fecha_registro = date('Y-m-d H:i:s');
			$row_corte_new->fecha_inicio   = date('Y-m-d H:i:s');
			$row_corte_new->id_sucursal    = $this->User->idsucursales;
			$row_corte_new->estatus        = 1;
			$row_corte_new->inicio_caja    = (double) $this->lEnCaja->Text;
			$row_corte_new->id_usuarios    = $this->User->idusuarios;
			$row_corte_new->save();
			//$this->lMensajeCorte->Text = '<div class="callout callout-success"><h5><i class="icon fa fa-check"></i> Corte terminado correctamente</h5><script>$("#'.$this->linkTicket->ClientID.'")[0].click();</script></div>';
			$this->MostrarCorteActual();
			
			//$url = $this->Service->constructUrl('cortes.actual');
            //$this->Response->redirect($url);
		}
	}
	
	
    public function RpListaDevolucion_DataBound($sender, $param){
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
			$row = $item->Data;
			if($row instanceof LCtFoliosCreditos ){
				//$this->j ++;
				$item->caduca->Text = $this->User->tiempotrascurrio(date('Y-m-d'),$row->fecha_caducidad);
			}
		}
    }
	
    public function RpListaProductos_DataBound($sender, $param){
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
			$row = $item->Data;
			if(count($row) > 0){
				$this->x ++;
				$item->lnumero->Text = $this->x;
			}
		}
    }
	//
	public function RpListaFolios_DataBound($sender, $param){
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
			$row = $item->Data;
			if($row instanceof LCtFoliosCreditos){
				$caduca = $this->User->tiempotrascurrio(date('Y-m-d'),$row->fecha_caducidad);
				$item->caduca->Text = $caduca;
				
				$folio_estatus = '';
				$catalogo5 = LBsCatalogosGenericos::finder()->find(" catalogo = ? AND valor = ?", array(5, $row->estatus));
				if($catalogo5 instanceof LBsCatalogosGenericos){
					$folio_estatus = '<label class="'.$catalogo5->cssclass.'"><i class="'.$catalogo5->icon.'"></i> '. $catalogo5->opcion.'</label>';
				}else{
					$folio_estatus = '';
				}
				$item->estatus->Text = $folio_estatus;
				//$this->x ++;
				//$item->lnumero->Text = $this->x;
			}
		}
    }
	
	public function rpDepartamentos_DataBound($sender, $param){
		$item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
			$row = (object) $item->Data;
			if(isset($row->folio)){
				$item->lFolio->Text = $row->folio;
				$item->lNombre->Text = $row->nombre;
				$item->lUnidades->Text = $row->unidades;
				$item->lMonto->Text = "$ " . number_format($row->monto,2);
			}
		}
	}
	
	public function lDesglose_onChange($sender, $param){
		$l = "";
		$list = $this->rpDepartamentos->items;
		$keys = $this->rpDepartamentos->DataKeys;
		foreach($list as $i => $element){
			if($element->lDesglose->checked)
				$l .= ($l != "" ? ",":"") . $keys[$element->itemIndex];
		}
		$this->hdDesglose->value = $l;
		$this->linkTicket->NavigateUrl = $this->Service->constructUrl('cortes.ticket', ["ticket" => $this->id_cortes->value , "departamentos" => $this->hdDesglose->value]);
	}
	
}