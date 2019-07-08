<?php
class historialpdf extends TPage
{
    public function onLoad($param)
    {
		$v = $this->User->ServicioActivo("5");
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
            $fecha_inicio = strtotime($this->request['fechainicio']);
            $fecha_final  = strtotime($this->request['fechafinal']);
            $Parametros = array("fechainicio" => date('Y-m-d',$fecha_inicio) . " 00:00:00",
                                "fechafinal"  => date('Y-m-d',$fecha_final)  . " 23:59:59",
                                "tbuscar"     => "%".$this->request['tbuscar']."%",
                                "idsucursales"=> $idsucursales);
            $tabla = $this->Application->Modules['query']->Client->queryForList("vwVentas_exportar",$Parametros);
            $rows = count($tabla);
            $documento_html = "";
            
            $documento_html .= '<h2 align="center">Ventas realizadas</h2>';
            $documento_html .= '<h4 align="right"><b></b> '.$this->User->fechacompleta($this->User->fecha(date('Y-m-d H:i:s'))).'</h4>
                                <table width="100%" border="0" cellspacing="2" cellpadding="2" style="font-family:Verdana, Geneva, sans-serif;">
                                    <thead>
                                        <tr>
                                            <th style="width:10%; background-color: #d9d9d9; text-align: center; font-size: 80%;">Folio</th>
											<th style="width:10%; background-color: #d9d9d9; text-align: center; font-size: 80%;">Corte</th>
                                            <th style="width:10%; background-color: #d9d9d9; text-align: center; font-size: 80%;">Atendio</th>
                                            <th style="width:25%; background-color: #d9d9d9; text-align: center; font-size: 80%;">Cliente</th>
											<th style="width:15%; background-color: #d9d9d9; text-align: center; font-size: 80%;">Fecha</th>
                                            <th style="width:10%; background-color: #d9d9d9; text-align: center; font-size: 80%;">Descuento</th>
                                            <th style="width:10%; background-color: #d9d9d9; text-align: center; font-size: 80%;">Total</th>
                                            <th style="width:10%; background-color: #d9d9d9; text-align: center; font-size: 80%;">Estatus</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
            $nrows = 5;
            $nrow = 0;
            $i = 0;
            $color_row="#ffffff";
            foreach($tabla as $i => $row){
                $time = strtotime($row->fecha_termina);
                $catalogo3 = LBsCatalogosGenericos::finder()->find(" catalogo = ? AND valor = ?", array(3, $row->estatus));
                //documentos
                $html_documentos = "";
                $nrow ++;
                $i++;
                $documento_html .= '        <tr>
                                                <td style="width:10%; background-color: '.$color_row.'; text-align: center; font-size: 80%;">
                                                    '.$row->id_ventas.'
                                                </td>
                                                <td style="width:10%; background-color: '.$color_row.'; font-size: 80%;">
                                                    '.$row->id_cortes.'
                                                </td>
                                                <td style="width:10%; background-color: '.$color_row.'; font-size: 75%;">
                                                    '.$row->bs_usuarios->user.'
                                                </td>
                                                <td style="width:25%; background-color: '.$color_row.'; font-size: 75%;">
                                                     '.$row->ms_clientes->nombre.'
                                                </td>
                                                <td style="width:15%; background-color: '.$color_row.'; font-size: 75%;">
                                                     '.date('d/m/y h:i a',$time).'
                                                </td>
                                                <td style="width:10%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
													 '.round((($row->descuento > 0 ? $row->descuento : 0 ) * 100)).' %
                                                </td>
                                                <td style="width:10%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
													$ '.number_format($row->total, 2).'
                                                </td>
                                                <td style="width:10%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
													'.$catalogo3->opcion.' <img src="'.$catalogo3->picture.'" width="8px" />
                                                </td>
                                            </tr>';
                $color_row = ($color_row=="#ffffff" ? "#f2f2f2" : "#ffffff");
                
            }
            
            if($rows == 0){
                $documento_html .= '		<tr>
                                                <td colspan="8">
                                                    No hay elementos que mostrar
                                                </td>
                                            </tr>';
            }
            $documento_html .= '	</tbody>';
            
            $documento_html .= '	<tfoot>
                                        <tr>
                                            <td colspan="8" align="right">
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
			$pdf->subtitulo = $this->Application->Parameters["LemaTienda"];
            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('AletsNet');
            $pdf->SetTitle('Triton - Ventas');
            $pdf->SetSubject('Lista de Ventas');
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