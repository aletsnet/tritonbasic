<?php
class productospdf extends TPage
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
            $Tipo = LBsCatalogosGenericos::finder()->findAll(" catalogo = 18 AND activo = 1");
            $lTipos = [];
            foreach($Tipo as $itipo => $ltipo){
               $lTipos[$ltipo->valor] = $ltipo;
            }
            
            
            $tipo_producto = $this->request['tipo'];
            
            $Parametros = array("nombre" => "%".$this->request['nombre']."%",
                                "idbodegas"  => $this->request['idbodegas'],
                                "tipo"  => $tipo_producto,
                                "idsucursales"  => $idsucursales);
            $tabla = $this->Application->Modules['query']->Client->queryForList("vwInventarios_exportar",$Parametros);
            $rows = count($tabla);
            $documento_html = "";
            
            $documento_html .= '<h2 align="center">Inventario</h2>';
            $documento_html .= '<h4 align="right" style="font-size: 60%; ">'.$this->User->fechacompleta($this->User->fecha(date('Y-m-d H:i:s'))).'</h4>
                                <table width="100%" border="0" cellspacing="2" cellpadding="2" style="font-family:Verdana, Geneva, sans-serif;">
                                    <thead>
                                        <tr>
                                            <th style="font-size: 60%; width:8%; background-color: #d9d9d9; text-align: center;">#</th>
											<th style="font-size: 60%; width:12%; background-color: #d9d9d9; text-align: center;">Codigo</th>
                                            <th style="font-size: 60%; width:10%; background-color: #d9d9d9; text-align: center;">Tipo</th>
                                            <th style="font-size: 60%; width:25%; background-color: #d9d9d9; text-align: center;">Producto / Servicio</th>
											<th style="font-size: 60%; width:20%; background-color: #d9d9d9; text-align: center;">Apartado / Departamento</th>
                                            <th style="font-size: 60%; width:10%; background-color: #d9d9d9; text-align: center;">P. Adquirido</th>
                                            <th style="font-size: 60%; width:10%; background-color: #d9d9d9; text-align: center;">P. Publico</th>
                                            <th style="font-size: 60%; width:10%; background-color: #d9d9d9; text-align: center;">Existencia</th>
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
                                                <td style="width:8%; background-color: '.$color_row.'; text-align: center; font-size: 60%;">
                                                    '.$i.'
                                                </td>
                                                <td style="width:12%; background-color: '.$color_row.'; font-size: 60%;">
                                                    '.$row->ms_productos->codigo.'
                                                </td>
                                                <td style="width:10%; background-color: '.$color_row.'; font-size: 60%;">
                                                    '.$lTipos[$row->ms_productos->tipo].'
                                                </td>
                                                <td style="width:25%; background-color: '.$color_row.'; font-size: 60%;">
                                                     '.$row->ms_productos->nombre.'
                                                </td>
                                                <td style="width:20%; background-color: '.$color_row.'; font-size: 60%;">
                                                     '.$row->ms_productos->ct_departamentos->nombre.'
                                                </td>
                                                <td style="width:10%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
													$ '.number_format($row->precioadquisicion, 2).'
                                                </td>
                                                <td style="width:10%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
													$ '.number_format($row->preciopublico, 2).'
                                                </td>
                                                <td style="width:10%; background-color: '.$color_row.'; text-align: right; font-size: 65%;">
													 '.$row->stock.' '.$row->unidad.'
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