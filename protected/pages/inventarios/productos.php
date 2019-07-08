<?php
class Productos extends TPage
{
	public $i = 0;
	public $j = 0;
	public $perimiso_actualizar = false;
	public $importe = 0;
	
	public function onInit($param){
		$this->master->titulo->Text = "Productos y servicios";
		$this->master->subtitulo->Text = "Inventarios";
        $this->title = "Productos y servicios - Inventarios";
		
		//Prado::log(TVarDumper::dump($this->autopostback,1),TLogger::NOTICE,$this->PagePath);
	}
	
    public function onLoad($param)
    {
		$v = $this->User->ServicioActivo("8");
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
			if( $this->request['e']=='e'){
				$row  = LMsInventarios::finder()->find("borrado = 0 AND id_inventarios = ?", $this->request['p']);
				//Prado::log(TVarDumper::dump($row,1),TLogger::NOTICE,$this->PagePath);
				if($row instanceof LMsInventarios ){
					//Prado::log(TVarDumper::dump($row->ms_productos,1),TLogger::NOTICE,$this->PagePath);
					if($row->ms_productos->foto != ""){
						$this->foto->ImageUrl = $row->ms_productos->foto;
						$this->file->value = $row->ms_productos->foto;
					}
					
					$this->cmdDepartamento->DataSource = LCtDepartamentos::finder()->findAll(" borrado = 0 ");
					$this->cmdDepartamento->dataBind();
					
					$this->cmdBodega->DataSource = LCtBodegas::finder()->findAll(" id_sucursales = ? AND borrado = 0",$this->User->idsucursales);;
					$this->cmdBodega->dataBind();
					
					//$this->cmdClaveUnidad->DataSource = LSatUnidades::finder()->findAll(" ORDER BY nombre");
					//$this->cmdClaveUnidad->dataBind();
					
					$this->txtCodigo->Text = $row->ms_productos->codigo;
					$this->txtNombre->Text = $row->ms_productos->nombre;
					$this->txtDescripcion->Text = $row->ms_productos->descripcion;
					$this->ChProducto1->checked = $row->ms_productos->tipo;
					$this->ChProducto0->checked = !$row->ms_productos->tipo;
					$this->cmdDepartamento->Text = $row->ms_productos->id_departamentos;
					$this->cmdBodega->text = $row->id_bodegas;
					$this->id_productos->value = $row->ms_productos->id_productos;
					$this->id_inventarios->value = $row->id_inventarios;
					$this->txtPrecioAdquisicion->text = $row->precioadquisicion;
					$this->txtPrecioPublico->text = $row->preciopublico;
					$this->txtStock->text = $row->stock;
					$this->txtMinimo->text = $row->minimo_stock;
					$this->txtMaximo->text = $row->maximo_stock;
					$this->txtUnidad->text = $row->unidad;
					
					$this->codigoactivo->Text = '<label class="btn btn-success"><i class="fa fa-check"></i></label>';
					$this->codigovalido->value = "1";
					
					//SAT
					if($row->ms_productos->claveprodserv != ""){
						$this->txtClaveProdServ->text = $row->ms_productos->claveprodserv;
						$this->lSatProducto->text     = $row->ms_productos->sat_productos_servicios->descripcion;
					}
					if($row->ms_productos->claveunidad != ""){
						$this->txtClaveUnidad->text   = $row->ms_productos->claveunidad;
						$this->lSatUnidaded->text     = $row->ms_productos->sat_unidades->nombre;
					}
					
					$impuesto = LBsCatalogosGenericos::finder()->findAll(" catalogo = 16 AND activo = 1 ");
					$this->rpImpuesto->DataSource = $impuesto;
					$this->rpImpuesto->dataBind();
				}
				
				$this->tpanelAviso->visible = false;
				$this->Formulario->visible = true;
				$this->tpDatos->visible = false;
				$this->tpSinDatos->visible = false;
				$this->Buscador->visible = false;
			}else{
				$this->tpanelAviso->visible = false;
				$this->Formulario->visible = false;
				
				$this->cmdBodega2->DataSource = LCtBodegas::finder()->findAll(" id_sucursales = ? AND borrado = 0",$this->User->idsucursales);
				$this->cmdBodega2->dataBind();
				$this->cmdBodega2->SelectedIndex = 0;
				
				$this->dgTabla->VirtualItemCount = $this->getRowCount();
				$this->dgTabla->DataSource=$this->getDataRows(0,$this->dgTabla->PageSize);
				$this->dgTabla->dataBind();
				
				/*$impuesto = LBsCatalogosGenericos::finder()->findAll(" catalogo = 16 AND activo = 1 ");
				$this->rpImpuesto->DataSource = $impuesto;
				$this->rpImpuesto->dataBind();*/
			}
			//cmdClaveUnidad
			$this->txtConcepto->text = $this->Application->Parameters["satProductosServicios"];
        }
    }
    
	public function btnBlanco_OnClick($sender, $param){
		//Prado::log(TVarDumper::dump($this,1),TLogger::NOTICE,$this->PagePath);
	}
	
    public function btnBuscar_Producto_OnClick($sender, $param){
        if($this->IsValid)
        {
            $row = LMsProductos::finder()->find(" borrado = 0 AND codigo = ? ", $this->txtCodigo->Text);
            if($row instanceof LMsProductos){
				$this->codigoactivo->Text = '<label class="btn btn-success"><i class="fa fa-check"></i></label>';
				$this->codigovalido->value = "1";
				
                $this->id_productos->value = $row->id_productos;
                if($row->foto != ""){
                    $this->foto->ImageUrl = $row->foto;
                    $this->file->value = $row->foto;
                }
                $this->txtNombre->Text = $row->nombre;
                $this->txtDescripcion->Text = $row->descripcion;
                $this->{'ChProducto'.$row->tipo}->checked = true;
                $this->cmdDepartamento->Text = $row->id_departamentos;
				$rox = LMsInventarios::finder()->find(" borrado = 0 AND id_productos = ? ", $row->id_productos);
				if($rox instanceof LMsInventarios){
					$this->cmdBodega->text = $rox->id_bodegas;
					//$this->id_productos->value = $row->ms_productos->id_productos;
					$this->id_inventarios->value = $rox->id_inventarios;
					$this->txtPrecioAdquisicion->text = $rox->precioadquisicion;
					$this->txtPrecioPublico->text = $rox->preciopublico;
					$this->txtStock->text = $rox->stock;
					$this->txtUnidad->text = $rox->unidad;
				}
            }else{
				$codigo = LCtCodigosreservados::finder()->find(" borrado = 0 AND codigo = ? ", $this->txtCodigo->Text);
				if($codigo instanceof LCtCodigosreservados){
					$this->codigoactivo->Text = '<label class="btn btn-danger"><i class="fa fa-close"></i></label>';
					$this->codigovalido->value = "";
				}else{
					$this->codigoactivo->Text = '<label class="btn btn-success"><i class="fa fa-check"></i></label>';
					$this->codigovalido->value = "1";
				}
			}
        }
    }
    
    public function btnAgregar_departamentos_OnClick($sender, $param){
        //$this->ModalDepartamentos->open();
        $this->RpDepartamento->DataSource = LCtDepartamentos::finder()->findAll(" borrado = 0 ");
        $this->RpDepartamento->dataBind();
    }
    
    public function btnBorrar_departamentos_OnClick($sender, $param){
        $item = $sender->namingContainer;
		$keys = $this->RpDepartamento->DataKeys;
		//Prado::log(TVarDumper::dump($keys[$item->itemIndex],1),TLogger::NOTICE,$this->PagePath);
		$row  = LCtDepartamentos::finder()->find("borrado = 0 AND id_departamentos = ?", $keys[$item->itemIndex]);
        if($row instanceof LCtDepartamentos){
            $row->borrado = 1;
            $row->save();
        }
        
        $rowsDepartamento = LCtDepartamentos::finder()->findAll(" borrado = 0 ");
        $this->RpDepartamento->DataSource = $rowsDepartamento;
        $this->RpDepartamento->dataBind();
        
        $this->cmdDepartamento->DataSource = $rowsDepartamento;
        $this->cmdDepartamento->dataBind();
    }
    
    public function btnUsar_departamentos_OnClick($sender, $param){
        $item = $sender->namingContainer;
		$keys = $this->RpDepartamento->DataKeys;
        //Prado::log(TVarDumper::dump($keys[$item->itemIndex],1),TLogger::NOTICE,$this->PagePath);
        $this->cmdDepartamento->text = $keys[$item->itemIndex];
        //$this->ModalDepartamentos->close();
    }
    
    public function RpDepartamento_DataBound($sender, $param){
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            if($item->Data instanceof LCtDepartamentos){
                $this->i ++;
                $item->lnumero->Text = $this->i;
                $item->btnBorrar_departamentos->Attributes->onclick = 'if(!confirm("¿Esta seguro de eliminar el departamento '.$item->Data->nombre.' ?")) return false;';
            }
        }
    }
    
    public function btnGuardar_departamentos_OnClick($sender, $param){
        if($this->IsValid)
        {
            $rows = LCtDepartamentos::finder()->count(" borrado = 0  AND nombre = ?", trim($this->txtDepartemanto->text));
            if($rows == 0){
                $row = new LCtDepartamentos;
                $row->nombre = trim($this->txtDepartemanto->text);
                $row->save();
                $this->txtDepartemanto->text = "";
            }
            
            $rowsDepartamento = LCtDepartamentos::finder()->findAll(" borrado = 0 ");
            $this->RpDepartamento->DataSource = $rowsDepartamento;
            $this->RpDepartamento->dataBind();
            
            $this->cmdDepartamento->DataSource = $rowsDepartamento;
            $this->cmdDepartamento->dataBind();
        }
    }
    
    public function btnNuevo_OnClick($sender, $param){
		$this->foto->ImageUrl = 'image/producto.png';
		$this->file->value    = '';
		
		$this->cmdDepartamento->DataSource = LCtDepartamentos::finder()->findAll(" borrado = 0 ");
		$this->cmdDepartamento->dataBind();
		$this->cmdDepartamento->Text = "";
		
		$this->cmdBodega->DataSource = LCtBodegas::finder()->findAll(" id_sucursales = ? AND borrado = 0",$this->User->idsucursales);
		$this->cmdBodega->dataBind();
		$this->cmdBodega->Text = "";
		
		$impuesto = LBsCatalogosGenericos::finder()->findAll(" catalogo = 16 AND activo = 1 ");
		$this->rpImpuesto->DataSource = $impuesto;
		$this->rpImpuesto->dataBind();
		
		//$this->cmdClaveUnidad->DataSource = LSatUnidades::finder()->findAll(" ORDER BY nombre");
		//$this->cmdClaveUnidad->dataBind();
		//$this->cmdClaveUnidad->Text = "XUN";
		
		$this->txtCodigo->Text            = "";
		$this->txtNombre->Text            = "";
		$this->txtDescripcion->Text       = "";
		$this->ChProducto1->checked       = false;
		$this->ChProducto0->checked       = false;
		$this->id_productos->value        = "";
		$this->id_inventarios->value      = "";
		$this->txtPrecioAdquisicion->text = "";
		$this->txtPrecioPublico->text     = "";
		$this->txtStock->text             = "";
		$this->txtMinimo->text            = "";
		$this->txtMaximo->text            = "";
		$this->txtUnidad->text            = "U";
		
		$this->codigoactivo->Text = '<label class="btn btn-info btn-flat"><i class="fa fa-asterisk"></i></label>';
		$this->codigovalido->value = "0";
			
        $this->tpanelAviso->visible = false;
		$this->Formulario->visible = true;
        $this->tpDatos->visible = false;
        $this->tpSinDatos->visible = false;
        $this->Buscador->visible = false;
        
		//SAT
		$this->txtClaveProdServ->text = "";
		$this->lSatProducto->text     = "";
		$this->txtClaveUnidad->text   = "";
		$this->lSatUnidaded->text     = "";
		
        //$this->id_productos->value = "";
        //$this->id_inventarios->value = "";
        //$this->txtNombre->text = "";
        //$this->txtCargo->text = "";
    }
    
	public function btnEditar_OnClick($sender, $param){
		$item = $sender->namingContainer;
		$keys = $this->dgTabla->DataKeys;
		
		$row  = LMsInventarios::finder()->find("borrado = 0 AND id_inventarios = ?", $keys[$item->itemIndex]);
		//Prado::log(TVarDumper::dump($row,1),TLogger::NOTICE,$this->PagePath);
		if($row instanceof LMsInventarios ){
			//Prado::log(TVarDumper::dump($row->ms_productos,1),TLogger::NOTICE,$this->PagePath);
			if($row->ms_productos->foto != ""){
				$this->foto->ImageUrl = $row->ms_productos->foto;
				$this->file->value = $row->ms_productos->foto;
			}
			
			$this->cmdDepartamento->DataSource = LCtDepartamentos::finder()->findAll(" borrado = 0 ");
			$this->cmdDepartamento->dataBind();
            
            $this->cmdBodega->DataSource = LCtBodegas::finder()->findAll(" id_sucursales = ? AND borrado = 0",$this->User->idsucursales);;
            $this->cmdBodega->dataBind();
			
			//$this->cmdClaveUnidad->DataSource = LSatUnidades::finder()->findAll(" ORDER BY nombre");
			//$this->cmdClaveUnidad->dataBind();
			
            $this->txtCodigo->Text = $row->ms_productos->codigo;
            $this->txtNombre->Text = $row->ms_productos->nombre;
            $this->txtDescripcion->Text = $row->ms_productos->descripcion;
            $this->ChProducto1->checked = $row->ms_productos->tipo;
            $this->ChProducto0->checked = !$row->ms_productos->tipo;
            $this->cmdDepartamento->Text = $row->ms_productos->id_departamentos;
            $this->cmdBodega->text = $row->id_bodegas;
            $this->id_productos->value = $row->ms_productos->id_productos;
            $this->id_inventarios->value = $row->id_inventarios;
            $this->txtPrecioAdquisicion->text = $row->precioadquisicion;
            $this->txtPrecioPublico->text = $row->preciopublico;
            $this->txtStock->text = $row->stock;
			$this->txtMinimo->text = $row->minimo_stock;
			$this->txtMaximo->text = $row->maximo_stock;
            $this->txtUnidad->text = $row->unidad;
			
			$this->codigoactivo->Text = '<label class="btn btn-success"><i class="fa fa-check"></i></label>';
			$this->codigovalido->value = "1";
			
			//SAT
			if($row->ms_productos->claveprodserv != ""){
				$this->txtClaveProdServ->text = $row->ms_productos->claveprodserv;
				$this->lSatProducto->text     = $row->ms_productos->sat_productos_servicios->descripcion;
			}
			if($row->ms_productos->claveunidad != ""){
				$this->txtClaveUnidad->text   = $row->ms_productos->claveunidad;
				$this->lSatUnidaded->text     = $row->ms_productos->sat_unidades->nombre;
			}
		}
		
		$this->tpanelAviso->visible = false;
		$this->Formulario->visible = true;
        $this->tpDatos->visible = false;
        $this->tpSinDatos->visible = false;
        $this->Buscador->visible = false;
		//Prado::log(TVarDumper::dump($keys[$item->itemIndex],1),TLogger::NOTICE,$this->PagePath);
	}
	
	public function Validador_ChProducto($sender, $param){
		$valido = false;
		if($this->ChProducto0->checked == true || $this->ChProducto1->checked == true)
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
	
	public function OnSelected_cmdSucursal($sender, $param){
		$this->cmdAccesos->DataSource = LCtAccesos::finder()->findAll(" activo = 1 AND id_sucursal = ? ",$sender->Text);
		$this->cmdAccesos->dataBind();
	}
	
    public function btnGuardar_OnClick($sender, $param){
        $id_productos = $this->id_productos->value;
        $id_inventarios = $this->id_inventarios->value;
		$id_sucursales = $this->User->idsucursales;
        
		if($this->IsValid)
        {
            //ms_productos
            $row = LMsProductos::finder()->find(" borrado = 0 AND id_productos = ? ", $id_productos);
            if($row instanceof LMsProductos){
                if($this->file->value != ""){
                    $row->foto = $this->file->value;
                }
                $row->nombre           = $this->txtNombre->Text;
                $row->descripcion      = $this->txtDescripcion->Text;
                $row->tipo             = $this->ChProducto1->checked;
                $row->id_departamentos = $this->cmdDepartamento->Text;
				//SAT
				if($this->txtClaveProdServ->text != "")
					$row->claveprodserv    = $this->txtClaveProdServ->text;
				if($this->txtClaveUnidad->text != "")
					$row->claveunidad      = $this->txtClaveUnidad->text;
                
				$row->save();
                //$this->id_productos->value = $row->id_productos;
            }else{
                $row = new LMsProductos;
                if($this->file->value != ""){
                    $row->foto = $this->file->value;
                }
                $row->codigo           = $this->txtCodigo->Text;
                $row->nombre           = $this->txtNombre->Text;
                $row->descripcion      = $this->txtDescripcion->Text;
                $row->tipo             = $this->ChProducto1->checked;
                $row->id_departamentos = $this->cmdDepartamento->Text;
				//SAT
				if($this->txtClaveProdServ->text != "")
					$row->claveprodserv    = $this->txtClaveProdServ->text;
				if($this->txtClaveUnidad->text != "")
					$row->claveunidad      = $this->txtClaveUnidad->text;
                
				$row->save();
                $this->id_productos->value = $row->id_productos;
                $id_productos = $row->id_productos;
            }
            
            //ms_inventarios
            $row2 = LMsInventarios::finder()->find(" borrado = 0 AND id_inventarios = ? ", $id_inventarios);
            if($row2 instanceof LMsInventarios){
                $row2->id_sucursales = $id_sucursales;
                $row2->id_bodegas = $this->cmdBodega->text;
                $row2->id_productos = $id_productos;
                $row2->precioadquisicion = (double) $this->txtPrecioAdquisicion->text;
                $row2->preciopublico = (double) $this->txtPrecioPublico->text;
                $row2->stock = $this->txtStock->text;
				if($this->txtMinimo->text != ""){ $row2->minimo_stock = $this->txtMinimo->text; }
				if($this->txtMaximo->text != ""){ $row2->maximo_stock = $this->txtMaximo->text; }
                $row2->unidad = $this->txtUnidad->text;
                $row2->save();
                $this->id_productos->value = $row->id_productos;
            }else{
                $row2 = new LMsInventarios;
                $row2->id_sucursales = $id_sucursales;
                $row2->id_bodegas = $this->cmdBodega->text;
                $row2->id_productos = $id_productos;
                $row2->precioadquisicion = (double) $this->txtPrecioAdquisicion->text;
                $row2->preciopublico = (double) $this->txtPrecioPublico->text;
                $row2->stock = $this->txtStock->text;
				if($this->txtMinimo->text != ""){ $row2->minimo_stock = $this->txtMinimo->text; }
				if($this->txtMaximo->text != ""){ $row2->maximo_stock = $this->txtMaximo->text; }
                $row2->unidad = $this->txtUnidad->text;
                $row2->save();
            }
            //impuestos
			//limpia lista de impuesto de este producto
			$impuestoskeys = $this->rpImpuesto->DataKeys;
			$row_impuestos = LCtProductoImpuestos::finder();
			$row_impuestos->deleteAll('id_productos = ?', $this->id_productos->value);
			//agrega el impuesto
			$importe = (double) $this->txtPrecioPublico->Text;
			foreach($this->rpImpuesto->items as $item){
				if($item->chAplica->Checked){
					//Prado::log(TVarDumper::dump($row2_tasa,1),TLogger::NOTICE,$this->PagePath);
					$row_tasa = LSatTasaCuota::finder()->find(" nimpuesto = ? AND tasa like ? ",[(string) $impuestoskeys[$item->ItemIndex],(string) $item->cmdTaza->Text]);
					if($row_tasa instanceof LSatTasaCuota){
						$row_impuesto = new LCtProductoImpuestos;
						//$row_impuesto->id_producto_impuestos;
						$row_impuesto->id_productos  = $this->id_productos->value;
						$row_impuesto->id_tasa_cuota = $row_tasa->id_tasa_cuota;
						$row_impuesto->impuesto      = $impuestoskeys[$item->ItemIndex];
						$row_impuesto->valor         = $item->cmdTaza->Text;
						$row_impuesto->incluido      = $item->chIncluido->Checked;
						//$row_impuesto->borrado
						$row_impuesto->save();
					}else{
						Prado::log("Ocurrio un error en los impuestos",TLogger::NOTICE,$this->PagePath);
					}
				}
			}
			
			$this->rpImpuesto->footer->ltotal->Text = "$ " . number_format($importe,2);
			
			
			$this->foto->ImageUrl = "image/producto.png";
			$this->file->value = "";
            $this->txtCodigo->Text = "";
			$this->txtNombre->Text = "";
            $this->txtDescripcion->Text = "";
            $this->ChProducto0->checked = false;
            $this->ChProducto1->checked = false;
            $this->cmdDepartamento->Text = "";
            //$this->cmdBodega->text = ;
            $this->id_productos->value = "";
            $this->id_inventarios->value = "";
            $this->txtPrecioAdquisicion->text = "";
            $this->txtPrecioPublico->text = "";
            $this->txtStock->text = "";
            $this->txtUnidad->text = "U";
        
			$this->Formulario->visible = false;
			$this->tpanelAviso->visible = true;
			$this->Buscador->visible = true;
			
			//SAT
			$this->txtClaveProdServ->text = "";
			$this->lSatProducto->text     = "";
			$this->txtClaveUnidad->text   = "";
			$this->lSatUnidaded->text     = "";
			
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
	
	public function deleteItem($sender,$param)
    {
        $keyid = $this->dgTabla->DataKeys[$param->Item->ItemIndex];
		//$row = LMsVisitas::finder()->findByPk();
		$row = LMsInventarios::finder()->find(" borrado = 0 AND id_inventarios = ? ", $keyid);
        $row->borrado = 1;
        $row->save();
        $this->getRows(false,($this->dgTabla->CurrentPageIndex*$this->dgTabla->PageSize));
		
		Prado::log("[".$this->User->idusuarios.
                       "][".$this->User->idusuarios."][IP:".
                       $_SERVER['REMOTE_ADDR'].
                       "][Elimino id_inventario: ".$keyid."]",
                       TLogger::NOTICE,
                       $this->PagePath);
		
    }
	
	protected function getDataRows($offset,$rows)
    {
        $idsucursales = $this->User->idsucursales;
		$tipo = 0;
		if($this->cmdServicios->Text != " ")
			$tipo = (int) $this->cmdServicios->Text;
		
        $Parametros = array("nombre"       => "%".$this->txtBuscar->Text."%",
							"idbodegas"    => $this->cmdBodega2->Text,
							"tipo"         => $tipo,
                            "idsucursales"  => $idsucursales,
							"rows"        => $rows,
							"offset"      => $offset);
		$tabla = $this->Application->Modules['query']->Client->queryForList("vwInventarios",$Parametros);
        return $tabla;
    }
    
    protected function getRowCount()
    {
        $idsucursales = $this->User->idsucursales;
		$tipo = 0;
		if($this->cmdServicios->Text != " ")
			$tipo = (int) $this->cmdServicios->Text;
		
        $Parametros = array("nombre"       => "%".$this->txtBuscar->Text."%",
							"idbodegas"    => $this->cmdBodega2->Text,
							"tipo"         => $tipo,
                            "idsucursales" => $idsucursales);
		$var = $this->Application->Modules['query']->Client->queryForObject("vwInventarios_count",$Parametros);
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
        $this->i = $offset;
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
			if($row instanceof LMsInventarios ){
				$this->i ++;
				$item->rowI->lNumero->Text = $this->i;
				
				$item->rowImagen->Style="width: 64px;";
				
				if($row->ms_productos->foto != "")
					$item->rowImagen->foto->ImageUrl = $row->ms_productos->foto;
					
				$item->rowServicios->lTipo->Text = ($row->ms_productos->tipo? 'Producto' : 'Servicio');
				
				$item->rowProducto->lCodigo->Text = $row->ms_productos->codigo;
				$item->rowProducto->lProducto->Text = $row->ms_productos->nombre;
				
                $item->rowValor->lValor->value = $row->preciopublico;
				
                $item->rowPrecioPublico->lPrecioPublico->value = $row->preciopublico;
				
                $item->rowBart->lStock->Text = " <b>" . $row->stock . "</b> <small>" . $row->unidad."</small>";
				
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
				
				$item->rowBotonos->btnEditar->NavigateUrl = $this->Service->constructUrl('inventarios.productos',['e' => 'e', 'p' => $row->id_inventarios]);
				
				$item->rowEditar->EditButton->CssClass = "btn bg-maroon";
				$item->rowEditar->EditButton->Text     = '<i class="fa fa-indent"></i>';
				$item->rowEditar->enabled = ($this->User->idroles ==1 || $this->User->idroles ==3);
				$item->rowBotonos->btnEditar->enabled = ($this->User->idroles ==1 || $this->User->idroles ==3);
				
				$item->DeleteColumn->Button->Attributes->onclick='if(!confirm(\'¿Esta seguro de eliminar ['.$row->ms_productos->codigo.'] '.$row->ms_productos->nombre.' del inventario?\')) return false;';
				$item->DeleteColumn->Button->CssClass = "btn btn-danger";
				$item->DeleteColumn->Button->Text = '<i class="fa fa-trash"></i>';
				$item->DeleteColumn->Button->enabled = ($this->User->idroles ==1 || $this->User->idroles ==3);
				
				//sat
				if($row->ms_productos->claveprodserv != ""){
					$item->rowDatosFiscales->lSatConcepto->Text = "<b><small>".$row->ms_productos->claveprodserv . "</small></b> " . $row->ms_productos->sat_productos_servicios->descripcion;
				}
				
				if($row->ms_productos->claveunidad != ""){
					$item->rowDatosFiscales->lSatUnidad->Text   = "<b><small>".$row->ms_productos->claveunidad . "</small></b> " . $row->ms_productos->sat_unidades->nombre;
				}
				
				$rows_impuesto = [];
				$rows_impuesto = LCtProductoImpuestos::finder()->findAll(" id_productos = ? ",[$row->ms_productos->id_productos]);
				$list = "";
				$valor = 0;
				$publico = $row->preciopublico;
				$imp = 0;
				foreach($rows_impuesto as $i => $rimpuesto){
					//Prado::log(TVarDumper::dump($rimpuesto,1),TLogger::NOTICE,$this->PagePath);
					$list .= " <b><small>". $rimpuesto->sat_tasa_cuota->impuesto .": </small></b>" . ($rimpuesto->valor * 100) ." % ".($rimpuesto->incluido?"(incluido)":"")." <br />";
					if($rimpuesto->incluido){
						if(!$valor > 0){
							$a = (float) 0;
							$a = 1 +$rimpuesto->valor;
							$valor = $publico / $a;
							//Prado::log($valor." = " . $publico ." / (" . $a . ")",TLogger::NOTICE,$this->PagePath);
						}
						$imp += $valor * $rimpuesto->valor;
					}else{
						if(!$valor > 0){
							$valor = $publico;
						}
						$imp += ($valor * $rimpuesto->valor);
						$publico = $publico + $imp;
					}
					Prado::log($row->preciopublico . " V:" . $valor . " | " . $rimpuesto->valor . " " . $imp,TLogger::NOTICE,$this->PagePath);
				}
				$item->rowDatosFiscales->lSatImpuestos->Text = $list;
				
				$item->rowValor->lValor->value                 = $valor;
				$item->rowPrecioPublico->lPrecioPublico->value = $publico;
				
				
			}
		}
		
        if($item->ItemType==='EditItem')
        {
			$row = $item->Data;
			if($row instanceof LMsInventarios ){
				$this->i ++;
				$item->rowI->lNumero->Text = $this->i;
				$item->rowImagen->Style="width: 64px;";
				if($row->ms_productos->foto != "")
					$item->rowImagen->foto->ImageUrl = $row->ms_productos->foto;
				$item->rowServicios->lTipo->Text = ($row->ms_productos->tipo? 'Producto' : 'Servicio');
				$item->rowProducto->lCodigo->Text = $row->ms_productos->codigo;
				$item->rowProducto->lProducto->Text = $row->ms_productos->nombre;
				
                //$item->rowPrecioAdquisicion->txtPrecioAdquisicion->text = $row->precioadquisicion;
                $item->rowPrecioPublico->txtPrecioPublico->text = $row->preciopublico;
                $item->rowBart->txtStock->Text = $row->stock ;
				
				//$item->rowPrecioAdquisicion->txtPrecioAdquisicion->cssclass = 'form-control';
                $item->rowPrecioPublico->txtPrecioPublico->cssclass = 'form-control';
                $item->rowBart->txtStock->cssclass = 'form-control';
				
				$item->rowEditar->UpdateButton->CssClass = "btn btn-success";
				$item->rowEditar->UpdateButton->Text     = '<i class="fa fa-save"></i>';
				
				$item->rowEditar->CancelButton->CssClass = "btn bg-orange";
				$item->rowEditar->CancelButton->Text     = '<i class="fa fa-close"></i>';;
				
				$item->DeleteColumn->Button->Attributes->onclick='if(!confirm(\'¿Esta seguro de eliminar ['.$row->ms_productos->codigo.'] '.$row->ms_productos->nombre.' del inventario?\')) return false;';
				$item->DeleteColumn->Button->CssClass = "btn btn-danger fa fa-trash";
				$item->DeleteColumn->Button->Text = " ";
			}
        }
	}
	
	public function editItem($sender, $param){
		$this->dgTabla->EditItemIndex = $param->Item->ItemIndex;
		$this->getRows(false,($this->dgTabla->CurrentPageIndex*$this->dgTabla->PageSize));
	}
	
	public function saveItem($sender, $param){
		$item=$param->Item;
		$idkey = $this->dgTabla->DataKeys[$item->ItemIndex];
		$row = LMsInventarios::finder()->find(" id_inventarios = ?",$idkey);
		if($row instanceof LMsInventarios){
			//$row->precioadquisicion = $item->rowPrecioAdquisicion->txtPrecioAdquisicion->text;
			$row->preciopublico     = $item->rowPrecioPublico->txtPrecioPublico->text;
			$row->stock             = $item->rowBart->txtStock->Text;
			$row->save();
		}
		$this->dgTabla->EditItemIndex = -1;
		$this->getRows(false,($this->dgTabla->CurrentPageIndex*$this->dgTabla->PageSize));
	}
	
	public function cancelItem($sender, $param){
		$this->dgTabla->EditItemIndex = -1;
		$this->getRows(false,($this->dgTabla->CurrentPageIndex*$this->dgTabla->PageSize));
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
		$tipo = 0;
		if($this->cmdServicios->Text != " ")
			$tipo = (int) $this->cmdServicios->Text;
		
		$Parametros = array("nombre" => "%".$this->txtBuscar->Text."%",
							"idbodegas"  => $this->cmdBodega2->Text,
							"tipo"  => $tipo,
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
	
	public function btnBuscarProdServ_OnClick($sender, $param){
		$this->RpClaveProdServ->VirtualItemCount = $this->RpClaveProdServ_getRowCount();
		$this->RpClaveProdServ->DataSource = $this->RpClaveProdServ_getDataRows(0,$this->RpClaveProdServ->PageSize);
        $this->RpClaveProdServ->dataBind();
	}
	
	public function btnBuscarConcepto_OnClick($sender, $param){
		$this->RpClaveProdServ->VirtualItemCount = $this->RpClaveProdServ_getRowCount();
		$this->RpClaveProdServ->DataSource = $this->RpClaveProdServ_getDataRows(0,$this->RpClaveProdServ->PageSize);
        $this->RpClaveProdServ->dataBind();
	}
	
	public function RpClaveProdServ_DataBound($sender, $param){
		$item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
			$row = $item->Data;
			if($row instanceof LSatProductosServicios ){
				$item->rowClaveprodserv->lCodigo->Text = $row->claveprodserv;
				$item->rowConcepto->lConcepto->Text = $row->descripcion;
			}
			//Prado::log(TVarDumper::dump($row,1),TLogger::NOTICE,$this->PagePath);
		}
	}
	
	public function btnUsarClaveProdServ_OnClick($sender, $param){
		$item = $sender->namingContainer;
		$keys = $this->RpClaveProdServ->DataKeys;
		$row = $this->RpClaveProdServ->items[$item->itemIndex];
        $this->txtClaveProdServ->Text = $keys[$item->itemIndex];
		$this->lSatProducto->Text     = $row->rowConcepto->lConcepto->Text;
        //Prado::log(TVarDumper::dump($row->rowConcepto->lConcepto->Text,1),TLogger::NOTICE,$this->PagePath);
	}
	
	protected function RpClaveProdServ_getDataRows($offset,$rows)
    {	
		$criteria = new TActiveRecordCriteria;
		$criteria->Condition = "(claveprodserv like :descripcion OR descripcion like :descripcion OR similares like :similares) ";
		$criteria->Parameters[':descripcion'] = '%'.$this->txtConcepto->Text.'%';
		$criteria->Parameters[':similares']   = '%'.$this->txtConcepto->Text.'%';
		$criteria->OrdersBy['claveprodserv'] = 'asc';
		$criteria->OrdersBy['descripcion'] = 'asc';
		$criteria->Limit  = $rows;
		$criteria->Offset = $offset;
		$tabla = LSatProductosServicios::finder()->findAll($criteria);
        return $tabla;
    }
    
    protected function RpClaveProdServ_getRowCount()
    {
		$criteria = new TActiveRecordCriteria;
		$criteria->Condition = "(claveprodserv like :descripcion OR descripcion like :descripcion OR similares like :similares) ";
		$criteria->Parameters[':descripcion'] = '%'.$this->txtConcepto->Text.'%';
		$criteria->Parameters[':similares']   = '%'.$this->txtConcepto->Text.'%';
		$var = LSatProductosServicios::finder()->Count($criteria);
        
		$this->lnumero->Text = "Encontrados: " . number_format($var);
		//Prado::log(TVarDumper::dump($Parametros,1),TLogger::NOTICE,$this->PagePath);
        return $var;
    }
	
	public function RpClaveProdServ_changePage($sender,$param)
    {
        $this->RpClaveProdServ->CurrentPageIndex = $param->NewPageIndex;
        $offset=$param->NewPageIndex*$this->RpClaveProdServ->PageSize;
        //$this->i = $offset;
        $this->RpClaveProdServ->DataSource = $this->RpClaveProdServ_getDataRows($offset,$this->RpClaveProdServ->PageSize);
        $this->RpClaveProdServ->dataBind();
    }
	
	public function RpClaveProdServ_pagerCreated($sender,$param)
    {
        $param->Pager->Controls->insertAt(0,'Pagina(s): ');
		//Prado::log(TVarDumper::dump($param,2),TLogger::NOTICE,$this->PagePath);
    }
	
	public function btnBuscarClaveUnidad_OnClick($sender, $param){
		$this->RpClaveUnidad->VirtualItemCount = $this->RpClaveUnidad_getRowCount();
		$this->RpClaveUnidad->DataSource = $this->RpClaveUnidad_getDataRows(0,$this->RpClaveUnidad->PageSize);
        $this->RpClaveUnidad->dataBind();
	}
	
	public function RpClaveUnidad_DataBound($sender, $param){
		$item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
			$row = $item->Data;
			if($row instanceof LSatUnidades ){
				$item->rowClaveUnidad->lCodigo->Text = $row->claveunidad;
				$item->rowNombreUnidad->lUnidad->Text = $row->nombre;
			}
			//Prado::log(TVarDumper::dump($row,1),TLogger::NOTICE,$this->PagePath);
		}
	}
	
	protected function RpClaveUnidad_getDataRows($offset,$rows)
    {	
		$criteria = new TActiveRecordCriteria;
		$criteria->Condition = " claveunidad like :descripcion OR nombre like :descripcion OR descripcion like :descripcion";
		$criteria->Parameters[':descripcion'] = '%'.$this->txtUnidadNombre->Text.'%';
		$criteria->OrdersBy['nombre'] = 'asc';
		$criteria->Limit  = $rows;
		$criteria->Offset = $offset;
		$tabla = LSatUnidades::finder()->findAll($criteria);
        return $tabla;
    }
    
    protected function RpClaveUnidad_getRowCount()
    {
		$criteria = new TActiveRecordCriteria;
		$criteria->Condition = " claveunidad like :descripcion OR nombre like :descripcion OR descripcion like :descripcion";
		$criteria->Parameters[':descripcion'] = '%'.$this->txtUnidadNombre->Text.'%';
		$var = LSatUnidades::finder()->Count($criteria);
        
		$this->lnumero2->Text = "Encontrados: " . number_format($var);
		//Prado::log(TVarDumper::dump($Parametros,1),TLogger::NOTICE,$this->PagePath);
        return $var;
    }
	
	public function RpClaveUnidad_changePage($sender,$param)
    {
        $this->RpClaveUnidad->CurrentPageIndex = $param->NewPageIndex;
        $offset=$param->NewPageIndex*$this->RpClaveUnidad->PageSize;
        //$this->i = $offset;
        $this->RpClaveUnidad->DataSource = $this->RpClaveUnidad_getDataRows($offset,$this->RpClaveUnidad->PageSize);
        $this->RpClaveUnidad->dataBind();
    }
	
	public function RpClaveUnidad_pagerCreated($sender,$param)
    {
        $param->Pager->Controls->insertAt(0,'Pagina(s): ');
		//Prado::log(TVarDumper::dump($param,2),TLogger::NOTICE,$this->PagePath);
    }
	
	public function btnUsarClaveUnidad_OnClick($sender, $param){
		$item = $sender->namingContainer;
		$keys = $this->RpClaveUnidad->DataKeys;
		$row  = $this->RpClaveUnidad->items[$item->itemIndex];
        $this->txtClaveUnidad->text = $keys[$item->itemIndex];
		$this->lSatUnidaded->Text   = $row->rowNombreUnidad->lUnidad->Text;
        //Prado::log(TVarDumper::dump($keys[$item->itemIndex],1),TLogger::NOTICE,$this->PagePath);
	}
	
	public function rpImpuesto_OnItemCreated($sender, $param){
		$item=$param->Item;
		switch($item->ItemType){
			case 'Header':
				$this->importe = (double) 0.0;
				break;
			case 'Footer':
				$item->ltotal->Text = "$ " . number_format($this->importe,2);
				break;
			case 'Item':
			case 'AlternatingItem':
				$row = $item->Data;
				if($row instanceof LBsCatalogosGenericos ){
					$this->j ++;
					$item->lnum->text = $this->j;
					$item->limpuesto->text = $row->opcion;
					
					$row_impuesto = LCtProductoImpuestos::finder()->find(" impuesto = ? AND id_productos = ? ",[$row->valor, $this->id_productos->value]);
					if($row_impuesto instanceof LCtProductoImpuestos){
						$item->ltaza->visible      = false;
						$item->cmdTaza->visible    = true;
						$item->chIncluido->enabled = true;
						
						$tasa = LSatTasaCuota::finder()->findAll(" nimpuesto = ? AND activo=1  ORDER BY tasa ",$row->valor);
						$item->cmdTaza->DataSource = $tasa;
						$item->cmdTaza->dataBind();
						$item->cmdTaza->Text = $row_impuesto->valor;
						$item->chAplica->checked = true;
						$item->chIncluido->checked = $row_impuesto->incluido;
					}else{
						$item->ltaza->text = "N/A";
						$item->cmdTaza->visible = false;
						$item->chIncluido->enabled = false;
					}
					$impuesto = (double) $item->cmdTaza->Text;
					$importe  = (double) $this->txtPrecioPublico->Text;
					
					$importe = $importe * $impuesto;
					$item->limporte->text = "$ " . number_format($importe,2);
					$item->vimporte->value = number_format($importe,2);
					$this->importe += $importe;
					//$this->importe = (double) 0.0;
				}
				break;
			default:
				Prado::log(TVarDumper::dump($item->ItemType,1),TLogger::NOTICE,$this->PagePath);
				break;
		}
	}
	
	public function cmdTaza_onChange($sender, $param){
		$item = $sender->namingContainer;
		$keys = $this->rpImpuesto->DataKeys;
		
		$impuesto = (double) $item->cmdTaza->Text;
		$importe  = (double) $this->txtPrecioPublico->Text;
		
		if($item->chIncluido->Checked){
			if($impuesto > 0){
				$item->limporte->text = ($importe * $impuesto) / (1 + $impuesto) ;
			}else{
				$item->limporte->text = 0;
			}
		}else{
			$item->limporte->text = $importe * $impuesto;
		}
		$item->vimporte->value = number_format($item->limporte->text,2);
		$item->limporte->text  = "$ " . number_format($item->limporte->text,2);
		$this->calcularImporte();
	}
	
	public function chAplica_onChange($sender, $param){
		//Prado::log(TVarDumper::dump($param,1),TLogger::NOTICE,$this->PagePath);
		$item = $sender->namingContainer;
		$keys = $this->rpImpuesto->DataKeys;
		
		//$item->ltaza->visible   = false;
		//$item->cmdTaza->visible = true;
		if($sender->Checked){
			$tasa = LSatTasaCuota::finder()->findAll(" nimpuesto = ? AND activo=1  ORDER BY tasa ",$keys[$item->itemIndex]);
			$item->cmdTaza->DataSource = $tasa;
			$item->cmdTaza->dataBind();
			$item->cmdTaza->SelectedIndex = 0;
			
			$impuesto = (double) $item->cmdTaza->Text;
			$importe  = (double) $this->txtPrecioPublico->Text;
			$item->limporte->text = $importe * $impuesto;
			$item->vimporte->value = number_format($item->limporte->text,2);
			$item->limporte->text = "$ " . number_format($item->limporte->text,2);
		}
		$item->chIncluido->enabled = $sender->Checked;
		$item->ltaza->visible      = !$sender->Checked;
		$item->cmdTaza->visible    = $sender->Checked;
		//$keys[$item->index]
		//Prado::log(TVarDumper::dump($keys[$item->itemIndex],1),TLogger::NOTICE,$this->PagePath);
		$this->calcularImporte();
	}
	
	public function chIncluido_onChange($sender, $param){
		$item = $sender->namingContainer;
		$keys = $this->rpImpuesto->DataKeys;
		
		$impuesto = (double) $item->cmdTaza->Text;
		if($item->chIncluido->Checked){
			if($impuesto > 0){
				$item->limporte->text = ($this->txtPrecioPublico->Text * $impuesto) / (1 + $impuesto) ;
			}else{
				$item->limporte->text = 0;
			}
		}else{
			$item->limporte->text = $this->txtPrecioPublico->Text * $impuesto;
		}
		
		$item->vimporte->value = number_format($item->limporte->text,2);
		$item->limporte->text = "$ " . number_format($item->limporte->text,2);
		$this->calcularImporte();
	}
	
	function calcularImporte(){
		$importe = (double) $this->txtPrecioPublico->Text;
		foreach($this->rpImpuesto->items as $item){
			if(!$item->chIncluido->Checked){
				$importe += $item->vimporte->value;
			}
		}
		
		$this->rpImpuesto->footer->ltotal->Text = "$ " . number_format($importe,2);
		//Prado::log(TVarDumper::dump($this->rpImpuesto->footer->ltotal,1),TLogger::NOTICE,$this->PagePath);
	}
}