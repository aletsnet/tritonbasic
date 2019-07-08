<?php
class actividad extends TPage
{
	public $i = 0;
	public $perimiso_actualizar = false;
	
	public function onInit($param){
		$this->Title = "Actividad de usuarios - Sistema";
        $this->master->titulo->Text = "Actividad de usuarios";
		$this->master->subtitulo->Text = "Sistema";
	}
    
    public function onLoad($param)
    {
		$v = $this->User->ServicioActivo("3");
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
            $this->dgTabla->VirtualItemCount = $this->getRowCount();
            $this->dgTabla->DataSource=$this->getDataRows(0,$this->dgTabla->PageSize);
            $this->dgTabla->dataBind();
        }
    }
    
    protected function getDataRows($offset,$rows)
    {
        //$idareas = $this->User->idarea;
		$where = " status = 1 ";
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Condition = $where;
		$ct_buscar->OrdersBy['fecha_ultima'] = 'asc';
        $ct_buscar->Limit = $rows;
        $ct_buscar->Offset = $offset;
		
        $tabla = LBsUsuariosActivos::finder()->findAll($ct_buscar);
		//Prado::log(TVarDumper::dump($tabla,1),TLogger::NOTICE,$this->PagePath);
        return $tabla;
    }
    
    protected function getRowCount()
    {
        //$idareas = $this->User->idarea;
		$where = " status = 1";
		$ct_buscar = new TActiveRecordCriteria;
		$ct_buscar->Condition = $where;
        $var = LBsUsuariosActivos::finder()->count($ct_buscar);
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
			if($row instanceof LBsUsuariosActivos ){
				$this->i ++;
				$item->rowI->lNumero->Text = $this->i;
				$item->rowImagen->Style="width: 64px;";
				if($row->bs_usuarios->foto != "")
					$item->rowImagen->foto->ImageUrl = $row->bs_usuarios->foto;
                $tiempo = $this->User->tiempotrascurrio($row->fecha_ultima ,date('Y-m-d H:i:s'));
                $segundos = strtotime("now") - strtotime($row->fecha_ultima);
                $lstatus = "";
                if($segundos <= 480){
                    $lstatus = 'bg-green';
                }elseif(480 < $segundos && $segundos <= 960){
                    $lstatus = 'bg-yellow';
                }else{
                    $lstatus = 'bg-red';
                }
                $item->rowEstatus->estatus->Text = '<span class="badge '.$lstatus.'">'.$tiempo.'</span>';
				
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
        $this->dgTabla->DataSource=$this->getDataRows($offset,$this->dgTabla->PageSize);
        $this->dgTabla->dataBind();
    }
	
	public function pagerCreated($sender,$param)
    {
        $param->Pager->Controls->insertAt(0,'Pagina(s): ');
		//Prado::log(TVarDumper::dump($param,2),TLogger::NOTICE,$this->PagePath);
    }
    
}