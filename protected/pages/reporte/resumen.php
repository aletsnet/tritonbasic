<?php
class resumen extends TPage
{
    public $layers = "";
    
    public function onInit($param){
		$this->master->titulo->Text = "Mapa";
		$this->master->subtitulo->Text = "Mapa de incidencia";
        $this->title = "Mapa de incidencia";
        
	}
	
	public function onLoad($param)
    {
        if(!$this->IsPostBack){
            
            if($this->User->Roles[0] == 3){
                $this->lfuente->visible = false;
                $this->cfuente->visible = false;
            }else{
                $fuentes = LBsUsuarios::finder()->findAll(" activo = 1 AND borrado = 0 AND id_roles = 3 Order by dependencia asc");
                foreach($fuentes as $i => $v){
                    $fuentes[$i]->dependencia = $v->nombre . " - ". $v->dependencia;
                }
                $this->cmdFuente->DataSource = $fuentes;
                $this->cmdFuente->dataBind();
            }
            
            $this->cmdPrioridad->DataSource = LBsCatalogosGenericos::finder()->findAll(" catalogo = ? AND activo = 1",1);
			$this->cmdPrioridad->dataBind();
            
            $this->cmdArea->DataSource = LCtAreas::finder()->findAll(" activo = 1 Order by area asc");
			$this->cmdArea->dataBind();
            
            $this->cmdTema->DataSource = LBsCatalogosGenericos::finder()->findAll(" catalogo = ? AND activo = 1",4);
			$this->cmdTema->dataBind();
            
            $this->layers = $this->layers_construct();
        }
	}
    
    public function MostarMapa($sender, $param){
        $this->layers = $this->layers_construct();
        $this->lFichaMap->Text = "";
    }
    
    public function layers_construct(){
        $conn = $this->Application->modules['db']->DbConnection;
        $semaforo = 1;
        
        $sqlUsuario = "";
        if($this->User->Roles[0] == 3){
            $sqlUsuario = " AND id_usuarios = " . $this->User->idusuarios;
        }else{
            if($this->cmdFuente->Text > 0){
                $sqlUsuario = " AND id_usuarios = " . $this->cmdFuente->Text;
            }
        }
        
        $sqlPrioridad = "";
        if($this->cmdPrioridad->Text > 0){
            $sqlPrioridad = " AND semaforo = " . $this->cmdPrioridad->Text;
            $semaforo = $this->cmdPrioridad->Text;
        }
        
        $sqlArea = "";
        if($this->cmdArea->Text > 0){
            $sqlArea = " AND id_areas = " . $this->cmdArea->Text;
        }
        $sqlTema = "";
        if($this->cmdTema->Text > 0){
            $sqlTema = " AND tema = " . $this->cmdTema->Text;
        }
        //opcidad
        //prioridad 1
        $arreglo = [];
        $opacidad = 0;
        $sql = 'SELECT id_municipios, COUNT(*) n FROM ms_incidencias a INNER JOIN ct_incidencia_municipio b ON a.id_incidencias = b.id_incidencias WHERE borrado = 0 '.$sqlUsuario.' '.$sqlPrioridad.' '.$sqlArea.' '.$sqlTema.' AND STATUS = 1 GROUP BY id_municipios';
        
        $eco3 = $conn->createCommand($sql);
        $data = $eco3->query();
        foreach($data as $v){
            $arreglo[$v['id_municipios']] = $v['n'];
        }
        
        
        $eco3 = $conn->createCommand('SELECT id_municipios, COUNT(*) n FROM ms_incidencias a INNER JOIN ct_incidencia_seccion b ON a.id_incidencias = b.id_incidencias WHERE borrado = 0 '.$sqlUsuario.' '.$sqlPrioridad.' '.$sqlArea.' '.$sqlTema.'  AND STATUS = 1 GROUP BY id_municipios');
        $data = $eco3->query();
        foreach($data as $v){
            if(key_exists($v['id_municipios'],$arreglo)){
                $arreglo[$v['id_municipios']] += $v['n'];
            }else{
                $arreglo[$v['id_municipios']] = $v['n'];
            }
            
        }
        
        if(count($arreglo)>0){
            $opacidad = max($arreglo);
        }
        
        
        
        //$opacidad = 0;
        
        //color
        $color = [ ["#FFFFFF","#FFFFFF","#FFFFFF","#FFFFFF","#FFFFFF","#FFFFFF","#FFFFFF","#FFFFFF","#FFFFFF","#FFFFFF"],
                   ["#FFFFFF","#ffb3b3","#ff9999","#ff8080","#ff6666","#ff4d4d","#ff3333","#ff1a1a","#ff0000","#e60000"],
                   ["#FFFFFF","#ffffb3","#ffff99","#ffff80","#ffff66","#ffff4d","#ffff33","#ffff1a","#ffff00","#e6e600"],
                   ["#FFFFFF","#99c2ff","#80b3ff","#66a3ff","#4d94ff","#3385ff","#1a75ff","#0066ff","#005ce6","#0066ff"]
                   ];
        
        $layers = RShMunicipiosHidalgo::finder()->findAll(' Order by id_municipios_hidalgo');
        $str_poligonos = "";
        //$semaforo = 0;
		foreach($layers as $layer)
		{
            $estadistito = [];
            $eco1 = $conn->createCommand('SELECT COUNT(*) n FROM ms_incidencias a
                                          INNER JOIN ct_incidencia_municipio b ON a.id_incidencias = b.id_incidencias AND id_municipios = '.$layer->id_municipios_hidalgo.'
                                          WHERE borrado = 0 AND status = 1 '.$sqlUsuario.' '.$sqlPrioridad.'  '.$sqlArea.' '.$sqlTema.' ');
			
            $n = 0;
            $data = $eco1->query();
            foreach($data as $s){
                $n = $s['n'];
            }
            //Prado::log(TVarDumper::dump($preproceso,2),TLogger::NOTICE,$this->PagePath);
            $eco2 = $conn->createCommand('SELECT COUNT(*) n FROM ms_incidencias a
                                          INNER JOIN ct_incidencia_seccion b ON a.id_incidencias = b.id_incidencias AND id_municipios = '.$layer->id_municipios_hidalgo.'
                                          WHERE borrado = 0 AND status = 1 '.$sqlUsuario.' '.$sqlPrioridad.'  '.$sqlArea.' '.$sqlTema.' ');
			//$preproceso = [];
            $data = $eco2->query();
            foreach($data as $s){
                $n += $s['n'];
            }
            
            //Prado::log(":O".TVarDumper::dump($n,2),TLogger::NOTICE,$this->PagePath);
           
            
            $lopacidad = 1;
            if($semaforo > 0){
                if($n != 0){
                    $lopacidad = round(($n / $opacidad ), 1) * 10;
                }
            }
            
            $lopacidad = $lopacidad - 1;
            $str_poligonos.=" municipio".$layer->id_municipios_hidalgo." = map.drawPolygon({
						paths: ".$layer->paths.",
						strokeOpacity: ".$layer->strokeOpacity.",
						strokeWeight: ".$layer->strokeWeight.",
						fillColor: '".$color[$semaforo][$lopacidad]."',
						fillOpacity: 0.7,    
                        mouseover:function(event){
                            var latitud=event.latLng.lat();
                            var longitud=event.latLng.lng();
                            $('#maplMunicipio').html();
							$('#maplMunicipio').html('".$layer->nombre."');
                        },
                        click: function(event){
                            $('#lmunicipio').html('".$layer->nombre."');
                            $('#".$this->idMunicipios->ClientID."').val('".$layer->id_municipios_hidalgo."');
                            $('#".$this->btnFichaMap->ClientID."').click();
                        }
					  });
					  
                      map.addMarker({
						lat: ".$layer->lat.",
						lng: ".$layer->lng.",
                        icon:{url: 'image/nomark.png'},
						label:{text:'".($n > 0 ? "[".$n."]":" ")."',color:'#183F92',fontSize:'11px',background: '#fff'},
					  });";
			
		}
        
        return $str_poligonos;
    }
    
    function btnFichaMap_OnClick($sender, $param){
        if($this->idMunicipios->value > 0){
            $conn = $this->Application->modules['db']->DbConnection;
            $sqlUsuario = "";
            if($this->User->Roles[0] == 3){
                $sqlUsuario = " AND id_usuarios = " . $this->User->idusuarios;
            }
            
            $sqlPrioridad = "";
            if($this->cmdPrioridad->Text > 0){
                $sqlPrioridad = " AND semaforo = " . $this->cmdPrioridad->Text;
                $semaforo = $this->cmdPrioridad->Text;
            }
            
            $sqlArea = "";
            if($this->cmdArea->Text > 0){
                $sqlArea = " AND id_areas = " . $this->cmdArea->Text;
            }
            
            $sqlTema = "";
            if($this->cmdTema->Text > 0){
                $sqlTema = " AND tema = " . $this->cmdTema->Text;
            }
            
            //semaforo
            $semaforo = [1 => 0, 2 => 0, 3 => 0];
            $eco1 = $conn->createCommand('SELECT semaforo,COUNT(*) n FROM ms_incidencias a
                                            INNER JOIN ct_incidencia_municipio b ON a.id_incidencias = b.id_incidencias AND id_municipios = ' . $this->idMunicipios->value . '
                                            WHERE borrado = 0 AND status = 1 '.$sqlUsuario.' '.$sqlPrioridad.' '.$sqlArea.' '.$sqlTema.' group by semaforo  ');
            $data = $eco1->query();
            foreach($data as $s){
                $semaforo[$s['semaforo']] = $s['n'];
            }
            $eco2 = $conn->createCommand('SELECT semaforo, COUNT(*) n FROM ms_incidencias a
                                          INNER JOIN ct_incidencia_seccion b ON a.id_incidencias = b.id_incidencias AND id_municipios = ' . $this->idMunicipios->value . '
                                          WHERE borrado = 0 AND status = 1 '.$sqlUsuario.' '.$sqlPrioridad.' '.$sqlArea.' '.$sqlTema.' group by semaforo ');
            $data = $eco2->query();
            foreach($data as $s){
                $semaforo[$s['semaforo']] += $s['n'];
            }
            $lSemaforo = '<p>---------------Semáforo------------------</p>';
            $lSemaforo .= '<p class="text-aqua">Informativo:        <label>'.$semaforo[3].'</label> <a class="btn btn-success btn-xs" href="'.$this->Service->constructUrl('incidencias.buscar',['m' => $this->idMunicipios->value, 'e' => 3, 'f' => $this->cmdFuente->Text]).'" target="_blank"> <i class="fa fa-external-link-square "></i></a></p>';
            $lSemaforo .= '<p class="text-yellow">Alerta amarilla: <label>'.$semaforo[2].'</label> <a class="btn btn-success btn-xs" href="'.$this->Service->constructUrl('incidencias.buscar',['m' => $this->idMunicipios->value, 'e' => 2, 'f' => $this->cmdFuente->Text]).'" target="_blank"> <i class="fa fa-external-link-square"></i></a></p>';
            $lSemaforo .= '<p class="text-red">Alerta roja:        <label>'.$semaforo[1].'</label> <a class="btn btn-success btn-xs" href="'.$this->Service->constructUrl('incidencias.buscar',['m' => $this->idMunicipios->value, 'e' => 1, 'f' => $this->cmdFuente->Text]).'" target="_blank"> <i class="fa fa-external-link-square"></i></a></p>';
            
            //$this->lFichaMap->Text = $lSemaforo;
            
            //tema
            $tema = [];
            $eco1 = $conn->createCommand('SELECT tema, (SELECT opcion FROM `bs_catalogos_genericos` WHERE valor = tema AND catalogo =4 ) ltema,COUNT(*) n FROM ms_incidencias a
                                            INNER JOIN ct_incidencia_municipio b ON a.id_incidencias = b.id_incidencias AND id_municipios = ' . $this->idMunicipios->value . '
                                            WHERE borrado = 0 AND status = 1 '.$sqlUsuario.' '.$sqlPrioridad .' '.$sqlArea.' '.$sqlTema.' group by tema  ');
            $data = $eco1->query();
            foreach($data as $s){
                //$tema[$s['tema']] = [];
                $tema[$s['tema']] = ['n' => $s['n'], 'l' => $s['ltema']];
            }
            $eco2 = $conn->createCommand('SELECT tema, (SELECT opcion FROM `bs_catalogos_genericos` WHERE valor = tema AND catalogo =4 ) ltema, COUNT(*) n FROM ms_incidencias a
                                          INNER JOIN ct_incidencia_seccion b ON a.id_incidencias = b.id_incidencias AND id_municipios = ' . $this->idMunicipios->value . '
                                          WHERE borrado = 0 AND status = 1 '.$sqlUsuario.' '.$sqlPrioridad .' '.$sqlArea.' '.$sqlTema.' group by tema ');
            $data = $eco2->query();
            foreach($data as $s){
                $tema[$s['tema']]['n'] += $s['n'];
                $tema[$s['tema']]['l'] += $s['ltema'];
            }
            
            $lTema = '<p>---------------Tema------------------</p>';
            foreach($tema as $r => $l){
                $lTema .= '<p>'.$l['l'].':  <label>'.$l['n'].'</label> <a class="btn btn-success btn-xs" href="'.$this->Service->constructUrl('incidencias.buscar',['m' => $this->idMunicipios->value, 't' => $r, 'f' => $this->cmdFuente->Text]).'" target="_blank"> <i class="fa fa-external-link-square"></i></a></p>';
            }
            $this->lFichaMap->Text = $lSemaforo.$lTema;
            
            
            //Prado::log(":O".TVarDumper::dump($n,2),TLogger::NOTICE,$this->PagePath);
        }
    }
}