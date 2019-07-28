<?php
class corte extends TPage
{
	public function onLoad($param)
    {
        $v = $this->User->ServicioActivo("6");
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
        
		$dbnet = $this->Application->Modules['query']->Client;
		
        $id_cortes = $this->request['ticket'];
        $tabla = $dbnet->queryForList("vwListaCorteInventario", $id_cortes);
        $documento_html = "";
        $documento_html .= '<h4 align="center">Corte de inventario </h4>';
        $documento_html .= '<h4 align="right" style="font-size: 60%; ">'.$this->User->fechacompleta($this->User->fecha(date('Y-m-d H:i:s'))).'</h4>
                            <table width="100%" border="0" cellspacing="1" cellpadding="1" style="font-family:Verdana, Geneva, sans-serif;">
                                <thead>
                                    <tr>
                                        <th style="font-size: 60%; width:8%; background-color: #d9d9d9; text-align: center;">#</th>
                                        <th style="font-size: 60%; width:10%; background-color: #d9d9d9; text-align: center;">Codigo</th>
                                        <th style="font-size: 60%; width:25%; background-color: #d9d9d9; text-align: center;">Producto / Servicio</th>
                                        <th style="font-size: 60%; width:10%; background-color: #d9d9d9; text-align: center;">Departamento</th>
                                        <th style="font-size: 60%; width:8%; background-color: #d9d9d9; text-align: center;">P. Publico</th>
                                        <th style="font-size: 60%; width:8%; background-color: #d9d9d9; text-align: center;">Inicio</th>
                                        <th style="font-size: 60%; width:8%; background-color: #d9d9d9; text-align: center;">Abasto</th>
                                        <th style="font-size: 60%; width:8%; background-color: #d9d9d9; text-align: center;">Mermas</th>
                                        <th style="font-size: 60%; width:8%; background-color: #d9d9d9; text-align: center;">Ventas</th>
                                        <th style="font-size: 60%; width:8%; background-color: #d9d9d9; text-align: center;">Termina</th>
                                        <th style="font-size: 60%; width:6%; background-color: #d9d9d9; text-align: center;"> · </th>
                                    </tr>
                                </thead>
                                <tbody>';
        //$rows = count($tabla);
        $nrow = 0;
        $i = 0;
        $color_row="#ffffff";
        foreach($tabla as $i => $row){
                //documentos
                $html_documentos = "";
                $nrow ++;
                $i++;
                $documento_html .= '        <tr>
                                                <td style="width:8%; background-color: '.$color_row.'; text-align: center; font-size: 60%;">
                                                    '.$i.'
                                                </td>
                                                <td style="width:10%; background-color: '.$color_row.'; font-size: 55%;">
                                                    '.$row->ms_productos->codigo.'
                                                </td>
                                                <td style="width:25%; background-color: '.$color_row.'; font-size: 55%;">
                                                     '.$row->ms_productos->nombre.'
                                                </td>
                                                <td style="width:10%; background-color: '.$color_row.'; font-size: 55%;">
                                                     '.$row->ms_productos->ct_departamentos->nombre.'
                                                </td>
                                                <td style="width:8%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
													$ '.number_format($row->ms_inventarios->preciopublico, 2).'
                                                </td>
                                                <td style="width:8%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
													 '.$row->inicia.' '.$row->ms_inventarios->unidad.'
                                                </td>
                                                <td style="width:8%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
													 '.$row->abasto.' '.$row->ms_inventarios->unidad.'
                                                </td>
                                                <td style="width:8%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
													 '.$row->merma.' '.$row->ms_inventarios->unidad.'
                                                </td>
                                                <td style="width:8%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
													 '.$row->venta.' '.$row->ms_inventarios->unidad.'
                                                </td>
                                                <td style="width:8%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
													 '.$row->termina.' '.$row->ms_inventarios->unidad.'
                                                </td>
                                                <td style="width:6%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
													
                                                </td>
                                            </tr>';
                $color_row = ($color_row=="#ffffff" ? "#f2f2f2" : "#ffffff");
                
            }
            
            if($nrow == 0){
                $documento_html .= '		<tr>
                                                <td colspan="9">
                                                    No hay elementos que mostrar
                                                </td>
                                            </tr>';
            }
            $documento_html .= '	</tbody>';
            
            $documento_html .= '	<tfoot>
                                        <tr>
                                            <td colspan="9" align="right" style="font-size: 60%; ">
                                                <b>Registros: '.$nrow.'</b>
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
        $pdf->SetTitle('Triton - Corte de inventario');
        $pdf->SetSubject('Lista de inventario - corte');
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
        $pdf->Output('ListaInventario'.$fecha.'.pdf', 'I');
        
        Prado::log("[".$this->User->idusuarios.
                       "][IP:".
                       $_SERVER['REMOTE_ADDR'].
                       "][Mostrar Documento PDF]",
                       TLogger::NOTICE,
                       $this->Page->PagePath);
            
        
        exit(0);
    }
}