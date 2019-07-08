<?php
class listapdf extends TPage
{
    public function onLoad($param)
    {
		$v = $this->User->ServicioActivo(8);
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
            
            $where = " borrado = 0 AND tipo_cliente = 2";
			$ct_buscar = new TActiveRecordCriteria;
			$ct_buscar->Parameters[':buscar'] = $this->request['buscar'];
			$where .= " AND (nombre like :buscar OR rfc like :buscar OR telefono like :buscar)";
			$ct_buscar->Condition = $where;
			
			$ct_buscar->OrdersBy['rfc'] = 'decs';
			
			$tabla = LMsClientes::finder()->findAll($ct_buscar);
            $rows = count($tabla);
            $documento_html = "";
            
            $documento_html .= '<h2 align="center">Clientes</h2>';
            $documento_html .= '<h4 align="right"><b></b> '.$this->User->fechacompleta($this->User->fecha(date('Y-m-d H:i:s'))).'</h4>
                                <table width="100%" border="0" cellspacing="2" cellpadding="2" style="font-family:Verdana, Geneva, sans-serif;">
                                    <thead>
                                        <tr>
                                            <th style="width:12%; background-color: #d9d9d9; text-align: center;">#</th>
											<th style="width:20%; background-color: #d9d9d9; text-align: center;">RFC</th>
                                            <th style="width:32%; background-color: #d9d9d9; text-align: center;">Nombre</th>
                                            <th style="width:12%; background-color: #d9d9d9; text-align: center;">Teléfono</th>
                                            <th style="width:12%; background-color: #d9d9d9; text-align: center;">Saldo</th>
                                            <th style="width:12%; background-color: #d9d9d9; text-align: center;">Credito</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
            $nrows = 5;
            $nrow = 0;
            $i = 0;
            $color_row="#ffffff";
            foreach($tabla as $i => $row){
                //documentos
				$saldo = 0;
                $html_documentos = "";
                $nrow ++;
                $i++;
                $documento_html .= '        <tr>
                                                <td style="width:12%; background-color: '.$color_row.'; text-align: center; font-size: 60%;">
                                                    '.$i.'
                                                </td>
                                                <td style="width:20%; background-color: '.$color_row.'; font-size: 60%;">
                                                    '.$row->rfc.'
                                                </td>
                                                <td style="width:32%; background-color: '.$color_row.'; font-size: 75%;">
                                                    '.$row->nombre.'
                                                </td>
                                                <td style="width:12%; background-color: '.$color_row.'; font-size: 75%;">
                                                     '.$row->telefono.'
                                                </td>
                                                <td style="width:12%; background-color: '.$color_row.'; font-size: 75%;">
                                                    $ '.number_format($saldo,2).'
                                                </td>
                                                <td style="width:12%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
													$ '.number_format($row->credito,2).'
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
			$pdf->subtitulo = $this->Application->Parameters["LemaTienda"];
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