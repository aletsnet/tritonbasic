<?php
class movimientospdf extends TPage
{
    public function onLoad($param)
    {
		$v = $this->User->ServicioActivo(9);
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
            $idbodega = $this->request['id_bodegas'];
            $id_cortes = $this->request['id_cortes'];
            $buscar = $this->request['buscar'];
            
            $tipos = [1=>"Abasto", 2=>"Mermas", 3=>"Total"];
            $tipo_movimiento = $this->request['tipo'];
            if($tipo_movimiento == ""){
                $tipo_movimiento = 1;
            }
            
            $where = " borrado = 0 AND tipo_movimiento = $tipo_movimiento AND id_sucursales = :id_sucursales AND id_bodegas = :id_bodegas AND id_cortes = :id_cortes";
            $ct_buscar = new TActiveRecordCriteria;
            $ct_buscar->Parameters[':id_sucursales'] = $idsucursales;
            $ct_buscar->Parameters[':id_bodegas'] = $idbodega;
            $ct_buscar->Parameters[':id_cortes'] = $id_cortes;
            
            if($buscar != ""){
                $where .= " AND descripcion LIKE :buscar ";
                $ct_buscar->Parameters[':buscar'] = "%".$buscar."%";
            }
            
            $ct_buscar->Condition = $where;
            $tabla = LMsMovimientos::finder()->findAll($ct_buscar);
            
            $rows = count($tabla);
            $documento_html = "";
            
            $documento_html .= '<h4 align="center">Movimientos de '.$tipos[$tipo_movimiento].'</h4>';
            $documento_html .= '<h4 align="right" style="font-size: 60%; ">'.$this->User->fechacompleta($this->User->fecha(date('Y-m-d H:i:s'))).'</h4>
                                <table width="100%" border="0" cellspacing="2" cellpadding="2" style="font-family:Verdana, Geneva, sans-serif;">
                                    <thead>
                                        <tr>
                                            <th style="font-size: 60%; width:15%; background-color: #d9d9d9; text-align: center;">#</th>
											<th style="font-size: 60%; width:40%; background-color: #d9d9d9; text-align: center;">Descripción</th>
                                            <th style="font-size: 60%; width:15%; background-color: #d9d9d9; text-align: center;">Fecha</th>
                                            <th style="font-size: 60%; width:15%; background-color: #d9d9d9; text-align: center;">Articulos</th>
                                            <th style="font-size: 60%; width:15%; background-color: #d9d9d9; text-align: center;">Estatus</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
            $nrows = 5;
            $nrow = 0;
            $i = 0;
            $color_row="#ffffff";
            foreach($tabla as $i => $row){
                //documentos
                $where2 = " borrado = 0 AND id_movimientos = :id_movimientos ";
                $ct_buscar2 = new TActiveRecordCriteria;
                $ct_buscar2->Parameters[':id_movimientos'] = $row->id_movimientos;
                $ct_buscar2->Condition = $where2;
                $nregistros = LCtMovimientosInventarios::finder()->count($ct_buscar2);
                
                $lestatus = "";
                $catalogo2 = LBsCatalogosGenericos::finder()->find("catalogo = ? AND valor = ?",[2,$row->estatus]);
				if($catalogo2 instanceof LBsCatalogosGenericos){
					$lestatus = $catalogo2->opcion;
				}else{
					$lestatus = "N/C";
				}
                
                $html_documentos = "";
                $nrow ++;
                $i++;
                $documento_html .= '        <tr>
                                                <td style="width:15%; background-color: '.$color_row.'; text-align: center; font-size: 60%;">
                                                    '.$i.'
                                                </td>
                                                <td style="width:40%; background-color: '.$color_row.'; font-size: 60%;">
                                                    '.$row->descripcion.'
                                                </td>
                                                <td style="width:15%; background-color: '.$color_row.'; font-size: 60%;">
                                                    '.$row->fecha_registro.'
                                                </td>
                                                <td style="width:15%; background-color: '.$color_row.'; font-size: 60%;">
                                                     '.$nregistros.'
                                                </td>
                                                <td style="width:15%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
													'.$lestatus.'
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
            
            
            Prado::using('Application.modulos.tcpdfpa');
            // L => Vertical , H => Horizontal
            $pdf = new tcpdfpa('H', PDF_UNIT, 'LETTER', true, 'UTF-8', false);
            $pdf->titulo = $this->User->lsucursal;
			$pdf->subtitulo = $this->Application->Parameters["lproyect"];
            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('AletsNet');
            $pdf->SetTitle('Triton - Usuarios');
            $pdf->SetSubject('Lista de usuarios del sistema');
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