<?php
class listapdf extends TPage
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
			
			$where = " ";
			$ct_buscar = new TActiveRecordCriteria;
			$ct_buscar->Parameters[':id_sucursales'] = $idsucursales;
			$where .= " id_sucursal = :id_sucursales ";
			$ct_buscar->Parameters[':fecha_inicio'] = date('Y-m-d',$fecha_inicio) . " 00:00:00";
			$ct_buscar->Parameters[':fecha_final']  = date('Y-m-d',$fecha_final)  . " 23:59:59";
			$where .= " AND (fecha_inicio BETWEEN :fecha_inicio AND :fecha_final ";
			$where .= " OR   fecha_final  BETWEEN :fecha_inicio AND :fecha_final )";
			$ct_buscar->Condition = $where;
			$ct_buscar->OrdersBy['id_cortes'] = 'desc';
            $tabla = LMsCortes::finder()->findAll($ct_buscar);
            $rows = count($tabla);
            $documento_html = "";
            
            $documento_html .= '<h2 align="center">Cortes</h2>';
            $documento_html .= '<h4 align="right" style="font-size: 60%; ">'.$this->User->fechacompleta($this->User->fecha(date('Y-m-d H:i:s'))).'</h4>
                                <table width="100%" border="0" cellspacing="0" cellpadding="2" style="font-family:Verdana, Geneva, sans-serif;">
                                    <thead>
                                        <tr>
                                            <th style="width:07%; background-color: #d9d9d9; text-align: center; font-size: 70%;">Corte</th>
											<th style="width:13%; background-color: #d9d9d9; text-align: center; font-size: 70%;">Usuario</th>
                                            <th style="width:15%; background-color: #d9d9d9; text-align: center; font-size: 70%;">Fecha</th>
                                            <th style="width:10%; background-color: #d9d9d9; text-align: center; font-size: 70%;">Duración</th>
											<th style="width:10%; background-color: #d9d9d9; text-align: center; font-size: 70%;">Gasto realizados</th>
                                            <th style="width:10%; background-color: #d9d9d9; text-align: center; font-size: 70%;">Total</th>
                                            <th style="width:10%; background-color: #d9d9d9; text-align: center; font-size: 70%;">Creditos realizados</th>
											<th style="width:10%; background-color: #d9d9d9; text-align: center; font-size: 70%;">Inicio de Caja</th>
											<th style="width:10%; background-color: #d9d9d9; text-align: center; font-size: 70%;">Retiro a depositar</th>
                                            <th style="width:10%; background-color: #d9d9d9; text-align: center; font-size: 70%;">Estatus</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
            $nrows = 5;
            $nrow = 0;
            $i = 0;
            $color_row="#ffffff";
			$catalogo6 = LBsCatalogosGenericos::finder()->findAll(" catalogo = ? ", 6);
			$estatus = array();
			foreach($catalogo6 as $ii => $jj){ $estatus[$jj->valor] = $jj; }
			
            foreach($tabla as $i => $row){
                $fecha = strtotime($row->fecha_inicio);
                //documentos
                $html_documentos = "";
                $nrow ++;
                $i++;
                $documento_html .= '        <tr>
                                                <td style="width:7%; background-color: '.$color_row.'; text-align: center; font-size: 80%;">
                                                    '.$row->id_cortes.'
                                                </td>
                                                <td style="width:13%; background-color: '.$color_row.'; font-size: 60%;">
                                                    '.$row->bs_usuarios->nombre.'
                                                </td>
                                                <td style="width:15%; background-color: '.$color_row.'; font-size: 55%;">
                                                    '.date('d/m/Y h:i a',$fecha).'
                                                </td>
                                                <td style="width:10%; background-color: '.$color_row.'; font-size: 75%;">
                                                     '.$this->User->tiempotrascurrio($row->fecha_inicio,($row->fecha_final != ""? $row->fecha_final : "now")).'
                                                </td>
                                                <td style="width:10%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
                                                    $ '.number_format($row->gastos_realizados, 2).'
                                                </td>
                                                <td style="width:10%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
													$ '.number_format($row->total, 2).'
                                                </td>
                                                <td style="width:10%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
													$ '.number_format($row->creditos, 2).'
                                                </td>
                                                <td style="width:10%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
													$ '.number_format($row->inicio_caja, 2).'
                                                </td>
                                                <td style="width:10%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
													$ '.number_format($row->retiro_deposito, 2).'
                                                </td>
                                                <td style="width:10%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
													'.$estatus[$row->estatus].' 
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