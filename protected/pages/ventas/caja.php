<?php
class caja extends TPage
{
	public $i = 0;
	public $perimiso_actualizar = false;
	public $total_venta = 0.0 ;
	
	public function onInit($param){
		$this->master->titulo->Text = "Ventas";
		$this->master->subtitulo->Text = "Terminal de venta";
        $this->title = "Ventas - Terminal de venta";
	}
	
    public function onLoad($param)
    {
		$v = $this->User->ServicioActivo("4");
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
			//cliente 1
			$this->id_clientes->value = 1;
			
			//bodega
			$this->cmdBodega->DataSource = LCtBodegas::finder()->findAll(" id_sucursales = ? AND borrado = 0",$this->User->idsucursales);
            $this->cmdBodega->dataBind();
			$this->cmdBodega->SelectedIndex = 0;
			
			//codigos
			$this->RpListaFunciones->DataSource = LCtCodigosreservados::finder()->findAll("borrado = 0 and estatus = 1");
            $this->RpListaFunciones->dataBind();
			
			//formas de pago
			#$this->rpFormaPago->DataSource = LBsCatalogosGenericos::finder()->findAll("activo = 1 and catalogo = 9");
            #$this->rpFormaPago->dataBind();
			
			//corte actual
            $this->IniciarTerminalVenta();
			
        }
    }
	
	public function RpListaFunciones_DataBound($sender, $param){
		$item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
			$row = $item->Data;
			if($row instanceof LCtCodigosreservados ){
				$item->lcodigo->Text = $row->codigo;
				$item->ldescripcion->Text = $row->nombre;
			}
		}
	}
	
	public function btnFunsion_OnClick($sender, $param){
		$item = $sender->namingContainer;
		$keyid = $this->RpListaFunciones->DataKeys[$item->itemIndex];
		$row = LCtCodigosreservados::finder()->find(" id_codigosreservados = ? ", $keyid);
		if($row instanceof LCtCodigosreservados){
			$funcion = $row->funcion;
			$this->{$funcion}(null,null);
		}
	}
	
	public function btnActualizar_OnClick($sender, $param){
		$this->IniciarTerminalVenta();
	}
	
	public function btnTerminarVenta_OnClick($sender, $param){
		$row = LMsVentas::finder()->find(" id_ventas = ? ",array($this->id_ventas->value));
		if($row instanceof LMsVentas){
			$efectivo = $this->params->value;
			$venta    = (float) 0.0;
			$subtotal = (float) 0.0;
			$total    = (float) 0.0;
			$descuento= 0;
			$rows_count = 0;
			$rows_ventasdetalle = LCtVentasDetalle::finder()->findAll(" id_ventas = ? ",array($this->id_ventas->value));
			$rows_count = count($rows_ventasdetalle);
			if($rows_count > 0){
				foreach($rows_ventasdetalle as $i => $row_ventasdetalle){
					$inventario = $row_ventasdetalle->ms_inventarios;
					if($inventario instanceof LMsInventarios){
						if($inventario->ms_productos->tipo == 1){
							$inventario->stock = $inventario->stock - $row_ventasdetalle->cantidad;
							$inventario->save();
						}
						
						if($inventario->id_inventarios == 1){
							$row_creditos_abonos = new LCtCreditosAbonos;
							
							$row_creditos_abonos->id_clientes = $this->id_clientes->value;
							$row_creditos_abonos->id_ventas   = $this->id_ventas->value;
							$row_creditos_abonos->id_usuarios = $this->User->idusuarios;
							$row_creditos_abonos->fecha_abono = date("Y/m/d H:i:s");
							$row_creditos_abonos->monto       = $row_ventasdetalle->precio_vendido;
							$row_creditos_abonos->save();
						}
					}
					$Rtotal   = (float) $row_ventasdetalle->cantidad * $row_ventasdetalle->precio_publico;
					$stotal   = (float) $row_ventasdetalle->cantidad * $row_ventasdetalle->precio_vendido;
					$total    = (float) $total + $stotal;
					$subtotal = (float) $subtotal + $Rtotal;
					$venta    = (float) $venta + $stotal;
				}
				
				//folio credito
				$row_folios = LCtFoliosVentaCobrado::finder()->find(" id_ventas = ? AND borrado = 0", array($this->id_ventas->value));
				if($row_folios instanceof LCtFoliosVentaCobrado){
					$row_folio = $row_folios->ct_folios_creditos;
					$this->lExtra->Text = '';
					$total = $total - $row_folio->monto;
				}
				
				if($subtotal > 0){
					$descuento = (($subtotal - $venta)/$subtotal);
				}
					
				if($efectivo == ""){
					$efectivo = $total;
				}
				
				$cambio = $efectivo - $total;
				//Prado::log(TVarDumper::dump($rows,1),TLogger::NOTICE,$this->PagePath);
				//Prado::log($cambio." = ".$efectivo."-".$total,TLogger::NOTICE,$this->PagePath);
				if($total >= 0){
					if($cambio > -1){
						$row->id_clientes = $this->id_clientes->value;
						$row->efectivo  = $efectivo;
						$row->descuento = $descuento;
						$row->subtotal  = $subtotal;
						$row->total     = $total;
						$row->estatus   = 3;
						$row->fecha_termina = date("Y-m-d H:i:s");
						$row->modo_pago = $this->formadepago->value;
						$row->save();
						
						$row_folios = LCtFoliosVentaCobrado::finder()->find(" id_ventas = ? AND estatus = 1 AND borrado = 0", array($this->id_ventas->value));
						if($row_folios instanceof LCtFoliosVentaCobrado){
							$row_folio = $row_folios->ct_folios_creditos;
							$row_folios->estatus = 2;
							$row_folios->save();
							$row_folio->estatus = 4;
							$row_folio->save();
						}
						
						$script = "<script>   formapago(1); ";
						if($this->ckTicketImprimir->Checked){
							$script .= '$("#'.$this->linkTicket->ClientID.'")[0].click();';
						}
						$script .= " </script>";
						
						$this->id_clientes->value = 1;
						
						$this->lMesanje->Text = '<div class="callout callout-success"><h5><i class="icon fa fa-check"></i> Venta Realizada <b>Cambio: '.'$ '.number_format($cambio,2).'</b></h5>'.$script.'</div>';
					}else{
						$this->lMesanje->Text = '<div class="callout callout-danger"><h5><i class="icon fa fa-warning"></i> Venta no se puede realizar</h5> <h3><b>Cambio: '.'$ '.number_format($cambio,2).'</b></h3></div>';
					}
				}else{
					$this->lMesanje->Text = '<div class="callout callout-danger"><h5><i class="icon fa fa-warning"></i> Venta no se puede realizar</h5> <h3><b>Cuenta : '.'$ '.number_format($total,2).'</b></h3></div>';
				}
			}else{
				$this->lMesanje->Text = '<div class="callout callout-warning"><h5><i class="icon fa fa-warning"></i> No se puede completar la venta por que no hay nada en lista</h5> </div>';
			}
			$this->IniciarTerminalVenta();
		}
	}
	
	
	public function btnVentaEspera_OnClick($sender, $param){
		
		$row_venta = LMsVentas::finder()->find(" id_ventas = ?", array($this->id_ventas->value));
		if($row_venta instanceof LMsVentas){
			$rows_ventasdetalle = LCtVentasDetalle::finder()->findAll(" id_ventas = ? AND borrado = 0",$row_venta->id_ventas);
			$crows = count($rows_ventasdetalle);
			if($crows > 0){
				$row_venta->estatus = 2;
				$row_venta->save();
			}else{
				$this->lMesanje->Text = '<div class="callout callout-warning"><h5><i class="icon fa fa-warning"></i> Es necesario almenos un articulo</h5> </div>';
			}
		}
		
		$this->IniciarTerminalVenta();
	}
	
	public function btnVentaRecuperar_OnClick($sender, $param){
		$index = ($this->params->value!=""?($this->params->value -1):0);
		$id_corte = 0;
		$row_corte = LMsCortes::finder()->find(" estatus = ? AND id_sucursal = ?", array(1, $this->User->idsucursales));
		if($row_corte instanceof LMsCortes){
			$id_corte = $row_corte->id_cortes;
		}
		$rows = LMsVentas::finder()->count(" id_cortes = ? AND estatus = ?", array($id_corte,2));
		//Prado::log(TVarDumper::dump($rows,1),TLogger::NOTICE,$this->PagePath);
		if($this->params->value <= $rows){
			$keyid = $this->RpListaVentas->DataKeys[$index];
			$row_venta = LMsVentas::finder()->find(" id_ventas = ?", array($keyid));
			if($row_venta instanceof LMsVentas){
				$row_venta->estatus = 1;
				$row_venta->save();
			}
		}
		$this->IniciarTerminalVenta();
	}
	
	/**
	 *Buscar Clientes
	*/
	public function btnNuevoCliente_OnClick($sender, $param){
		$script = "<script> $('#modalNuevoCliente').modal('show');  </script>";
		$this->lMesanje->Text = $script;
	}
	
	public function btnClientSave_OnClick($sender, $param){
		$row = LMsClientes::finder()->find(" borrado = 0 AND telefono = ? ", $this->txtTelefono->Text);
        if(!($row instanceof LMsClientes)){
			$row = new LMsClientes;
			$row->telefono = $this->txtTelefono->Text;
			$row->nombre = $this->txtNombre->Text;
			$row->direccion = $this->txtDireccion->Text;
			
			$row->save();
			$this->lCliente->Text    = $row->nombre;
			$this->lDireccion->Text = $row->direccion;
			$this->id_clientes->value= $row->id_clientes;
		}
		$script = "<script> $('#modalNuevoCliente').modal('hide');  </script>";
		$this->lMesanje->Text = $script;
	}
	
	public function btnBuscarClientes_OnClick($sender, $param){
		$this->txtCliente->Text = $this->params->value;
		//ModalClientes
		//$this->ModalClientes->Open();
		$script = "<script> $('#ModalClientes').modal('show');  </script>";
		$this->lMesanje->Text = $script;
		$where = " borrado = 0 AND tipo_cliente = 2 AND (telefono like :nombre OR nombre like :nombre OR id_clientes like :nombre )";
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Parameters[':nombre'] = "%".trim($this->txtCliente->Text)."%";
		$ct_buscar->Condition = $where;
		$ct_buscar->OrdersBy['nombre'] = 'asc';
        $ct_buscar->Limit = 10;
		$this->dgClientes->DataSource = LMsClientes::finder()->findAll($ct_buscar);
        $this->dgClientes->dataBind();
	}
	
	public function dgClientes_OnItemCreated($sender, $param){
		$item=$param->Item;
		//Prado::log(TVarDumper::dump($item->ItemType,2),TLogger::NOTICE,$this->PagePath);
		switch($item->ItemType){
            //case 'Header':
            case 'Item':
            case 'AlternatingItem':
                $row = $item->Data;
                if($row instanceof LMsClientes){
					$item->rowSaldo->lSaldo->Text              = number_format($this->User->SaldoActual($row->id_clientes),2) ;
					$item->rowCreditoPermitido->lCredito->Text = number_format($row->credito,2);
				}
                //Prado::log(TVarDumper::dump($row,2),TLogger::NOTICE,$this->PagePath);
                break;
            default:
                //Prado::log(TVarDumper::dump($item->ItemType,2),TLogger::NOTICE,$this->PagePath);
                break;
		}
	}
	
	public function JbtnBuscarClienteNombre_OnClick($sender, $param){
		$where = " borrado = 0 AND (telefono like :nombre OR nombre like :nombre OR id_clientes like :nombre )";
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Parameters[':nombre'] = "%".trim($this->txtCliente->Text)."%";
		$ct_buscar->Condition = $where;
		$ct_buscar->OrdersBy['nombre'] = 'asc';
        $ct_buscar->Limit = 10;
		$this->dgClientes->DataSource = LMsClientes::finder()->findAll($ct_buscar);
        $this->dgClientes->dataBind();
	}
	
	public function btnClickCliente_OnClick($sender, $param){
		$item = $sender->namingContainer;
		$keyid = $this->dgClientes->DataKeys[$item->itemIndex];
		$row = LMsClientes::finder()->find(" borrado = 0 AND id_clientes = ? ", $keyid);
        if($row instanceof LMsClientes){
			$this->lCliente->Text    = $row->nombre;
			$this->id_clientes->value= $row->id_clientes;
			$this->lDireccion->Text = $row->direccion;
		}
		//$this->linecomando->focus();
		//$this->ListaActual($this->id_ventas->value);
		//$this->ModalClientes->Close();
		$script = "<script> $('#ModalClientes').modal('hide');  </script>";
		$this->lMesanje->Text = $script;
	}
	
	public function JbtnCerrarClienteNombre_OnClick($sender, $param){
		$this->ModalClientes->Close();
	}
	
	/**
	 *Procesamiento de la terminal
	*/
	public function btnProcesar_OnClick($sender, $param){
		$this->lMesanje->Text = '';
		$textcomando = trim($this->linecomando->Text);
		if($textcomando != ""){
			$arraycomandos = explode(" ", $textcomando);
			$countcomandos = count($arraycomandos);
			switch($countcomandos){
				case 1:
					$row_codigo = $this->BuscarFuncion($arraycomandos[0]);
					if($row_codigo instanceof LCtCodigosreservados){
						$funcion = $row_codigo->funcion;
						$this->{$funcion}(null,null);
					}else{
						$this->AgregarListaVenta($arraycomandos[0]);
					}
					break;
				case 2:
					$row_codigo = $this->BuscarFuncion($arraycomandos[0]);
					if($row_codigo instanceof LCtCodigosreservados){
						$this->params->value = $arraycomandos[1];
						$funcion = $row_codigo->funcion;
						$this->{$funcion}(null,null);
					}else{
						$this->AgregarListaVenta($arraycomandos[1],$arraycomandos[0]);
					}
					break;
				default:
					$row_codigo = $this->BuscarFuncion($arraycomandos[0]);
					$texto = "";
					$i = 0;
					foreach($arraycomandos as $i => $v){
						if($i > 0){
							$texto .= " ".$v;
						}
						$i++;
					}
					if($row_codigo instanceof LCtCodigosreservados){
						$this->params->value = $texto;
						$funcion = $row_codigo->funcion;
						$this->{$funcion}(null,null);
					}else{
						$this->AgregarListaVenta($arraycomandos[1],$arraycomandos[0]);
					}
					break;
			}
			//$this->IniciarTerminalVenta();
		}
		//$this->ListaActual($this->id_ventas->value);
		$this->IniciarTerminalVenta();
		$this->params->value = "";
		$this->linecomando->Text = "";
		//Prado::log(TVarDumper::dump($countcomandos,1),TLogger::NOTICE,$this->PagePath);
	}
	
	public function IniciarTerminalVenta(){
		$id_corte = 0;
		//$row_corte = LMsCortes::finder()->find(" estatus = ? AND id_sucursal = ? ", array(1, $this->User->idsucursales));
		//ticket
		$row_ticket = LCtSucursales::finder()->find(" id_sucursales = ? ", array($this->User->idsucursales));
		if($row_ticket instanceof LCtSucursales){
			$this->ckTicketImprimir->Checked = $row_ticket->ticket_automatico;
			$compatirCorte = $row_ticket->corte_compartido;
		}
		$row_corte = array();
		if(!$compatirCorte ){
			$row_corte = LMsCortes::finder()->find(" estatus = ? AND id_sucursal = ? AND id_usuarios = ? ", array(1, $this->User->idsucursales, $this->User->idusuarios));
			$this->lbModalidad->Text = "Modo corte por usuario";
		}else{
			$row_corte = LMsCortes::finder()->find(" estatus = ? AND id_sucursal = ? ", array(1, $this->User->idsucursales));
			$this->lbModalidad->Text = "Modo corte compartido";
		}
		
		if($row_corte instanceof LMsCortes){
			$id_corte = $row_corte->id_cortes;
		}else{
			$row_corte = new LMsCortes;
			$row_corte->id_sucursal = $this->User->idsucursales;
			$row_corte->id_usuarios = $this->User->idusuarios;
			$row_corte->fecha_registro = date("Y-m-d H:i:s");
			$row_corte->fecha_inicio = date("Y-m-d H:i:s");
			//$row_corte->fecha_final;
			
			$row_corte->save();
			$id_corte = $row_corte->id_cortes;
		}
		$this->lid_cortes->text = $id_corte;
		$this->id_cortes->value = $id_corte;
		
		//ventas
		$rows_ventas = LMsVentas::finder()->findAll(" id_cortes = ? AND estatus = ?", array($id_corte,2));
		$this->ListaVentaBacia->Visible = (!$rows_ventas > 0);
		$this->RpListaVentas->DataSource = $rows_ventas;
		$this->RpListaVentas->dataBind();
		
		$row_venta = LMsVentas::finder()->find(" id_cortes = ? AND estatus = ? AND id_usuarios = ?", array($id_corte,1,$this->User->idusuarios));
		if($row_venta instanceof LMsVentas){
			$this->lCliente->Text    = $row_venta->ms_clientes->nombre;
			$this->lTotal->Text      = "$ ".number_format($row_venta->total, 2);
			$this->lDescuento->Text  = ($row_venta->descuento * 100)." %";
			$this->id_clientes->value= $row_venta->id_clientes;
			$this->id_ventas->value  = $row_venta->id_ventas;
			$this->lfolioventa->Text = $row_venta->id_ventas;
		}else{
			$row_venta = new LMsVentas;
			$row_venta->id_cortes = $id_corte;
			$row_venta->estatus = 1;
			$row_venta->id_usuarios = $this->User->idusuarios;
			$row_venta->id_clientes = 1;
			$row_venta->fecha_inicio = date("Y-m-d H:i:s");
			$row_venta->save();
			$this->lCliente->Text    = $row_venta->ms_clientes->nombre;
			$this->lTotal->Text      = "$ ".number_format($row_venta->total, 2);
			$this->lDescuento->Text  = ($row_venta->descuento * 100)." %";
			$this->id_clientes->value= $row_venta->id_clientes;
			$this->id_ventas->value  = $row_venta->id_ventas;
			$this->lfolioventa->Text = $row_venta->id_ventas;
		}
		
		//lista de venta
		$this->ListaActual($this->id_ventas->value);
		
		//ticket's
		$this->linkTicket->NavigateUrl = $this->Service->constructUrl('ventas.ticket', array("ticket" => $this->id_ventas->value));
		
		//folio de credito
		/*$row_folios = LCtFoliosVentaCobrado::finder()->find(" id_ventas = ? AND estatus = 1 AND borrado = 0", array($this->id_ventas->value));
		if($row_folios instanceof LCtFoliosVentaCobrado){
			$row_folio = $row_folios->ct_folios_creditos;
			$tiempo = 0;
			$tiempo = strtotime(date('Y-m-d')) - strtotime($row_folio->fecha_caducidad);
			$this->lExtra->Text = '<label class="label label-success" style="font-size: 15px;"><i class="fa fa-ticket"></i> Credito es de $ ' . number_format($row_folio->monto, 2) .'</label>';
			//Prado::log(TVarDumper::dump($tiempo,1),TLogger::NOTICE,$this->PagePath);
		}*/
		$this->linecomando->focus();
	}
	
	public function BuscarFuncion($codigo = ""){
		$where = " borrado = 0 AND estatus = 1 AND codigo = :codigo";
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Parameters[':codigo'] = $codigo;
		$ct_buscar->Condition = $where;
        $row = LCtCodigosreservados::finder()->find($ct_buscar);
		//Prado::log(TVarDumper::dump($row,1),TLogger::NOTICE,$this->PagePath);
		return $row;
	}
	
	public function AgregarListaVenta($codigo = "", $cantidad = 1){
		//Prado::log(TVarDumper::dump($this->id_ventas->value,1),TLogger::NOTICE,$this->PagePath);
		$where = " borrado = 0 AND codigo = :codigo ";
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Parameters[':codigo'] = $codigo;
		$ct_buscar->Condition = $where;
		$row = LMsProductos::finder()->find($ct_buscar);
        if($row instanceof LMsProductos){
			$row_inventario = LMsInventarios::finder()->find(" id_productos = ? AND id_bodegas = ? AND borrado = 0 ",array($row->id_productos, $this->cmdBodega->text));
			if($row_inventario instanceof LMsInventarios){
				$row_ventasdetalle = LCtVentasDetalle::finder()->find(" id_ventas = ? AND id_inventarios = ?",array($this->id_ventas->value,$row_inventario->id_inventarios));
				if($row_ventasdetalle instanceof LCtVentasDetalle){
					$row_ventasdetalle->cantidad = $row_ventasdetalle->cantidad + $cantidad;
					$row_ventasdetalle->save();
				}else{
					$row_ventasdetalle = new LCtVentasDetalle;
					$row_ventasdetalle->id_ventas = $this->id_ventas->value;
					$row_ventasdetalle->id_inventarios = $row_inventario->id_inventarios;
					$row_ventasdetalle->precio_publico = $row_inventario->preciopublico;
					$row_ventasdetalle->precio_adquisicion = $row_inventario->precioadquisicion;
					$row_ventasdetalle->precio_vendido = $row_inventario->preciopublico;
					$row_ventasdetalle->descuento = 0;
					$row_ventasdetalle->cantidad = $cantidad;
					$row_ventasdetalle->fecha_movimiento = date("Y-m-d H:i:s");
					//$row_ventasdetalle->borrado
					$row_ventasdetalle->save();
				}
			}else{
				$this->lMesanje->Text = '<div class="callout callout-warning"><h5><i class="icon fa fa-warning"></i> Articulo no esta en este almacen</h5> </div>';
			}
		}else{
			$this->lMesanje->Text = '<div class="callout callout-warning"><h5><i class="icon fa fa-warning"></i> Articulo no encontrado</h5> </div>';
		}
	}
	
	/**
	 *Buscar Productos
	*/
	public function btnBuscarProducto_OnClick($sender, $param){
		$this->BuscarProducto();
	}
	
	public function BuscarProducto(){
		$this->txtNombres->Text = $this->params->value;
		//$this->ModalDepartamentos->Open();
		$script = "<script> $('#modalBuscarProductos').modal('show');  </script>";
		$this->lMesanje->Text = $script;
		
		$this->dgProductos->VirtualItemCount = $this->dgProductos_RowCount();
        $this->dgProductos->DataSource=$this->dgProductos_DataRows(0,$this->dgProductos->PageSize);
        $this->dgProductos->dataBind();
	}
	
	public function JbtnBuscarProductoNombre_OnClick($sender, $param){
		$this->dgProductos->VirtualItemCount = $this->dgProductos_RowCount();
        $this->dgProductos->DataSource=$this->dgProductos_DataRows(0,$this->dgProductos->PageSize);
        $this->dgProductos->dataBind();
	}
	
	public function JbtnCerrarProductoNombre_OnClick($sender, $param){
		$this->ModalDepartamentos->close();
	}
	
	protected function dgProductos_DataRows($offset,$rows)
    {
        /*$where = " borrado = 0 AND id_productos > 1 AND ((nombre like :nombre OR codigo like :nombre ) ";
		$where .= $this->User->text_voltear($this->txtNombres->Text).")"; 
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Parameters[':nombre'] = "%".$this->txtNombres->Text."%";
		$ct_buscar->Condition = $where;
		$ct_buscar->OrdersBy['nombre'] = 'asc';
        $ct_buscar->Limit = $rows;
		$ct_buscar->offset = $offset;
		$tabla = LMsProductos::finder()->findAll($ct_buscar);*/
		$idsucursales = $this->User->idsucursales;
		$tipo = 0;
		//if($this->cmdServicios->Text != " ")
		//	$tipo = (int) $this->cmdServicios->Text;
		
        $Parametros = array("nombre"       => "%".$this->txtNombres->Text."%",
							"idbodegas"    => $this->cmdBodega->Text,
							"tipo"         => $tipo,
                            "idsucursales"  => $idsucursales,
							"rows"        => $rows,
							"offset"      => $offset);
		$tabla = $this->Application->Modules['query']->Client->queryForList("vwInventarios",$Parametros);
		return $tabla;
    }
    
    protected function dgProductos_RowCount()
    {
        /*$where = " borrado = 0 AND id_productos > 1 AND ((nombre like :nombre OR codigo like :nombre ) ";
		$where .= $this->User->text_voltear($this->txtNombres->Text).")"; 
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Parameters[':nombre'] = "%".$this->txtNombres->Text."%";
		$ct_buscar->Condition = $where;
        $var = LMsProductos::finder()->count($ct_buscar);*/
		$idsucursales = $this->User->idsucursales;
		$tipo = 0;
		
        $Parametros = array("nombre"       => "%".$this->txtNombres->Text."%",
							"idbodegas"    => $this->cmdBodega->Text,
							"tipo"         => $tipo,
                            "idsucursales" => $idsucursales);
		$var = $this->Application->Modules['query']->Client->queryForObject("vwInventarios_count",$Parametros);
        return $var;
    }
	
	public function dgProductos_getRows($deforder=true,$offset=0)
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
        $this->dgProductos->VirtualItemCount = $this->dgProductos_RowCount();
        $this->dgProductos->DataSource=$this->dgProductos_DataRows($offset,$this->dgProductos->PageSize);
        $this->dgProductos->dataBind();
    }
	
	public function dgProductos_changePage($sender,$param)
    {
        $this->dgProductos->CurrentPageIndex=$param->NewPageIndex;
        $offset=$param->NewPageIndex*$this->dgProductos->PageSize;
        $this->i = $offset;
        $this->dgProductos->DataSource=$this->dgProductos_DataRows($offset,$this->dgProductos->PageSize);
        $this->dgProductos->dataBind();
    }
	
	public function dgProductos_pagerCreated($sender, $param){
		$param->Pager->Controls->insertAt(0,'Pagina(s): ');
	}
	
	public function dgProductos_OnItemCreated($sender, $param){
		$item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
			$row = $item->Data;
			if($row instanceof LMsInventarios ){
				$item->rowPrecio->lprecio->Text = "$ ".number_format($row->preciopublico,2);
				
				$css = array(1 => "progress-bar-danger", 2 => "progress-bar-yellow", 3 => "progress-bar-success" );
				$gcss = array(1 => "bg-red", 2 => "bg-yellow", 3 => "bg-green" );
				$css_estatus = $css[3];
				$css_gestatus = $gcss[3];
				$porcentaje = round(($row->stock / $row->maximo_stock) * 100) ;
				if($porcentaje < 50){
					$css_estatus = $css[2];
					$css_gestatus = $gcss[2];
					if($porcentaje < 15){
						$css_estatus = $css[1];
						$css_gestatus = $gcss[1];
					}
				}
				
				$item->rowBart->lBartStock->Text = '<div class="progress progress-xs"><div class="progress-bar '.$css_estatus.'" style="width: '.$porcentaje.'%"></div></div>';
				$item->rowBart->lPorcentajeStock->Text = '<span class="badge '.$css_gestatus.'">'.$porcentaje.' % </span>';
			}
		}
	}
	
	public function btnClick_OnClick($sender, $param){
		$item = $sender->namingContainer;
		$keyid = $this->dgProductos->DataKeys[$item->itemIndex];
		$row = LMsProductos::finder()->find(" borrado = 0 AND id_productos = ? ", $keyid);
        if($row instanceof LMsProductos){
			$this->AgregarListaVenta($row->codigo,1);
		}
		$this->linecomando->focus();
		$this->ListaActual($this->id_ventas->value);
		//$this->ModalDepartamentos->Close();
		$script = "<script> $('#modalBuscarProductos').modal('hide');  </script>";
		$this->lMesanje->Text = $script;
	}
	
	/*
	*Lista de productos de la venta
	*/
	public function ListaActual($id_ventas){
		$rows_ventasdetalle = LCtVentasDetalle::finder()->findAll(" id_ventas = ? AND borrado = 0",$id_ventas);
		$crows = count($rows_ventasdetalle);
		$this->ListaBacia->Visible = (!$crows > 0);
		$this->lnproductos->Text = $crows;
		$this->RpListaCompra->DataSource = $rows_ventasdetalle;
        $this->RpListaCompra->dataBind();
		$this->inactividad->value = 0;
	}
	
	public function RpListaCompra_DataBound($sender, $param){
		$item=$param->Item;
		//Prado::log(TVarDumper::dump($item->ItemType,1),TLogger::NOTICE,$this->PagePath);
		if($item->ItemType === 'Header'){
			$this->total_venta = 0;
		}
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
			$row = $item->Data;
			if($row instanceof LCtVentasDetalle ){
				$this->i ++;
				$item->lnumero->Text = $this->i;
				$item->precio->Text = "$ ".number_format($row->precio_vendido,2);
				$descuento = (float) ($row->descuento < 1 && $row->descuento > 0 ? $row->descuento : 0) * 100;
				$item->descuento->Text = round($descuento) . " %";
				$subtotal = (float) $row->precio_vendido * $row->cantidad; // - ($row->precio_publico * $row->descuento))* $row->cantidad;
				$this->total_venta += (float) $subtotal;
				$item->subtotal->Text = "$ ".number_format($subtotal,2);
				//Prado::log(TVarDumper::dump($row,1) . $this->total_venta,TLogger::NOTICE,$this->PagePath);
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
			$this->lTotal->Text = "$ ".number_format($this->total_venta,2);
			
		}
	}
	
	public function btnEditar_Lista_OnClick($sender, $param){
		//$this->ModalEditarVenta->open();
		$item = $sender->namingContainer;
		$keyid = $this->RpListaCompra->DataKeys[$item->itemIndex];
		
		$row  = LCtVentasDetalle::finder()->find("borrado = 0 AND id_ventas_detalle = ?", $keyid);
		if($row instanceof LCtVentasDetalle ){
			$this->lNombreProducto->Text = $row->ms_inventarios->ms_productos->nombre;
			$this->txtCantidad->Text = $row->cantidad;
			$this->txtPrecioVendido->Text = $row->precio_vendido;
			$subtotal = $row->cantidad * $row->precio_vendido;
			//$this->lSubTotal->Text = "$ ".number_format($subtotal,2);
			$this->txtSubTotal->Text = $subtotal;
			$this->id_ventas_detalle->value = $row->id_ventas_detalle;
		}
		//Prado::log(TVarDumper::dump($keyid,1),TLogger::NOTICE,$this->PagePath);
	}
		
	public function btnBorrar_Lista_OnClick($sender, $param){
		$item = $sender->namingContainer;
		$keyid = $this->RpListaCompra->DataKeys[$item->itemIndex];
		
		$row  = LCtVentasDetalle::finder()->find("borrado = 0 AND id_ventas_detalle = ?", $keyid);
		if($row instanceof LCtVentasDetalle ){
			$row->delete();
		}
		
		$this->IniciarTerminalVenta();
	}
	
	public function JbtnEditarPrecio_OnClick($sender, $param){
		//if($this->IsValid){
			//$item = $sender->namingContainer;
			$script = ""; //"<script>   </script>";
			
			$usuario = LBsUsuarios::finder()->find(" id_usuarios = ?",$this->User->idusuarios);
			$keyid = $this->id_ventas_detalle->value;
			$row  = LCtVentasDetalle::finder()->find("borrado = 0 AND id_ventas_detalle = ?", $keyid);
			$descuento = 0;
			if($row instanceof LCtVentasDetalle ){
				$row->cantidad = $this->txtCantidad->Text;
				$row->precio_vendido = $this->txtPrecioVendido->Text;
				if($row->precio_publico > 0){
					$descuento = (($row->precio_publico - $this->txtPrecioVendido->Text )/ $row->precio_publico);
				}
				//Prado::log($descuento . " " . $usuario->descuento,TLogger::NOTICE,$this->PagePath);
				if($descuento <= $usuario->descuento){
					$row->descuento = ($descuento < 1 ? $descuento : 0);
					$row->save();
					$this->lMesanje->Text = $script .'';
					
				}else{
					$this->lMesanje->Text = $script . '<div class="callout callout-warning"><h5><i class="icon fa fa-warning"></i> No es posible porcesar el descuento es de ' . round($descuento * 100) . '% </h5> </div>';
				}
				$this->IniciarTerminalVenta();
			}
			//$this->ModalEditarVenta->close();
		//}
		//Prado::log(TVarDumper::dump($this->IsValid,1),TLogger::NOTICE,$this->PagePath);
	}
	
	public function JbtnCancelar_OnClick($sender, $param){
		$this->ModalEditarVenta->close();
	}
	
	/*
	*Lista de ventas en espera
	*/
	public function RpListaVentas_DataBound($sender, $param){
		$item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
			$row = $item->Data;
			if($row instanceof LMsVentas){
				$time = strtotime($row->fecha_inicio);
				$this->i ++;
				$item->lj->text = $this->i;
				$total = 0;
				$row_detalle = LCtVentasDetalle::finder()->findAll(" id_ventas = ?", $row->id_ventas);
				foreach($row_detalle as $detalle){
					$total += ($detalle->precio_vendido * $detalle->cantidad);
				}
				
				$item->lventas->Text = '$ ' . number_format($total,2);//date('h:i a',$time);
				$item->btnBorrar_Ventas->Attributes->onclick='if(!confirm(\'¿Confirme la cancelación de esta venta '.date('h:i a',$time).' ?\')) return false;';
				$item->linkTicket->NavigateUrl = $this->Service->constructUrl('ventas.ticket', array("ticket" => $row->id_ventas));
				//Prado::log(TVarDumper::dump($row,1),TLogger::NOTICE,$this->PagePath);
			}
		}
	}
	
	public function btnEditar_Venta_OnClick($sender,$param){
		$item = $sender->namingContainer;
		$keyid = $this->RpListaVentas->DataKeys[$item->itemIndex];		
		$row_venta = LMsVentas::finder()->find(" id_ventas = ?", array($keyid));
		if($row_venta instanceof LMsVentas){
			$row_venta->estatus = 1;
			$row_venta->save();
		}
		$this->IniciarTerminalVenta();
	}
	
	public function btnBorrar_Ventas_OnClick($sender, $param){
		$item = $sender->namingContainer;
		$keyid = $this->RpListaVentas->DataKeys[$item->itemIndex];
		$row_venta = LMsVentas::finder()->find(" id_ventas = ?", array($keyid));
		if($row_venta instanceof LMsVentas){
			$row_venta->estatus = 4;
			$row_venta->save();
		}
		$this->IniciarTerminalVenta();
	}
	
	public function btnFolioCredito_OnClick($sender, $param){
		$folio = $this->params->value;
		if($folio != ""){
			$row_folio = LCtFoliosCreditos::finder()->find(" id_ventas = ? AND estatus in (2,3) AND borrado = 0", array($folio));
			if($row_folio instanceof LCtFoliosCreditos){
				$tiempo = 0;
				$tiempo = strtotime(date('Y-m-d')) - strtotime($row_folio->fecha_caducidad);
				if($tiempo < 0){
					$this->lMesanje->Text = '';
					$this->lExtra->Text = '<label class="label label-success" style="font-size: 15px;"><i class="fa fa-ticket"></i> Credito es de $ ' . number_format($row_folio->monto, 2) .'</label>';
					$row_folios = LCtFoliosVentaCobrado::finder()->find(" id_ventas = ? AND borrado = 0", array($this->id_ventas->value));
					if(!$row_folios instanceof LCtFoliosVentaCobrado){
						$row_folio->estatus = 3;
						$row_folio->save();
						$row_folios = new LCtFoliosVentaCobrado;
						$row_folios->id_ventas          = $this->id_ventas->value;
						$row_folios->id_folios_creditos = $row_folio->id_folios_creditos;
						$row_folios->credito            = $row_folio->monto;
						$row_folios->estatus            = 1;
						$row_folios->save();
					}else{
						$this->lMesanje->Text = '<div class="callout callout-warning"><h5><i class="icon fa fa-warning"></i> Ya esta cargado</h5> </div>';
					}
				}else{
					$this->lMesanje->Text = '<div class="callout callout-warning"><h5><i class="icon fa fa-warning"></i> Folio de credito a expirado</h5> </div>';
				}
				//Prado::log(TVarDumper::dump($tiempo,1),TLogger::NOTICE,$this->PagePath);
			}else{
				$this->lMesanje->Text = '<div class="callout callout-warning"><h5><i class="icon fa fa-warning"></i> Folio de credito no valido</h5> </div>';
			}
			//$this->lExtra->Text = "Folio: ";
		}else{
			$this->lMesanje->Text = '<div class="callout callout-warning"><h5><i class="icon fa fa-warning"></i> Se necesita un numero de folio</h5> </div>';
		}
		//$this->IniciarTerminalVenta();
	}
	
	public function ckTicketImprimir_OnCheckedChanged($sender, $param){
		$row_ticket = LCtSucursales::finder()->find(" id_sucursales = ? ", array($this->User->idsucursales));
		if($row_ticket instanceof LCtSucursales){
			$row_ticket->ticket_automatico = $sender->checked;
			$row_ticket->save();
		}
	}
	
	//venta a credito
	public function btnCreditoVenta_OnClick($sender, $param){
		$this->txtAbomoCredito->Text = "";
		$this->txtClienteCredito->Text = "";
		$row = LMsVentas::finder()->find(" id_ventas = ? ",array($this->id_ventas->value));
		if($row instanceof LMsVentas){
			$row_cliente = LMsClientes::finder()->find(" id_clientes = ? ",$this->id_clientes->value);
			$efectivo = (int) $this->params->value;
			$venta    = (float) 0.0;
			$subtotal = (float) 0.0;
			$total    = (float) 0.0;
			$descuento= 0;
			$rows_count = 0;
			$rows_ventasdetalle = LCtVentasDetalle::finder()->findAll(" id_ventas = ? ",array($this->id_ventas->value));
			$rows_count = count($rows_ventasdetalle);
			if($row_cliente instanceof LMsClientes){
				if($row_cliente->tipo_cliente == 2){
					if($rows_count > 0){
						foreach($rows_ventasdetalle as $i => $row_ventasdetalle){
							$inventario = $row_ventasdetalle->ms_inventarios;
							if($inventario instanceof LMsInventarios){
								if($inventario->ms_productos->tipo == 1){
									$inventario->stock = $inventario->stock - $row_ventasdetalle->cantidad;
									$inventario->save();
								}
							}
							$Rtotal   = (float) $row_ventasdetalle->cantidad * $row_ventasdetalle->precio_publico;
							$stotal   = (float) $row_ventasdetalle->cantidad * $row_ventasdetalle->precio_vendido;
							$total    = (float) $total + $stotal;
							$subtotal = (float) $subtotal + $Rtotal;
							$venta    = (float) $venta + $stotal;
						}
						//folio credito
						$row_folios = LCtFoliosVentaCobrado::finder()->find(" id_ventas = ? AND borrado = 0", array($this->id_ventas->value));
						if($row_folios instanceof LCtFoliosVentaCobrado){
							$row_folio = $row_folios->ct_folios_creditos;
							$this->lExtra->Text = '';
							$total = $total - $row_folio->monto;
						}
						
						if($subtotal > 0){
							$descuento = (($subtotal - $venta)/$subtotal);
						}
						
						$cambio = $efectivo - $total;
						$saldo = $this->User->SaldoActual($this->id_clientes->value);
						$saldo_prelimanal = $saldo + $total;
						if($saldo_prelimanal <= $row_cliente->credito){
							//venta realizada en modo credito
							$row->id_clientes = $this->id_clientes->value;
							$row->efectivo  = $efectivo;
							$row->descuento = $descuento;
							$row->subtotal  = $subtotal;
							$row->total     = $total;
							$row->estatus   = 5;
							$row->fecha_termina = date("Y-m-d H:i:s");
							$row->save();
							
							
							$row_folios = LCtFoliosVentaCobrado::finder()->find(" id_ventas = ? AND estatus = 1 AND borrado = 0", array($this->id_ventas->value));
							if($row_folios instanceof LCtFoliosVentaCobrado){
								$row_folio = $row_folios->ct_folios_creditos;
								$row_folios->estatus = 2;
								$row_folios->save();
								$row_folio->estatus = 4;
								$row_folio->save();
							}
							
							$script = "<script>
											formapago(1); ";
							if($this->ckTicketImprimir->Checked){
								$script .= '$("#'.$this->linkTicket->ClientID.'")[0].click();';
							}
							$script .= "<script>";
							$this->lMesanje->Text = '<div class="callout callout-success"><h5><i class="icon fa fa-check"></i> Venta a credito realizada <b>monto a pagar: '.'$ '.number_format($total,2).'</b></h5>'.$script.'</div>';
							$this->IniciarTerminalVenta();
							$this->params->value = "";
							$this->linecomando->Text = "";
						}else{
							$this->lMesanje->Text = '<div class="callout callout-warning"><h5><i class="icon fa fa-warning"></i> No se puede completar la venta por que el saldo de deuda supera el permitido $ '.number_format($saldo_prelimanal,2).' / $ '.number_format($row_cliente->credito,2).'</h5> </div>';
						}
					}else{
						$this->lMesanje->Text = '<div class="callout callout-warning"><h5><i class="icon fa fa-warning"></i> No se puede completar la venta por que no hay nada en lista</h5> </div>';
					}
					//$this->IniciarTerminalVenta();
				}else{
					$this->btnBuscarClientes_OnClick(null,null);
					$this->lMesanje->Text = '<div class="callout callout-warning"><h5><i class="icon fa fa-warning"></i> Se necesita un cliente valido </h5> </div>';
				}
			}else{
				$this->lMesanje->Text = '<div class="callout callout-warning"><h5><i class="icon fa fa-warning"></i> No se encontro ningún cliente </h5> </div>';
			}
		}
	}
	
	public function dgCredito_OnItemCreated($sender, $param){
		$item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
			$row = $item->Data;
			if($row instanceof LMsVentas){
				//$item->rowCredito->lCredito->text = "$ " . number_format($row->total,2);
				$item->rowCredito->lCredito->text = "$ " . number_format($this->User->SaldoActual($row->id_clientes));
				//Prado::log(TVarDumper::dump($row,1),TLogger::NOTICE,$this->PagePath);
			}
		}
	}
	
	public function btnAbonar_OnClick($sender, $param){
		$this->ModalAbonar->Open();
		$this->TpAbono->Visible = false;
		//$ClientID = $this->txtClienteCredito->ClientID;
		$this->txtClienteCredito->focus();
		//$this->txtClienteCredito->Attributes->onload = "alert('xD');";
		//Prado::log(TVarDumper::dump($this->txtClienteCredito->ClientID,1),TLogger::NOTICE,$this->PagePath);
		//$this->LbJSCredito->Text = '<script> $(function(){ $("#' . $ClientID . '").filter(":visible").focus(); })</script>';
	}
	
	public function JbtnBuscarCredito_OnClick($sender, $param){
		$idsucursales = $this->User->idsucursales;
		$Parametros = array("buscar" => "%".$this->txtClienteCredito->Text."%",
                            "idsucursales"  => $idsucursales);
		$tabla = $this->Application->Modules['query']->Client->queryForList("vwCreditos_exportar",$Parametros);
		//Prado::log(TVarDumper::dump($tabla,1),TLogger::NOTICE,$this->PagePath);
		//$this->dgCredito->Visible = true;
		/*$where = " borrado = 0 AND (b.nombre like :buscar OR b.rfc like :buscar OR b.telefono like :buscar OR a.id_ventas = :buscar)";
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Parameters[':buscar'] = "%".$this->txtClienteCredito->Text."%";
		$ct_buscar->Condition = $where;
		$ct_buscar->OrdersBy['nombre'] = 'asc';
        $ct_buscar->Limit = 10;
		$tabla = LMsClientes::finder()->findAll($ct_buscar);*/
		$this->dgCredito->DataSource = $tabla;
        $this->dgCredito->dataBind();
	}
	
	public function JbtnCerrarCredito_OnClick($sender, $param){
		$this->ModalAbonar->Close();
	}
	
	public function btnClickCredito_OnClick($sender, $param){
		$item = $sender->namingContainer;
		$keyid = $this->dgCredito->DataKeys[$item->itemIndex];
		//Prado::log(TVarDumper::dump($keyid,1),TLogger::NOTICE,$this->PagePath);
		$row = LMsVentas::finder()->find(" id_ventas = ? ",array($keyid));
		if($row instanceof LMsVentas){
			$this->lClienteCredito->Text = $row->ms_clientes->nombre;
			$this->id_clientes->value = $row->id_clientes;
			$this->lCliente->Text = $row->ms_clientes->nombre;
			$monto = 0;
			$row_credito = LMsVentas::finder()->findAll(" id_clientes = ? AND estatus = 5 ",array($row->id_clientes));
			foreach($row_credito as $keya => $valuea){
				$monto = $monto + ($valuea->total - $valuea->efectivo);
			}
			$abono = 0;
			$row_abonado = LCtCreditosAbonos::finder()->findAll(" id_clientes = ? ",array($row->id_clientes));
			foreach($row_abonado as $keyb => $valueb){
				$abono = $abono + $valueb->monto;
			}
			//Prado::log($monto . " - " . $abono,TLogger::NOTICE,$this->PagePath);
			$this->credito_monto->value = ($monto - $abono);
			$this->lMontoDeuda->Text = "$ " . number_format($monto - $abono,2);
			$this->TpAbono->Visible = true;
			$this->dgCredito->DataSource = array();;
			$this->dgCredito->dataBind();
		}
		//$this->dgCredito->Visible = false;
	}
	
	public function JbtnAbonarCredito_OnClick($sender, $param){
		$row = LMsVentas::finder()->find(" id_ventas = ? ",array($this->id_ventas->value));
		if($row instanceof LMsVentas){
			$row->id_clientes = $this->id_clientes->value;
			$row->save();
			$row_ventasdetalle = LCtVentasDetalle::finder()->find("id_ventas = ? AND id_inventarios = 1 ",$this->id_ventas->value);
			if($row_ventasdetalle instanceof LCtVentasDetalle){
				$row_ventasdetalle->precio_vendido = $this->txtAbomoCredito->Text;
				$row_ventasdetalle->fecha_movimiento = date("Y-m-d H:i:s");
				//$row_ventasdetalle->borrado
				$row_ventasdetalle->save();
			}else{
				$row_ventasdetalle = new LCtVentasDetalle;
				$row_ventasdetalle->id_ventas = $this->id_ventas->value;
				$row_ventasdetalle->id_inventarios = 1;
				$row_ventasdetalle->precio_publico = 0;
				$row_ventasdetalle->precio_adquisicion = 0;
				$row_ventasdetalle->precio_vendido = $this->txtAbomoCredito->Text;
				$row_ventasdetalle->descuento = 0;
				$row_ventasdetalle->cantidad = 1;
				$row_ventasdetalle->fecha_movimiento = date("Y-m-d H:i:s");
				//$row_ventasdetalle->borrado
				$row_ventasdetalle->save();
			}
		}
		$this->ModalAbonar->Close();
		$this->IniciarTerminalVenta();
	}
}
