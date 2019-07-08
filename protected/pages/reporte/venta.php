<?php
class venta extends TPage
{
    public $MakeGrafic = "";
    public $i = 0;
    public $cssColor = ["#ff4000",
                        "#ff8000",
                        "#ffbf00",
                        "#ffff00",
                        "#bfff00",
                        "#80ff00",
                        "#40ff00",
                        "#00ff00",
                        "#00ff40",
                        "#00ff80",
                        "#00ffbf",
                        "#00ffff",
                        "#00bfff",
                        "#0080ff",
                        "#0040ff",
                        "#0000ff",
                        "#4000ff",
                        "#8000ff",
                        "#bf00ff",
                        "#ff00ff",
                        "#ff00bf",
                        "#ff0080",
                        "#ff0040",
                        "#ff0000"];
    public $dias = 0;
    public $series = [];
    public $labels = [];
    public $ltotal = 0;
    
    public function onInit($param){
		$this->master->titulo->Text = "Articulo";
		$this->master->subtitulo->Text = "Articulo ";
        $this->title = "Top articulo";
        
	}
	
	public function onLoad($param)
    {
        $v = $this->User->ServicioActivo("15");
        if(!$v){
			Prado::log("[".$_SERVER['REMOTE_ADDR']."][".$this->User->idusuarios.'][Permiso denegado]',TLogger::NOTICE,$this->PagePath);
            $url = $this->Service->constructUrl('sistema.403');
            $this->Response->redirect($url);
        }
        if(!$this->IsPostBack){
            //Prado::log(TVarDumper::dump($this->fechainicio,2),TLogger::NOTICE,$this->PagePath);
            /*$ltop = LBsCatalogosGenericos::finder()->findAll(" catalogo = 10 AND activo = 1 ");
            $this->cmdTop->DataSource = $ltop;
            $this->cmdTop->dataBind();
            $this->cmdTop->SelectedIndex = 2;*/
            
            $fecha1 = Date("Y-m-d",strtotime("-1 month"));
            $fecha2 = Date("Y-m-d");
            $this->fechainicio->Text = $this->User->fecha($fecha1);
            $this->fechafinal->Text  = $this->User->fecha($fecha2);
            
            $dia1 = date_create($fecha1);
            $dia2 = date_create($fecha2);
            $interval = date_diff($dia2,$dia1);
            $this->dias = $interval->format('%a');
            
            /*$datos = ['fechai' => $fecha1 . ' 00:00:00',
                      'fechaf' => $fecha2 . ' 23:59:59',
                      'limite' => (int) $this->cmdTop->Text];*/
            //$larticulos = $this->Application->Modules['query']->Client->queryForList("vwTopArticulo",$datos);
            $lTipoVentas = LBsCatalogosGenericos::finder()->findAll(" catalogo = 3 AND activo = 1 AND valor > 2 Order by valor ");
            $this->RpLista->DataSource = $lTipoVentas;
			$this->RpLista->dataBind();
            
            $this->grafica();
        }
    }
    
    public function itemCreated_RpLista($sender, $param){
        $item=$param->Item;
        Prado::log(TVarDumper::dump($item->ItemType,3),TLogger::NOTICE,$this->PagePath);
        switch($item->ItemType){
            case 'Header':
                $this->ltotal = 0;
                break;
            case 'Item':
            case 'AlternatingItem':
                $row = $item->Data;
                if($row instanceof LBsCatalogosGenericos){
                    $css = $this->cssColor[$this->i];
                    $item->lcolor->Text    = '<i class="fa fa-circle" style="color: '.$css.';"></i>';
                    $item->lnombre->Text   = $row->opcion;
                    //$item->lcantidad->Text = $row->valor;
                    
                    $this->i ++;
                    if($this->i >= 15){
                        $this->i = 0;
                    }
                    
                    $datos = ['fechai' => $this->User->fecha($this->fechainicio->Text) . ' 00:00:00',
                              'fechaf' => $this->User->fecha($this->fechafinal->Text)  . ' 23:59:59',
                              'estatus' => $row->valor];
                    $lventas = [];
                    $serie = [];
                    if($this->dias <= 31){
                        $lventas = $this->Application->Modules['query']->Client->queryForList("vwTopVentasDia",$datos);
                    }else{
                        if($this->dias <= 93){
                            $lventas = $this->Application->Modules['query']->Client->queryForList("vwTopVentasSemana",$datos);
                        }else{
                            if($this->dias <= 890){
                                $lventas = $this->Application->Modules['query']->Client->queryForList("vwTopVentasMes",$datos);
                            }else{
                                $lventas = $this->Application->Modules['query']->Client->queryForList("vwTopVentasAnio",$datos);
                            }
                        }
                    }
                    
                    foreach($lventas as $r){
                        if(!key_exists($r['f'],$this->labels)){
                            $this->labels[$r['f']] = 0;
                        }
                    }
                    //$serie = $this->labels; //[$r['f'] => $r['t']];
                    $ltotal = 0;
                    foreach($lventas as $r){
                        $serie[$r['f']] = $r['t'];
                        $ltotal += $r['t'];
                    }
                    $this->ltotal += $ltotal;
                    $lserie = ['color'   => $css,
                               'idserie' => $row->valor,
                               'serie'   => $serie];
                    $this->series[$row->valor] = $lserie;
                    $item->lcantidad->Text = "$ " . number_format($ltotal);
                    //Prado::log(TVarDumper::dump($this->series,3),TLogger::NOTICE,$this->PagePath);
                }
                break;
            case 'Footer':
                $item->ltotal->Text = "$ " . number_format($this->ltotal);
                break;
		}
    }
    
    public function btnBuscar_OnClick($sender, $param){
        $fecha1 = $this->User->fecha($this->fechainicio->Text);
        $fecha2 = $this->User->fecha($this->fechafinal->Text);
        $dia1 = date_create($fecha1);
        $dia2 = date_create($fecha2);
        $interval = date_diff($dia2,$dia1);
        $this->dias = $interval->format('%a');
        
        $lTipoVentas = LBsCatalogosGenericos::finder()->findAll(" catalogo = 3 AND activo = 1 AND valor > 2 Order by valor ");
        $this->RpLista->DataSource = $lTipoVentas;
        $this->RpLista->dataBind();
        
        $this->grafica();
    }
    
    public function grafica(){
        //Prado::log(TVarDumper::dump($this->labels,3),TLogger::NOTICE,$this->PagePath);
        //Prado::log(TVarDumper::dump($this->series,3),TLogger::NOTICE,$this->PagePath);
        //ksort($this->labels);
        
        $labels = "";
        foreach($this->labels as $key => $value){
            $labels = $labels . ($labels != "" ? "," : "") . "'".$key."'";
        }
        
        $datasets = "";
        
        foreach($this->series as $key => $value){
            $series = $this->labels;
            $serie  = "";
            foreach($value['serie'] as $k => $v){
                $series[$k] = $v;
            }
            
            foreach($series as $l => $n){
                $serie .= ($serie != "" ? "," : "") . $n;
            }
            
            //$this->series
            $data = "{
                    label               : '$key',
                    fillColor           : '".$value['color']."',
                    strokeColor         : '".$value['color']."',
                    pointColor          : '".$value['color']."',
                    pointStrokeColor    : '".$value['color']."',
                    pointHighlightFill  : '#fff',
                    pointHighlightStroke: '".$value['color']."',
                    data                : [".$serie."]
                  }
                  ";
            $datasets .= ($datasets != "" ? "," : "") . $data;
        }
        
        $this->MakeGrafic = "
            var areaChartData = {
                labels  : [$labels],
                datasets: [$datasets],
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: 'Chart.js Line Chart'
                    }
                }
            }
            
            var areaChartOptions = {
                
            }
            
            var lineChartCanvas          = $('#lineChart').get(0).getContext('2d')
            var lineChart                = new Chart(lineChartCanvas)
            var lineChartOptions         = areaChartOptions
            lineChartOptions.datasetFill = false
            lineChart.Line(areaChartData, lineChartOptions)";
        
    }
}