<?php
class facturas extends TPage
{
	public $i = 0;
    public $total_venta = 0;
    public $catalogo3 = array();
	//public $id_venta = 0;
	
	public function onInit($param){
		$this->master->titulo->Text = "Facturas";
		$this->master->subtitulo->Text = "Lista de facturas";
        $this->title = "Facturas - Ventas";
        //Prado::log(TVarDumper::dump($this->txtTicket->config,3),TLogger::NOTICE,$this->PagePath);
	}
    
    public function onLoad($param)
    {
        if(!$this->User->ServicioActivo("18")){
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
            $this->pnDatosFiscales->Visible = false;
			$MetodoPago = LBsCatalogosGenericos::finder()->findAll(" catalogo = ? AND activo = 1 ",12);
			$this->cmdMetodoPago->DataSource = $MetodoPago;
			$this->cmdMetodoPago->dataBind();
        }
    }
	
	public function btnForm_OnClick(){
		//Prado::log(TVarDumper::dump($this,1),TLogger::NOTICE,$this->PagePath);
		Prado::log("Enter",TLogger::NOTICE,$this->PagePath);
	}
	
	public function btnBuscarTicket_OnClick($sender, $param){
		$this->lMensaje->Text = "<div></div>";
		if($this->txtTicket->Text != ""){
			//el ticket de ser una venta terminada y tiene que haber sido facturado
			$hora_tolerancia = $this->Application->Parameters["satDiasToleranciaFactura"];
			$time = strtotime(" - " . $hora_tolerancia . " day");
			$fechai = date("Y-m-d 00:00:00",$time);
			$fechaf = date("Y-m-d 23:59:59");
			$criteria = new TActiveRecordCriteria;
			$where = " estatus in (3,5) AND tipo_venta = 1 ";
			$where .= " AND id_ventas = :idventas ";
			$where .= " AND fecha_termina between :fechai AND :fechaf";
			$criteria->Condition = $where;
			$criteria->Parameters[':fechai'] = $fechai;
			$criteria->Parameters[':fechaf'] = $fechaf;
			$criteria->Parameters[':idventas'] = $this->txtTicket->Text;
			
			$row_venta = LMsVentas::finder()->find($criteria);
			if($row_venta instanceof LMsVentas){
				$this->id_ventas->value =  $row_venta->id_ventas;
				//Datos del Cliente
				if($row_venta->ms_clientes->tipo_cliente == 2){
					$this->lCliente->Text     = $row_venta->ms_clientes->nombre;
					$this->txtRFC->Text       = $row_venta->ms_clientes->rfc;
					$this->id_clientes->value = $row_venta->id_clientes;
					$this->ldatos->Text       = "<small>Tel.</small> ".$row_venta->ms_clientes->telefono;
					$this->lDomicilio->Text   = $row_venta->ms_clientes->direccion;
				}
				//lista de compra
				$rows_ventasdetalle = LCtVentasDetalle::finder()->findAll(" id_ventas = ? AND borrado = 0",$row_venta->id_ventas);
				$crows = count($rows_ventasdetalle);
				$this->ListaBacia->Visible = (!$crows > 0);
				//$this->lnproductos->Text = $crows;
				$this->RpListaCompra->DataSource = $rows_ventasdetalle;
				$this->RpListaCompra->dataBind();
				//$this->inactividad->value = 0;
			}else{
				$this->lMensaje->Text = '<div class="callout callout-danger"><h4><i class="fa fa-exclamation-triangle"></i> Ticket invalido</h4><p>Ticket no existe, o no es valido</p></div>';
			}
		}
	}
		
	public function RpListaCompra_DataBound($sender, $param){
		$item=$param->Item;
		//Prado::log(TVarDumper::dump($item->ItemType,1),TLogger::NOTICE,$this->PagePath);
		if($item->ItemType === 'Header'){
			//$this->total_venta = 0;
		}
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
			$row = $item->Data;
			if($row instanceof LCtVentasDetalle ){
				$this->i ++;
				$item->lnumero->Text = $this->i;
				$item->nombreproducto->Text = $row->ms_inventarios->ms_productos->nombre;
				if($row->ms_inventarios->ms_productos->sat_productos_servicios instanceof LSatProductosServicios){
					$item->nombreproducto->Text .= " <br /> <small>" . $row->ms_inventarios->ms_productos->sat_productos_servicios->descripcion . "</small>";
				}
				$item->precio->Text = "$ ".number_format($row->precio_vendido,2);
				//$descuento = (float) ($row->descuento < 1 && $row->descuento > 0 ? $row->descuento : 0) * 100;
				//$item->descuento->Text = round($descuento) . " %";
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
			//$this->lTotal->Text = "$ ".number_format($this->total_venta,2);
			
		}
	}
	
	public function btnLista_OnClick($sender, $param){
		$this->ljs->Text = '<script> </script>';
		$this->dgTicket->VirtualItemCount = $this->dgTicket_getRowCount();
		$this->dgTicket->CurrentPageIndex = 0;
		$this->dgTicket->DataSource = $this->dgTicket_getDataRows(0,$this->dgTicket->PageSize);
        $this->dgTicket->dataBind();
	}
	
	public function dgTicket_DataBound($sender, $param){
		$item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
			$row = $item->Data;
			if($row instanceof LMsVentas ){
				
				$item->rowTicket->style = " width: 80px; ";
				
				$item->rowFecha->lFecha->Text = date("d/M H:i",strtotime($row->fecha_termina));
				$item->rowFecha->style = " width: 100px; ";
				
				$item->rowTotal->lTotal->Text = "$ " . number_format($row->total,2);
				$item->rowTotal->cssclass = "text-right";
				$item->rowTotal->style = " width: 90px; ";
				
				$item->rowLinkTicket->style = " width: 50px; ";
			}
		}
	}
	
	protected function dgTicket_getDataRows($offset,$rows)
    {
		$hora_tolerancia = $this->Application->Parameters["satDiasToleranciaFactura"];
		$time = strtotime(" - " . $hora_tolerancia . " day");
		$fechai = date("Y-m-d 00:00:00",$time);
		$fechaf = date("Y-m-d 23:59:59");
		$criteria = new TActiveRecordCriteria;
		$where = " estatus in (3,5) ";
		$where .= " AND fecha_termina between :fechai AND :fechaf";
		$criteria->Condition = $where;
		$criteria->Parameters[':fechai'] = $fechai;
		$criteria->Parameters[':fechaf'] = $fechaf;
		$criteria->OrdersBy['id_ventas'] = 'desc';
		$criteria->Limit  = $rows;
		$criteria->Offset = $offset;
		$tabla = LMsVentas::finder()->findAll($criteria);
		
		//Prado::log(TVarDumper::dump($tabla,2),TLogger::NOTICE,$this->PagePath);
		
        return $tabla;
    }
    
    protected function dgTicket_getRowCount()
    {
		$hora_tolerancia = $this->Application->Parameters["satDiasToleranciaFactura"];
		$time = strtotime(" - " . $hora_tolerancia . " day");
		$fechai = date("Y-m-d 00:00:00",$time);
		$fechaf = date("Y-m-d 23:59:59");
		$criteria = new TActiveRecordCriteria;
		$where = " estatus in (3,5) ";
		$where .= " AND fecha_termina between :fechai AND :fechaf";
		$criteria->Condition = $where;
		$criteria->Parameters[':fechai'] = $fechai;
		$criteria->Parameters[':fechaf'] = $fechaf;
		//$criteria->OrdersBy['id_ventas'] = 'desc';
		$var = LMsVentas::finder()->Count($criteria);
        
		//$this->lnumero2->Text = "Encontrados: " . number_format($var);
		//Prado::log(TVarDumper::dump($var,1),TLogger::NOTICE,$this->PagePath);
        return $var;
    }
	
	public function dgTicket_changePage($sender,$param)
    {
        $this->dgTicket->CurrentPageIndex = $param->NewPageIndex;
        $offset=$param->NewPageIndex*$this->dgTicket->PageSize;
        //$this->i = $offset;
        $this->dgTicket->DataSource = $this->dgTicket_getDataRows($offset,$this->dgTicket->PageSize);
        $this->dgTicket->dataBind();
    }
	
	public function dgTicket_pagerCreated($sender,$param)
    {
        $param->Pager->Controls->insertAt(0,'Pagina(s): ');
		//Prado::log(TVarDumper::dump($param,2),TLogger::NOTICE,$this->PagePath);
    }
	
	public function btnClickTicket_OnClick($sender, $param){
		$item = $sender->namingContainer;
		$keys = $this->dgTicket->DataKeys;
		$row  = $this->dgTicket->items[$item->itemIndex];
		//Prado::log(TVarDumper::dump($keys[$item->itemIndex],2),TLogger::NOTICE,$this->PagePath);
		$this->txtTicket->Text = $keys[$item->itemIndex];
		$this->btnBuscarTicket_OnClick(null, null);
		$this->ljs->Text = '<script> $("#ModalTickets").modal("hide"); </script>';
		
	}
	
	public function btnBuscarRFC_OnClick($sender, $param){
		$this->lMensaje->Text = "<div></div>";
		if($this->txtRFC->Text != ""){
			//el ticket de ser una venta terminada y tiene que haber sido facturado
			$row_cliente = LMsClientes::finder()->find(" borrado = 0 AND (mail = ? OR rfc = ? )AND tipo_cliente = 2 ", [$this->txtRFC->Text, $this->txtRFC->Text]);
			if($row_cliente instanceof LMsClientes){
				$this->lnombre->Text      = $row_cliente->nombre;
				$this->txtRFC->Text       = $row_cliente->rfc;
				$this->lrfc->Text         = $row_cliente->rfc;
				$this->id_clientes->value = $row_cliente->id_clientes;
				$this->ltelefono->Text    = $row_cliente->telefono;
				$this->lDomicilio->Text   = $row_cliente->direccion;
				$this->lemail->Text       = $row_cliente->mail;
				$this->lMensaje->Text = '';
				$this->pnDatosFiscales->Visible = true;
			}else{
				$this->lnombre->Text      = "";
				$this->txtRFC->Text       = "";
				$this->lrfc->Text         = "";
				$this->id_clientes->value = "";
				$this->ltelefono->Text    = "";
				$this->lDomicilio->Text   = "";
				$this->lemail->Text       = "";
				$this->lMensaje->Text = '<div class="callout callout-danger"><h4><i class="fa fa-exclamation-triangle"></i> RFC no se encuentra</h4><p> Se sugiere revisar su escritura o darlo de alta <a >aquí</a></p></div>';
				$this->pnDatosFiscales->Visible = false;
			}
		}
	}
	
	public function btnListaClient_OnClick($sender, $param){
		$this->ljs->Text = '<script> </script>';
		$this->dgClientes->CurrentPageIndex = 0;
		$this->dgClientes->VirtualItemCount = $this->dgClientes_getRowCount();
		$this->dgClientes->DataSource = $this->dgClientes_getDataRows(0,$this->dgClientes->PageSize);
        $this->dgClientes->dataBind();
	}
	
	
	public function dgClientes_DataBound($sender, $param){
		$item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
			$row = $item->Data;
			if($row instanceof LMsVentas ){
				
				$item->rowTicket->style = " width: 80px; ";
				
				$item->rowFecha->lFecha->Text = date("d/M H:i",strtotime($row->fecha_termina));
				$item->rowFecha->style = " width: 100px; ";
				
				$item->rowTotal->lTotal->Text = "$ " . number_format($row->total,2);
				$item->rowTotal->cssclass = "text-right";
				$item->rowTotal->style = " width: 90px; ";
				
				$item->rowLinkTicket->style = " width: 50px; ";
			}
		}
	}
	
	protected function dgClientes_getDataRows($offset,$rows)
    {
		$criteria = new TActiveRecordCriteria;
		$where = " tipo_cliente = 2 AND borrado = 0 ";
		$where .= " AND (rfc like :nombre OR nombre like :nombre OR telefono like :nombre OR direccion  like :nombre) ";
		$criteria->Condition = $where;
		$criteria->Parameters[':nombre'] = "%".$this->txtCliente->Text."%";
		$criteria->OrdersBy['nombre'] = 'asc';
		$criteria->Limit  = $rows;
		$criteria->Offset = $offset;
		$tabla = LMsClientes::finder()->findAll($criteria);
		
		return $tabla;
    }
    
    protected function dgClientes_getRowCount()
    {
		$criteria = new TActiveRecordCriteria;
		$where = " tipo_cliente = 2 AND borrado = 0 ";
		$where .= " AND (rfc like :nombre OR nombre like :nombre OR telefono like :nombre OR direccion  like :nombre) ";
		$criteria->Condition = $where;
		$criteria->Parameters[':nombre'] = "%".$this->txtCliente->Text."%";
		$var = LMsClientes::finder()->Count($criteria);
		
        return $var;
    }
	
	public function dgClientes_changePage($sender,$param)
    {
        $this->dgClientes->CurrentPageIndex = $param->NewPageIndex;
        $offset=$param->NewPageIndex*$this->dgTicket->PageSize;
        //$this->i = $offset;
        $this->dgClientes->DataSource = $this->dgClientes_getDataRows($offset,$this->dgClientes->PageSize);
        $this->dgClientes->dataBind();
    }
	
	public function dgClientes_pagerCreated($sender,$param)
    {
        $param->Pager->Controls->insertAt(0,'Pagina(s): ');
		//Prado::log(TVarDumper::dump($param,2),TLogger::NOTICE,$this->PagePath);
    }
	
	public function btnClickCliente_OnClick($sender, $param){
		$item = $sender->namingContainer;
		$keys = $this->dgClientes->DataKeys;
		$row  = $this->dgClientes->items[$item->itemIndex];
		//Prado::log(TVarDumper::dump($this->dgClientes->items[$item->itemIndex]->rowRFC->Text,2),TLogger::NOTICE,$this->PagePath);
		$this->txtRFC->Text = $this->dgClientes->items[$item->itemIndex]->rowRFC->Text; //$keys[$item->itemIndex];
		$this->btnBuscarRFC_OnClick(null, null);
		$this->ljs->Text = '<script> $("#ModalClientes").modal("hide"); </script>';
		
	}
	
	public function btnNuevoCliente_OnClick($sender, $param){
		$this->pnCliente->Visible = true;
		$this->lStatusClient->Text = '';
		
		$this->valEIDCliente->Value = "";
		$this->txtERFC->Text        = "";
		$this->txtENombre->Text     = "";
		$this->txtETelefono->Text   = "";
		$this->txtEEmail->Text      = "";
		$this->txtEDireccion->Text  = "";
	}
	
	public function btnEditClient_OnClick($sender, $param){
		$row = LMsClientes::finder()->find(" id_clientes = ? ",$this->id_clientes->value);
		if($row instanceof LMsClientes){
			
			$this->pnCliente->Visible = true;
			//$this->lStatusClient->Text = '';
			$this->lStatusClient->Text = '<script> $("#ModalNuevoCliente").modal("show"); </script>';
			
			$this->valEIDCliente->Value = $row->id_clientes;
			$this->txtERFC->Text        = $row->rfc;
			$this->txtENombre->Text     = $row->nombre;
			$this->txtETelefono->Text   = $row->telefono;
			$this->txtEEmail->Text      = $row->mail;
			$this->txtEDireccion->Text  = $row->direccion;
		}
	}
	
	public function btnSaveClient_OnClick($sender, $param){
		if($this->IsValid){
			if( $this->valEIDCliente->Value != ""){
				$row = LMsClientes::finder()->find(" id_clientes = ? ",$this->valEIDCliente->Value);
				if($row instanceof LMsClientes){
					$row->tipo_cliente = 2;
					//$row->id_estados    =
					//$row->id_municipios =
					$row->rfc          = strtoupper($this->txtERFC->Text);
					$row->nombre       = $this->txtENombre->Text;
					$row->telefono     = $this->txtETelefono->Text;
					$row->direccion    = $this->txtEDireccion->Text;
					$row->mail         = $this->txtEEmail->Text;
					//$row->foto
					//$row->credito
					//$row->borrado
					$row->save();
				}else{
					Prado::log("Ocurrio un error al editar un registro existente ",TLogger::NOTICE,$this->PagePath);
				}
			}else{
				$row = new LMsClientes;
				//$row->id_clientes
				$row->tipo_cliente = 2;
				//$row->id_estados    =
				//$row->id_municipios =
				$row->rfc          = strtoupper($this->txtERFC->Text);
				$row->nombre       = $this->txtENombre->Text;
				$row->telefono     = $this->txtETelefono->Text;
				$row->direccion    = $this->txtEDireccion->Text;
				$row->mail         = $this->txtEEmail->Text;
				//$row->foto           
				//$row->credito        
				//$row->borrado
				$row->save();
			}
			$this->txtERFC->Text       = "";
			$this->txtENombre->Text    = "";
			$this->txtETelefono->Text  = "";
			$this->txtEEmail->Text     = "";
			$this->txtEDireccion->Text = "";
			$this->lStatusClient->Text = '<script> $("#ModalNuevoCliente").modal("hide"); </script>';
			$this->txtRFC->Text        = $row->rfc;
			$this->btnBuscarRFC_OnClick(null,null);
		}
	}
	
	public function btnCloseClient_OnClick($sender, $param){
		$this->pnCliente->Visible = false;
		$this->lStatusClient->Text = '<script> $("#ModalNuevoCliente").modal("hide"); </script>';
		
		$this->valEIDCliente->Value = "";
		$this->txtERFC->Text        = "";
		$this->txtENombre->Text     = "";
		$this->txtETelefono->Text   = "";
		$this->txtEEmail->Text      = "";
		$this->txtEDireccion->Text  = "";
	}
	
}