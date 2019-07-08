<?php
//Prado::using('System.Util.TVarDumper');
 
class mermas extends TPage
{
	public $i = 0;
	public $j = 0;
	public $perimiso_actualizar = false;
	
	public function onInit($param){
		$this->master->titulo->Text = "Movimientos";
		$this->master->subtitulo->Text = "Movimientos de salida";
        $this->title = "Movimientos - salida";
	}
	
    public function onLoad($param)
    {
		$v = $this->User->ServicioActivo("9");
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
            $this->mostrarPanel(1);
            
            $this->cmdBodega2->DataSource = LCtBodegas::finder()->findAll(" id_sucursales = ? AND borrado = 0",$this->User->idsucursales);
            $this->cmdBodega2->dataBind();
			$this->cmdBodega2->SelectedIndex = 0;
			
			//$this->cmdBodega2->Text = 1;
            
            $this->dgTabla->VirtualItemCount = $this->getRowCount();
            $this->dgTabla->DataSource=$this->getDataRows(0,$this->dgTabla->PageSize);
            $this->dgTabla->dataBind();
        }
    }
    
    public function btnBuscar_Producto_OnClick($sender, $param){
        if($this->IsValid)
        {
            $row = LMsProductos::finder()->find(" borrado = 0 AND codigo = ? ", $this->txtCodigo->Text);
            if($row instanceof LMsProductos){
                $this->id_productos->value = $row->id_productos;
                if($row->foto != ""){
                    $this->foto->ImageUrl = $row->foto;
                    $this->file->value = $row->foto;
                }
                $this->txtNombre->Text = $row->nombre;
				$this->txtNombre->enabled = false;
				$rox = LMsInventarios::finder()->find(" borrado = 0 AND id_productos = ? AND id_bodegas = ?",array($row->id_productos,$this->cmdBodega3->Text));
				if($rox instanceof LMsInventarios){
					$this->id_inventarios->value = $rox->id_inventarios;
					//$this->txtPrecioAdquisicion->text = $rox->precioadquisicion;
					$this->txtPrecioPublico->text = $rox->preciopublico;
					$this->txtUnidad->text = $rox->unidad;
					$this->txtUnidad->enabled = false;
					
					$roz = LCtMovimientosInventarios::finder()->find(" borrado = 0 AND id_inventarios = ? AND id_movimientos = ?",array($rox->id_inventarios,$this->id_movimientos->Value));
					if($roz instanceof LCtMovimientosInventarios){
						$this->codigoactivo->Text = '<label class="btn btn-danger"><i class="fa fa-close"></i></label>';
						$this->codigovalido->value = "";
					}else{
						$this->codigoactivo->Text = '<label class="btn btn-success"><i class="fa fa-check"></i></label>';
						$this->codigovalido->value = "1";
					}
				}else{
					$this->codigoactivo->Text = '<label class="btn btn-danger"><i class="fa fa-close"></i></label>';
					$this->codigovalido->value = "";
				}
            }else{
				$this->codigoactivo->Text = '<label class="btn btn-danger"><i class="fa fa-close"></i></label>';
				$this->codigovalido->value = "";
			}
        }
    }
    
	public function btnProcesar_OnClick($sender, $param){
		//proceso
		$row  = LMsMovimientos::finder()->find("borrado = 0 AND id_movimientos = ?", $this->id_movimientos->value);
		if($row instanceof LMsMovimientos ){
			$tabla = LCtMovimientosInventarios::finder()->findAll(" id_movimientos = ? ",$row->id_movimientos);
			$row->estatus = 2;
			$row->fecha_actualizacion = date('Y-m-d H:i:s');
			$row->save();
			$this->btnProcesar->Visible = false;
			$this->btnAgregar->Visible  = false;
			foreach($tabla as $i => $r){
				$cantidad_movimiento = $r->cantidad;
				$cantidad_actual = $r->ms_inventarios->stock;
				$inventario = $r->ms_inventarios;
				$precio = $r->preciopublico;
				switch($row->tipo_movimiento){
					case 1:
						$inventario->stock = $cantidad_actual + $cantidad_movimiento;
						$inventario->preciopublico = $precio;
						$inventario->save();
						break;
					case 2:
						$inventario->stock = $cantidad_actual - $cantidad_movimiento;
						//$inventario->preciopublico = $precio;
						$inventario->save();
						break;
					case 3:
						$inventario->stock = $cantidad_movimiento;
						$inventario->preciopublico = $precio;
						$inventario->save();
						break;
				}
				//$r->ms_inventarios->stock = $cantidad
				//Prado::log("[".$this->User->idusuarios."][".$this->User->idusuarios."][IP:".$_SERVER['REMOTE_ADDR']."][Inventario Actualizado ".$inventario->id_inventarios." : A:".$cantidad_actual.", D: ".$inventario->stock."]",TLogger::NOTICE,$this->PagePath);
				$this->dgTablaDetalle->VirtualItemCount = $this->getRowCount_Detalle();
				$this->dgTablaDetalle->DataSource=$this->getDataRows_Detalle(0,$this->dgTablaDetalle->PageSize);
				$this->dgTablaDetalle->dataBind();
			}
			
			$estatus_label = "";
			$catalogo2 = LBsCatalogosGenericos::finder()->find("catalogo = ? AND valor = ?",array(2,$row->estatus));
			if($catalogo2 instanceof LBsCatalogosGenericos){
				$estatus_label = '<label class="'.$catalogo2->cssclass.'">'.$catalogo2->opcion.'<label>';
			}else{
				$estatus_label = $row->estatus;
			}
			
		}
	}
	
    public function btnNuevo_OnClick($sender, $param){
		//$this->cmdDepartamento->DataSource = LCtDepartamentos::finder()->findAll(" borrado = 0 ");
		//$this->cmdDepartamento->dataBind();
		
		$this->cmdBodega->DataSource = LCtBodegas::finder()->findAll(" id_sucursales = ? AND borrado = 0",$this->User->idsucursales);;
		$this->cmdBodega->dataBind();
		//$this->cmdSucursal->Text = 1;
		$this->txtFechaMovimiento->Text = date('d/m/Y');
			
        $this->mostrarPanel(2);
        
        $this->id_productos->value = "";
        $this->id_inventarios->value = "";
        $this->txtNombre->text = "";
        //$this->txtCargo->text = "";
    }
	
    public function btnBuscar_Producto_Nombre_OnClick($sender, $param){
		$this->txtNombres->Text = $this->txtNombre->Text;
		//$this->ModalDepartamentos->Open();
		$script = "<script> $('#ModalDepartamentos').modal('show');  </script>";
		$this->ljs->Text = $script;
		$where = " tipo = 1 AND borrado = 0 AND (nombre like :nombre OR codigo like :nombre )";
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Parameters[':nombre'] = "%".$this->txtNombres->Text."%";
		$ct_buscar->Condition = $where;
		$ct_buscar->OrdersBy['nombre'] = 'asc';
        $ct_buscar->Limit = 10;
		$this->dgProductos->DataSource = LMsProductos::finder()->findAll($ct_buscar);
        $this->dgProductos->dataBind();
    }
    
	public function JbtnBuscarProductoNombre_OnClick($sender, $param){
		$where = " tipo = 1 AND borrado = 0 AND (nombre like :nombre OR codigo like :nombre )";
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Parameters[':nombre'] = "%".$this->txtNombres->Text."%";
		$ct_buscar->Condition = $where;
		$ct_buscar->OrdersBy['nombre'] = 'asc';
        $ct_buscar->Limit = 10;
		$this->dgProductos->DataSource = LMsProductos::finder()->findAll($ct_buscar);
        $this->dgProductos->dataBind();
	}
	
	public function btnClick_OnClick($sender, $param){
		$item = $sender->namingContainer;
		$keyid = $this->dgProductos->DataKeys[$item->itemIndex];
		$row = LMsProductos::finder()->find(" borrado = 0 AND id_productos = ? ", $keyid);
        if($row instanceof LMsProductos){
			$this->id_productos->value = $row->id_productos;
			if($row->foto != ""){
				$this->foto->ImageUrl = $row->foto;
				$this->file->value = $row->foto;
			}
			$this->txtCodigo->Text = $row->codigo;
			$this->txtNombre->Text = $row->nombre;
			$this->txtNombre->enabled = false;
			$rox = LMsInventarios::finder()->find(" borrado = 0 AND id_productos = ? AND id_bodegas = ?",array($row->id_productos,$this->cmdBodega3->Text));
			if($rox instanceof LMsInventarios){
				$this->id_inventarios->value = $rox->id_inventarios;
				//$this->txtPrecioAdquisicion->text = $rox->precioadquisicion;
				$this->txtPrecioPublico->text = $rox->preciopublico;
				$this->txtUnidad->text = $rox->unidad;
				$this->txtUnidad->enabled = false;
				
				$roz = LCtMovimientosInventarios::finder()->find(" borrado = 0 AND id_inventarios = ? AND id_movimientos = ?",array($rox->id_inventarios,$this->id_movimientos->Value));
				if($roz instanceof LCtMovimientosInventarios){
					$this->codigoactivo->Text = '<label class="btn btn-danger"><i class="fa fa-close"></i></label>';
					$this->codigovalido->value = "";
				}else{
					$this->codigoactivo->Text = '<label class="btn btn-success"><i class="fa fa-check"></i></label>';
					$this->codigovalido->value = "1";
				}
			}else{
				$this->codigoactivo->Text = '<label class="btn btn-danger"><i class="fa fa-close"></i></label>';
				$this->codigovalido->value = "";
			}
		}else{
			$this->codigoactivo->Text = '<label class="btn btn-danger"><i class="fa fa-close"></i></label>';
			$this->codigovalido->value = "";
		}
		//$this->ModalDepartamentos->Close();
		$script = "<script> $('#ModalDepartamentos').modal('hide');  </script>";
		$this->ljs->Text = $script;
	}
	
	public function btnEditar_OnClick($sender, $param){
		$item = $sender->namingContainer;
		$keyid = $this->dgTabla->DataKeys[$item->itemIndex];
		
		$this->cmdBodega->DataSource = LCtBodegas::finder()->findAll(" id_sucursales = ? AND borrado = 0",$this->User->idsucursales);;
		$this->cmdBodega->dataBind();
		
		$row  = LMsMovimientos::finder()->find("borrado = 0 AND id_movimientos = ?", $keyid);
		
		if($row instanceof LMsMovimientos ){
			//Prado::log(TVarDumper::dump($row->ms_productos,1),TLogger::NOTICE,$this->PagePath);
			$this->cmdBodega->Text = $row->id_bodegas;
			$this->txtDescripcion->Text = $row->descripcion;
			$this->txtFechaMovimiento->Text = $this->User->fecha($row->fecha_movimiento);
			$this->id_movimientos->value = $row->id_movimientos;
			//$row->estatus;
			//$row->borrado;
		}
		$this->mostrarPanel(2);
		//Prado::log(TVarDumper::dump($keys[$item->itemIndex],1),TLogger::NOTICE,$this->PagePath);
	}
	
	public function btnDetalles_OnClick($sender, $param){
		$item = $sender->namingContainer;
		$keyid = $this->dgTabla->DataKeys[$item->itemIndex];
		$this->id_movimientos->value = $keyid;
		$row  = LMsMovimientos::finder()->find("borrado = 0 AND id_movimientos = ?", $keyid);
		
		if($row instanceof LMsMovimientos ){
			$this->linkpdf->NavigateUrl = $this->Service->constructUrl('inventarios.ticket', array("ticket" => $row->id_movimientos));
			$estatus_label = "";
			$catalogo2 = LBsCatalogosGenericos::finder()->find("catalogo = ? AND valor = ?",array(2,$row->estatus));
			if($catalogo2 instanceof LBsCatalogosGenericos){
				$estatus_label = '<label class="'.$catalogo2->cssclass.'">'.$catalogo2->opcion.'<label>';
			}else{
				$estatus_label = $row->estatus;
			}
			$this->lmovimiento->Text = "Movimiento de ingreso: ".$this->User->fecha($row->fecha_movimiento) . " " . $estatus_label;
			$this->lmovimiento2->Text = "Movimiento de ingreso: ".$this->User->fecha($row->fecha_movimiento). " " . $estatus_label;
			switch($row->estatus){
				case 1:
					$this->btnProcesar->Visible = true;
					$this->btnAgregar->Visible  = true;
					break;
				case 2:
					$this->btnProcesar->Visible = false;
					$this->btnAgregar->Visible  = false;
					break;
				case 3:
					$this->btnProcesar->Visible = false;
					$this->btnAgregar->Visible  = false;
					break;
			}
			$this->mostrarPanel(4);
			$this->dgTablaDetalle->VirtualItemCount = $this->getRowCount_Detalle();
			$this->dgTablaDetalle->DataSource=$this->getDataRows_Detalle(0,$this->dgTablaDetalle->PageSize);
			$this->dgTablaDetalle->dataBind();
			
		}
	}
	
	public function btnCancelarMovimiento_OnClick($sender, $param){
		$this->id_movimientos->value = "";
		//$this->cmdBodega->Text;
		$this->txtDescripcion->Text = "";
		$this->txtFechaMovimiento->Text = "";
		
		$this->Formulario->visible = false;
		$this->tpDatos->visible = false;
		$this->tpSinDatos->visible = false;
		$this->Buscador->visible = true;
		
		$this->dgTabla->VirtualItemCount = $this->getRowCount();
		$this->dgTabla->DataSource=$this->getDataRows(0,$this->dgTabla->PageSize);
		$this->dgTabla->dataBind();
	}
	
	public function btnGuardarMovimiento_OnClick($sender, $param){
        $idsucursales = $this->User->idsucursales;
		$id_movimientos = $this->id_movimientos->value;
		if($this->IsValid)
        {
			$row = LMsMovimientos::finder()->find(" borrado = 0 AND id_movimientos = ? ", $id_movimientos);
			//Prado::log(TVarDumper::dump($row,1),TLogger::NOTICE,$this->PagePath);
			if($row instanceof LMsMovimientos){
				//$row->id_movimientos;
				$row->id_usuarios = $this->User->idusuarios;
				$row->id_sucursales = $idsucursales;
				$row->id_bodegas = $this->cmdBodega->Text;
				$row->tipo_movimiento = 2;
				$row->descripcion = $this->txtDescripcion->Text;
				//$row->fecha_registro;
				$row->fecha_movimiento = $this->User->fecha($this->txtFechaMovimiento->Text);
				$row->fecha_actualizacion = date('Y-m-d H:i:s');
				//$row->estatus;
				//$row->borrado;
				$row->save();
				$this->id_movimientos->value = $row->id_movimientos;
				$this->linkpdf->NavigateUrl = $this->Service->constructUrl('inventarios.listaabastopdf', array("lista" => $row->id_movimientos));
			}else{
				$row = new LMsMovimientos;
				//$row->id_movimientos;
				$row->id_usuarios = $this->User->idusuarios;
				$row->id_sucursales = $idsucursales;
				$row->id_bodegas = $this->cmdBodega->Text;
				$row->tipo_movimiento = 2;
				$row->descripcion = $this->txtDescripcion->Text;
				$row->fecha_registro = date('Y-m-d H:i:s');
				$row->fecha_movimiento = $this->User->fecha($this->txtFechaMovimiento->Text);
				//$row->fecha_actualizacion;
				//$row->estatus;
				//$row->borrado;
				$row->save();
				$this->id_movimientos->value = $row->id_movimientos;
				$this->linkpdf->NavigateUrl = $this->Service->constructUrl('inventarios.listaabastopdf', array("lista" => $row->id_movimientos));
			}
			
			
			$this->mostrarPanel(3);
			//$this->Buscador->visible = true;
			
			$this->dgTablaDetalle->VirtualItemCount = $this->getRowCount_Detalle();
			$this->dgTablaDetalle->DataSource=$this->getDataRows_Detalle(0,$this->dgTablaDetalle->PageSize);
			$this->dgTablaDetalle->dataBind();
		}
	}
	
	public function btnNuevoMovimiento_OnClick($sender, $param){
		$this->cmdBodega3->DataSource = LCtBodegas::finder()->findAll(" id_sucursales = ? AND borrado = 0",$this->User->idsucursales);
        $this->cmdBodega3->dataBind();
		$this->cmdBodega3->SelectedIndex = 0;
		$this->mostrarPanel(5);
	}
	
    public function btnGuardarDetalle_OnClick($sender, $param){
        if($this->IsValid)
        {
            //ms_productos
            $row = LCtMovimientosInventarios::finder()->find(" borrado = 0 AND id_inventarios = ? AND id_movimientos = ?",array($this->id_inventarios->value,$this->id_movimientos->value));
            if(!$row instanceof LCtMovimientosInventarios){
				$rox = LMsInventarios::finder()->find(" borrado = 0 AND id_inventarios = ?",$this->id_inventarios->value);
				if($rox instanceof LMsInventarios){
					if($this->file->value != ""){
						$rox->ms_productos->foto = $this->file->value;
						$rox->ms_productos->save();
					}
					if($rox->preciopublico != $this->txtPrecioPublico->Text && $this->txtPrecioPublico->Text != ""){
						$rox->preciopublico = $this->txtPrecioPublico->Text;
						$rox->save();
					}
				}
                $row = new LCtMovimientosInventarios;
                $row->id_movimientos = $this->id_movimientos->value;
				$row->id_inventarios = $this->id_inventarios->value;
				$row->cantidad       = (double) $this->txtStock->Text;
				$row->preciopublico  = (double) $this->txtPrecioPublico->Text;
				//$row->precioadquisicion
                $row->save();
            }
            
            
			$this->foto->ImageUrl = "image/producto.jpg";
			$this->file->value = "";
            $this->txtNombre->Text = "";
			$this->txtNombre->Enabled = true;
			$this->txtCodigo->Text = "";
            $this->id_productos->value = "";
            $this->id_inventarios->value = "";
            $this->txtPrecioPublico->text = "";
            $this->txtStock->text = "";
            $this->txtUnidad->text = "U";
			$this->txtUnidad->Enabled = true;
        
			$this->mostrarPanel(3);
			
			$this->dgTablaDetalle->VirtualItemCount = $this->getRowCount_Detalle();
			$this->dgTablaDetalle->DataSource=$this->getDataRows_Detalle(0,$this->dgTablaDetalle->PageSize);
			$this->dgTablaDetalle->dataBind();
        }
    }
    
	public function btnCancelarDetalle_OnClick($sender, $param){
		
		$this->foto->ImageUrl = "image/producto.jpg";
		$this->file->value = "";
		$this->txtNombre->Text = "";
		$this->txtNombre->Enabled = true;
		$this->txtCodigo->Text = "";
		$this->id_productos->value = "";
		$this->id_inventarios->value = "";
		$this->txtPrecioPublico->text = "";
		$this->txtStock->text = "";
		$this->txtUnidad->text = "U";
		$this->txtUnidad->Enabled = true;
	
		$this->mostrarPanel(3);
		
		$this->dgTablaDetalle->VirtualItemCount = $this->getRowCount_Detalle();
		$this->dgTablaDetalle->DataSource=$this->getDataRows_Detalle(0,$this->dgTablaDetalle->PageSize);
		$this->dgTablaDetalle->dataBind();
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
	
	public function deleteItem($sender,$param)
    {
        $keyid = $this->dgTabla->DataKeys[$param->Item->ItemIndex];
		//$row = LMsVisitas::finder()->findByPk();
		$row = LMsMovimientos::finder()->find(" borrado = 0 AND id_movimientos = ? ", $keyid);
        $row->borrado = 1;
        $row->save();
        $this->dgTabla->DataSource=$this->getDataRows(0,$this->dgTabla->PageSize);
        $this->dgTabla->dataBind();
        $this->dgTabla->VirtualItemCount = $this->getRowCount();
		
		Prado::log("[".$this->User->idusuarios.
                       "][".$this->User->idusuarios."][IP:".
                       $_SERVER['REMOTE_ADDR'].
                       "][Elimino id_inventario: ".$keyid."]",
                       TLogger::NOTICE,
                       $this->PagePath);
		
    }
	
	public function editItem_Detalle($sender,$param)
    {
        $this->dgTablaDetalle->EditItemIndex = $param->Item->ItemIndex;
        $this->dgTablaDetalle->DataSource    = $this->getDataRows_Detalle(0,$this->dgTablaDetalle->PageSize);
        $this->dgTablaDetalle->dataBind();
    }
	
	public function saveItem_Detalle($sender,$param)
    {
        $item=$param->Item;
		$keyid = $this->dgTablaDetalle->DataKeys[$item->ItemIndex];
        $row = LCtMovimientosInventarios::finder()->find(" borrado = 0 AND id_movimientos_inventarios = ? ", $keyid);
		if($row instanceof LCtMovimientosInventarios){
			$row->preciopublico = $item->rowPrecio->txtPrecioPublico->Text;
			$row->cantidad = $item->rowCantidad->TextBox->Text;
			//$row->borrado = 1;
			//Prado::log(TVarDumper::dump(,1),TLogger::NOTICE,$this->PagePath);
			$row->save();
		}
        $this->dgTablaDetalle->EditItemIndex=-1;
        $this->dgTablaDetalle->DataSource    = $this->getDataRows_Detalle(0,$this->dgTablaDetalle->PageSize);
        $this->dgTablaDetalle->dataBind();
    }
 
    public function cancelItem_Detalle($sender,$param)
    {
        $this->dgTablaDetalle->EditItemIndex=-1;
        $this->dgTablaDetalle->DataSource    = $this->getDataRows_Detalle(0,$this->dgTablaDetalle->PageSize);
        $this->dgTablaDetalle->dataBind();
    }
	
	public function deleteItem_Detalle($sender,$param)
    {
        $keyid = $this->dgTablaDetalle->DataKeys[$param->Item->ItemIndex];
		//$row = LMsVisitas::finder()->findByPk();
		$row = LCtMovimientosInventarios::finder()->find(" borrado = 0 AND id_movimientos_inventarios = ? ", $keyid);
        $row->borrado = 1;
        $row->save();
        $this->dgTablaDetalle->DataSource=$this->getDataRows_Detalle(0,$this->dgTablaDetalle->PageSize);
        $this->dgTablaDetalle->dataBind();
        $this->dgTablaDetalle->VirtualItemCount = $this->getRowCount_Detalle();
		
		Prado::log("[".$this->User->idusuarios.
                       "][".$this->User->idusuarios."][IP:".
                       $_SERVER['REMOTE_ADDR'].
                       "][Elimino id_movimientos_inventarios: ".$keyid."]",
                       TLogger::NOTICE,
                       $this->PagePath);
		
    }
	
	
	
	protected function getDataRows_Detalle($offset,$rows)
    {
        
		$where = " borrado = 0 AND id_movimientos = :id_movimientos ";
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Parameters[':id_movimientos'] = $this->id_movimientos->value;
		
		$ct_buscar->Condition = $where;
		$ct_buscar->OrdersBy['id_movimientos_inventarios'] = 'asc';
        $ct_buscar->Limit = $rows;
        $ct_buscar->Offset = $offset;
		
        $tabla = LCtMovimientosInventarios::finder()->findAll($ct_buscar);
        return $tabla;
    }
    
    protected function getRowCount_Detalle()
    {
        $where = " borrado = 0 AND id_movimientos = :id_movimientos ";
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Parameters[':id_movimientos'] = $this->id_movimientos->value;
		
		$ct_buscar->Condition = $where;
        $var = LCtMovimientosInventarios::finder()->count($ct_buscar);
		
		//$this->linkPdf->NavigateUrl = $this->Service->constructUrl('inventarios.productospdf',$Parametros);
		$visible = $var > 0;
		$this->BuscarDetalleDatos->Visible = $visible;
		$this->BuscarDetalleSinDatos->Visible = !$visible;
		//$this->nelementos->Text = $var;
		//Prado::log(TVarDumper::dump($Parametros,1),TLogger::NOTICE,$this->PagePath);
        return $var;
    }
	
	
	public function getRows_Detalle($deforder=true,$offset=0)
    {
        // Si se consulta desde el principio el indice
        // de GridDatos debe cambiar a 0
        if($offset==0)
        {
            $this->dgTablaDetalle->CurrentPageIndex=0;
        }
        
        // Llena de datos GridDatos
        //$this->visita->setVirtualItemCount(TableVisitas::finder()->count());
        $this->dgTablaDetalle->VirtualItemCount = $this->getRowCount_Detalle();
        $this->dgTablaDetalle->DataSource=$this->getDataRows_Detalle($offset,$this->dgTablaDetalle->PageSize);
        $this->dgTablaDetalle->dataBind();
    }
	
	protected function getDataRows($offset,$rows)
    {
        $idsucursales = $this->User->idsucursales;
		$idbodega = $this->cmdBodega2->Text;
		
		$where = " borrado = 0 AND tipo_movimiento = 2 AND id_sucursales = :id_sucursales AND id_bodegas = :id_bodegas";
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Parameters[':id_sucursales'] = $idsucursales;
		$ct_buscar->Parameters[':id_bodegas'] = $idbodega;
		/*if($this->txtBuscar->text != ""){
			$where .= " AND ( nombre LIKE :buscar
						OR user LIKE :buscar )";
			$ct_buscar->Parameters[':buscar'] = "%".$this->txtBuscar->text."%";
			//$Parametros['buscar'] = "%".$this->txtBuscar->text."%";
		}*/
		
		$ct_buscar->Condition = $where;
		$ct_buscar->OrdersBy['id_movimientos'] = 'desc';
        $ct_buscar->Limit = $rows;
        $ct_buscar->Offset = $offset;
		
        $tabla = LMsMovimientos::finder()->findAll($ct_buscar);
        return $tabla;
    }
    
    protected function getRowCount()
    {
        $idsucursales = $this->User->idsucursales;
		$idbodega = $this->cmdBodega2->Text;
		
		$where = " borrado = 0 AND tipo_movimiento = 2 AND id_sucursales = :id_sucursales AND id_bodegas = :id_bodegas";
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Parameters[':id_sucursales'] = $idsucursales;
		$ct_buscar->Parameters[':id_bodegas'] = $idbodega;
		$Parametros = array();
		/*if($this->txtBuscar->text != ""){
			$where .= " AND ( nombre LIKE :buscar
						OR user LIKE :buscar )";
			$ct_buscar->Parameters[':buscar'] = "%".$this->txtBuscar->text."%";
			//$Parametros['buscar'] = "%".$this->txtBuscar->text."%";
		}*/
		
		$ct_buscar->Condition = $where;
        $var = LMsMovimientos::finder()->count($ct_buscar);
		
		$this->linkPdf->NavigateUrl = $this->Service->constructUrl('inventarios.productospdf',$Parametros);
		$visible = $var > 0;
		$this->tpDatos->Visible = $visible;
		$this->tpSinDatos->Visible = !$visible;
		$this->nelementos->Text = $var;
		//Prado::log(TVarDumper::dump($Parametros,1),TLogger::NOTICE,$this->PagePath);
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
			if($row instanceof LMsMovimientos ){
				$this->i ++;
				$item->rowI->lNumero->Text = $this->i;
				$item->rowFecha->fecha_movimiento->value = $row->fecha_movimiento;
				$catalogo2 = LBsCatalogosGenericos::finder()->find("catalogo = ? AND valor = ?",array(2,$row->estatus));
				if($catalogo2 instanceof LBsCatalogosGenericos){
					$item->rowEstatus->lEstatus->Text = '<label class="'.$catalogo2->cssclass.'">'.$catalogo2->opcion.'<label>';
				}else{
					$item->rowEstatus->lEstatus->Text = $row->estatus;
				}
				if($row->estatus >1){
					$item->DeleteColumn->Button->Enabled = false;
					$item->rowBotonos->btnEditar->Enabled = false;
				}
				$item->DeleteColumn->Button->Attributes->onclick='if(!confirm(\'¿Esta seguro de eliminar el movimiento '.$row->fecha_movimiento.' del inventario?\')) return false;';
				$item->DeleteColumn->Button->CssClass = "btn btn-danger fa fa-trash";
				$item->DeleteColumn->Button->Text = " ";
				
				$item->rowBotonos->linkpdf->NavigateUrl = $this->Service->constructUrl('inventarios.listaabastopdf', array("lista" => $row->id_movimientos));
			}
		}
		
        if($item->ItemType==='EditItem')
        {
            
        }
	}
	
	public function itemCreated_Detalle($sender,$param)
    {
        $item=$param->Item;
		$row = $item->Data;
		
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
			if($row instanceof LCtMovimientosInventarios ){
				$this->j ++;
				$item->rowJ->lNumero->Text = $this->j;
				$item->rowPrecio->lPrecioPublico->value = $row->preciopublico;
				
				$item->EditColumn->EditButton->CssClass = "btn btn-success fa fa-edit";
				$item->EditColumn->EditButton->Text = " ";
				
				$item->DeleteColumnDetalle->Button->Attributes->onclick='if(!confirm(\'¿Esta seguro de quitar de la lista '.$row->ms_inventarios->ms_productos->nombre.' ?\')) return false;';
				$item->DeleteColumnDetalle->Button->CssClass = "btn btn-danger fa fa-close";
				$item->DeleteColumnDetalle->Button->Text = " ";
				
				if($row->ms_movimientos->estatus == 1){
					$item->EditColumn->Visible = true;
					$item->DeleteColumnDetalle->visible = true;
				}else{
					$item->EditColumn->Visible = false;
					$item->DeleteColumnDetalle->visible = false;
				}
			}
		}
		
        if($item->ItemType==='EditItem')
        {
            if($row instanceof LCtMovimientosInventarios ){
				$this->j ++;
				$item->rowJ->lNumero->Text = $this->j;
				$item->rowProducto->enabled = false;
				$item->rowCodigo->enabled = false;
				$item->rowPrecio->txtPrecioPublico->Text= $row->preciopublico;
				
				$item->EditColumn->UpdateButton->CssClass = "btn btn-success fa fa-save";
				$item->EditColumn->UpdateButton->Text = " ";
				$item->EditColumn->UpdateButton->CausesValidation="false";
				
				$item->EditColumn->CancelButton->CssClass = "btn btn-warning fa fa-close";
				$item->EditColumn->CancelButton->Text = " ";
				$item->EditColumn->CancelButton->CausesValidation="false";
				
				$item->DeleteColumnDetalle->Button->Attributes->onclick='if(!confirm(\'¿Esta seguro de quitar de la lista '.$row->ms_inventarios->ms_productos->nombre.' ?\')) return false;';
				$item->DeleteColumnDetalle->Button->CssClass = "btn btn-danger fa fa-close";
				$item->DeleteColumnDetalle->Button->Text = " ";
				//Prado::log(TVarDumper::dump($item->EditColumn,1),TLogger::NOTICE,$this->PagePath);
			}
        }
	}
	
    public function changePage($sender,$param)
    {
        $this->dgTabla->CurrentPageIndex=$param->NewPageIndex;
        $offset=$param->NewPageIndex*$this->dgTabla->PageSize;
        //Prado::log(TVarDumper::dump($offset,2),TLogger::NOTICE,$this->PagePath);
        $this->i = $offset;
        $this->dgTabla->DataSource=$this->getDataRows($offset,$this->dgTabla->PageSize);
        $this->dgTabla->dataBind();
    }
	
	public function changePage_Detalle($sender,$param)
    {
        $this->dgTablaDetalle->CurrentPageIndex=$param->NewPageIndex;
        $offset=$param->NewPageIndex*$this->dgTablaDetalle->PageSize;
        //Prado::log(TVarDumper::dump($offset,2),TLogger::NOTICE,$this->PagePath);
        $this->j = $offset;
        $this->dgTablaDetalle->DataSource=$this->getDataRows_Detalle($offset,$this->dgTablaDetalle->PageSize);
        $this->dgTablaDetalle->dataBind();
    }
	
	public function pagerCreated($sender,$param)
    {
        $param->Pager->Controls->insertAt(0,'Pagina(s): ');
		//Prado::log(TVarDumper::dump($param,2),TLogger::NOTICE,$this->PagePath);
    }
	
	public function pagerCreated_Detalle($sender,$param)
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
	
	public function mostrarPanel($modalidad){
		switch($modalidad){
			case 1:
				//mostrar inicio
				$this->tpanelAviso->visible = false;
				$this->Formulario->visible = false;
				
				$this->FormularioDetalle->visible = false;
				$this->BuscarDetalleSinDatos->visible = false;
				$this->BuscarDetalleDatos->visible = false;
				
				$this->Buscador->visible = true;
				$this->tpSinDatos->visible = false;
				$this->tpDatos->visible = false;
				break;
			case 2:
				//mostrar nuevo o editar
				$this->tpanelAviso->visible = false;
				$this->Formulario->visible = true;
				
				$this->FormularioDetalle->visible = false;
				$this->BuscarDetalleSinDatos->visible = false;
				$this->BuscarDetalleDatos->visible = false;
				
				$this->Buscador->visible = false;
				$this->tpSinDatos->visible = false;
				$this->tpDatos->visible = false;
				break;
			case 3:
				//Guardar movimiento
				$this->tpanelAviso->visible = true;
				$this->Formulario->visible = false;
				
				$this->FormularioDetalle->visible = false;
				$this->BuscarDetalleSinDatos->visible = false;
				$this->BuscarDetalleDatos->visible = false;
				
				$this->Buscador->visible = false;
				$this->tpSinDatos->visible = false;
				$this->tpDatos->visible = false;
				break;
			case 4:
				//Mostrar detalle
				$this->tpanelAviso->visible = false;
				$this->Formulario->visible = false;
				
				$this->FormularioDetalle->visible = false;
				$this->BuscarDetalleSinDatos->visible = false;
				$this->BuscarDetalleDatos->visible = false;
				
				$this->Buscador->visible = false;
				$this->tpSinDatos->visible = false;
				$this->tpDatos->visible = false;
				break;
			case 5:
				//Mostrar formulario detalle
				$this->tpanelAviso->visible = false;
				$this->Formulario->visible = false;
				
				$this->FormularioDetalle->visible = true;
				$this->BuscarDetalleSinDatos->visible = false;
				$this->BuscarDetalleDatos->visible = false;
				
				$this->Buscador->visible = false;
				$this->tpSinDatos->visible = false;
				$this->tpDatos->visible = false;
				break;
			default:
				$this->tpanelAviso->visible = false;
				$this->Formulario->visible = false;
				
				$this->FormularioDetalle->visible = false;
				$this->BuscarDetalleSinDatos->visible = false;
				$this->BuscarDetalleDatos->visible = false;
				
				$this->Buscador->visible = true;
				$this->tpSinDatos->visible = false;
				$this->tpDatos->visible = false;
				break;
		}
	}
	
	public function btnExportarXLS_OnClick($sender,$param){
		$idsucursales = $this->User->idsucursales;
		
		$tipo_producto = $this->CbProductos->checked;
		$mayor =  $this->CbMayorCero->checked ;
		$cero =  $this->CbInventarioCero->checked ;
		
		$Parametros = array("nombre" => "%".$this->txtBuscar->Text."%",
							"idbodegas"  => $this->cmdBodega2->Text,
							"tipo"  => $tipo_producto,
							"mayor"  => $mayor,
							"cero"  => $cero,
                            "idsucursales"  => $idsucursales);
		$tabla = $this->Application->Modules['query']->Client->queryForList("vwInventarios_exportar",$Parametros);
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
					->setTitle("Triton - Inventarios")
					->setSubject("Inventario")
					->setDescription("Inventario")
					->setKeywords("Triton, TPV, TPV 2017")
					->setCategory("El sistema 2017");
		
		// Agregar Informacion
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A1', '#')
					->setCellValue('B1', 'Codigo')
					->setCellValue('C1', 'Tipo')
					->setCellValue('D1', 'Producto / Servicio')
					->setCellValue('E1', 'Apartado / Departamento')
					->setCellValue('F1', 'P. Adquirido')
					->setCellValue('G1', 'P. Publico')
					->setCellValue('H1', 'Existencia')
					->setCellValue('I1', 'Unidad');
		$a = 1;
		//Prado::log('-'.TVarDumper::dump($tabla,1),TLogger::NOTICE,$this->PagePath);
		foreach($tabla as $i => $row){
			$a ++;
			//$tiempovisita = $this->User->tiempotrascurrio($row->fecha_cita,($row->fecha_atencion != '' ? $row->fecha_atencion : date('Y-m-d H:i:s')));
			//Prado::log('-'.TVarDumper::dump($tiempovisita,1),TLogger::NOTICE,$this->PagePath);
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A'.$a, $a-1)
						->setCellValue('B'.$a, $row->ms_productos->codigo)
						->setCellValue('C'.$a, ($row->ms_productos->tipo?'Producto':'Servicio'))
						->setCellValue('D'.$a, $row->ms_productos->nombre)
						->setCellValue('E'.$a, $row->ms_productos->ct_departamentos->nombre)
						->setCellValue('F'.$a, $row->precioadquisicion)
						->setCellValue('G'.$a, $row->preciopublico)
						->setCellValue('H'.$a, $row->stock)
						->setCellValue('I'.$a, $row->unidad);
		}
		// Renombrar Hoja
		$objPHPExcel->getActiveSheet()->setTitle('Usuarios del sistema Triton');
		
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