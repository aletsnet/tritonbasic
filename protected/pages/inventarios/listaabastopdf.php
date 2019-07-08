<?php
class listaabastopdf extends TPage
{
    public function onLoad($param)
    {
		$v = $this->User->ServicioActivo("9,10,11");
        if(!($v)){
			Prado::log(
				"[".$_SERVER['REMOTE_ADDR'].'][Permiso denegado]',
				TLogger::NOTICE,
				$this->PagePath
			);
            $url = $this->Service->constructUrl('sistema.403');
            $this->Response->redirect($url);
        }
        if(!$this->IsPostBack)
        {
            //$this->request['param']
            $idsucursales = $this->User->idsucursales;
            
            $keyid = $this->request['lista'];
            
            $catalogo1_crudo = LBsCatalogosGenericos::finder()->findAll("catalogo = ? AND activo = 1",1);
            $catalogo1 = array();
            foreach($catalogo1_crudo as $ri => $rv){
                $catalogo1[$rv->valor] = $rv;
            }
            
            $where = " borrado = 0 AND id_movimientos = :id_movimientos ";
            $ct_buscar = new TActiveRecordCriteria;
            $ct_buscar->Parameters[':id_movimientos'] = $keyid;
            $ct_buscar->Condition = $where;
            $rowMain = LMsMovimientos::finder()->find($ct_buscar);
            $documento_html = "";
            //Prado::log(TVarDumper::dump($rowMain,1),TLogger::NOTICE,$this->PagePath);
            if($rowMain instanceof LMsMovimientos){
                $estatus_label = "";
				$catalogo2 = LBsCatalogosGenericos::finder()->find("catalogo = ? AND valor = ?",array(2,$rowMain->estatus));
				if($catalogo2 instanceof LBsCatalogosGenericos){
					$estatus_label = $catalogo2->opcion.' <img src="'.$catalogo2->picture.'" width="12">';
				}else{
					$estatus_label = $row->estatus;
				}
                $ct_buscar->OrdersBy['id_movimientos_inventarios'] = 'asc';
                
                $tabla = LCtMovimientosInventarios::finder()->findAll(" borrado = 0 AND id_movimientos = ?",$keyid);
                $rows = count($tabla);
                
                $documento_html .= '<h2 align="center" style="font-size: 90%;">Lista de movimiento ('.$catalogo1[$rowMain->tipo_movimiento]->opcion.') <img src="'.$catalogo1[$rowMain->tipo_movimiento]->picture.'" width="12"> </h2>';
                $documento_html .= '<h4 align="right"> </h4>
                                    <table width="100%" border="0" cellspacing="1" cellpadding="2" style="font-family:Verdana, Geneva, sans-serif;">
                                        <thead>
                                            <tr>
                                                <th style="font-size: 80%;" colspan="6">Estatus: '.$estatus_label.' </th>
                                            </tr>
                                            <tr>
                                                <th style="font-size: 80%;" colspan="3">Creado: '.$this->User->fecha($rowMain->fecha_movimiento).' </th>
                                                <th style="font-size: 80%; text-align: right;" colspan="3"> Impreso: '.date('d/m/Y h:i A').' </th>
                                            </tr>
                                            <tr>
                                                <th style="font-size: 80%; width:10%; background-color: #d9d9d9; text-align: center;">#</th>
                                                <th style="font-size: 80%; width:15%; background-color: #d9d9d9; text-align: center;">Codigo</th>
                                                <th style="font-size: 80%; width:25%; background-color: #d9d9d9; text-align: center;">Producto</th>
                                                <th style="font-size: 80%; width:30%; background-color: #d9d9d9; text-align: center;">Apartado / Departamento</th>
                                                <th style="font-size: 80%; width:10%; background-color: #d9d9d9; text-align: center;">P. Publico</th>
                                                <th style="font-size: 80%; width:10%; background-color: #d9d9d9; text-align: center;">Cantidad</th>
                                            </tr>
                                        </thead>
                                        <tbody>';
                $nrows = 5;
                $nrow = 0;
                $i = 0;
                $color_row="#ffffff";
                foreach($tabla as $i => $row){
                    //documentos
                    $html_documentos = "";
                    $nrow ++;
                    $i++;
                    $documento_html .= '        <tr>
                                                    <td style="width:10%; background-color: '.$color_row.'; text-align: center; font-size: 60%;">
                                                        '.$i.'
                                                    </td>
                                                    <td style="width:15%; background-color: '.$color_row.'; font-size: 60%;">
                                                        '.$row->ms_inventarios->ms_productos->codigo.'
                                                    </td>
                                                    <td style="width:25%; background-color: '.$color_row.'; font-size: 75%;">
                                                         '.$row->ms_inventarios->ms_productos->nombre.'
                                                    </td>
                                                    <td style="width:30%; background-color: '.$color_row.'; font-size: 75%;">
                                                         '.$row->ms_inventarios->ms_productos->ct_departamentos->nombre.'
                                                    </td>
                                                    <td style="width:10%; background-color: '.$color_row.'; text-align: right; font-size: 85%;">
                                                        $ '.number_format($row->preciopublico,2).'
                                                    </td>
                                                    <td style="width:10%; background-color: '.$color_row.'; text-align: right; font-size: 85%;">
                                                         '.$row->cantidad.'
                                                    </td>
                                                </tr>';
                    $color_row = ($color_row=="#ffffff" ? "#f2f2f2" : "#ffffff");
                    
                }
                
                if($rows == 0){
                    $documento_html .= '		<tr>
                                                    <td colspan="6">
                                                        No hay elementos que mostrar
                                                    </td>
                                                </tr>';
                }
                $documento_html .= '	</tbody>';
                
                $documento_html .= '	<tfoot>
                                            <tr>
                                                <td colspan="6" align="right">
                                                    Registros: '.$rows.'
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>';
                
                //echo $documento_html;
            }
            
            Prado::using('Application.modulos.tcpdfpa');
            // L => Vertical , H => Horizontal
            $pdf = new tcpdfpa('H', PDF_UNIT, 'LETTER', true, 'UTF-8', false);
            $pdf->titulo = $this->User->lsucursal;
			$pdf->subtitulo = $this->Application->Parameters["LemaTienda"];
            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('AletsNet');
            $pdf->SetTitle('Triton - Movimietos');
            $pdf->SetSubject('Lista de movimiento en inventario');
            $pdf->SetKeywords('Lista, PDF, ficha');
            
            // set default header data
            $pdf->SetHeaderData("", 12, "", "");
            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            
            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            
            //set margins
            $pdf->SetMargins(10, 28, 15);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            
            $pdf->setFontSubsetting(true);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->AddPage();
            $pdf->writeHTML($documento_html, true, false, true, false, '');
            // Close and output PDF document
            // This method has several options, check the source code documentation for more information.
            $fecha = date("d.m.y H:i:s");
            $pdf->Output('ListaUsuario'.$fecha.'.pdf', 'I');
            
            Prado::log("[".$this->User->idusuarios.
                           "][IP:".
                           $_SERVER['REMOTE_ADDR'].
                           "][Mostrar Documento PDF]",
                           TLogger::NOTICE,
                           $this->Page->PagePath);
            
            exit(0);
        }
    }
}