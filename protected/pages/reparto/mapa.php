<?php

class mapa extends TPage
{
	
	public function onInit($param){
		$this->master->titulo->Text = "Reparto";
		$this->master->subtitulo->Text = "Servicio de reparto";
        $this->title = "Servicio de reparto";
		
		//Prado::log(TVarDumper::dump($this->autopostback,1),TLogger::NOTICE,$this->PagePath);
	}
	
    public function onLoad($param)
    {
		$v = $this->User->ServicioActivo("20");
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
            $rows = LMsVentas::finder()->findAll(" estatus = 3 AND tipo_venta=3 ");
            $this->rpListVentas->DataSource = $rows;
            $this->rpListVentas->dataBind();
            $this->pnNoSearcProduct->Visible = (count($rows) < 1);
            //$this->pnBuscarMap->Visible = false;
        }
    }
    
    public function rpListVentas_DataBound($sender, $param){
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
			$row = $item->Data;
            if($row instanceof LMsVentas){
                $item->lTelefono->Text = $row->ms_clientes->telefono;
                $item->lNombre->Text = $row->ms_clientes->nombre;
            }
        }
    }
    
    public function btnMostrar_OnClick($sender, $param){
        $item = $sender->namingContainer;
		$keys = $this->rpListVentas->DataKeys;
		//Prado::log(TVarDumper::dump($keys[$item->itemIndex],1),TLogger::NOTICE,$this->PagePath);
		$row  = LMsVentas::finder()->find("id_ventas = ?", $keys[$item->itemIndex]);
		if($row instanceof LMsVentas){
            $this->hdidventas->value = $row->id_ventas;
            $this->hdidclientes->value = $row->id_clientes;
			$this->lNombre->Text = $row->ms_clientes->nombre;
            $this->lTelefono->Text = $row->ms_clientes->telefono;
            $this->hdGeoreferencia->Value = $row->ms_clientes->geo_referencia;
			$this->txtDireccion->Text = $row->ms_clientes->direccion;
            $this->txtReferencia->Text = $row->ms_clientes->referencia;
        }
        $this->jsMap->Text = '<script> $("#pnBuscarMap").show(); $("#btnMapCal").click(); </script>';
    }
    
    public function btnSave_OnClick($sender, $param){
        $row  = LMsClientes::finder()->find("id_clientes = ?", $this->hdidclientes->value);
		if($row instanceof LMsClientes){
            $row->geo_referencia = $this->hdGeoreferencia->Value;
			$row->direccion = $this->txtDireccion->Text;
            $row->referencia = $this->txtReferencia->Text;
            $row->save();
        }
        $this->jsMap->Text = '';
    }
    
    public function btnEntregado_OnClick($sender, $param){
        $row  = LMsVentas::finder()->find("id_ventas = ?", $this->hdidventas->value);
		if($row instanceof LMsVentas){
            $row->estatus = 7;
            $row->fecha_termina = date("Y-m-d H:i:s");
            $row->save();
        }
        
        $rows = LMsVentas::finder()->findAll(" estatus = 3 AND tipo_venta=3 ");
        $this->rpListVentas->DataSource = $rows;
        $this->rpListVentas->dataBind();
        $this->pnNoSearcProduct->Visible = (count($rows) < 1);
        
        $this->jsMap->Text = '<script> $("#pnBuscarMap").hide(); </script>';
    }
    
    public function btnBlack_OnClick($sender, $param){
        
    }
}