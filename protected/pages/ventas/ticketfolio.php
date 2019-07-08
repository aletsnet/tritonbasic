<?php
class ticketfolio extends TPage
{
	public function onLoad($param)
    {
		$v = $this->User->ServicioActivo("4");
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
        
        $id_ventas = $this->request['ticket'];
        $html = '<!DOCTYPE html>
<html>
    <head>
        <title>Ticket</title>
         <meta charset="UTF-8"> 
        <style>
        table{
            width: 250px;
        }
        .fecha{
            font: italic bold 10px Georgia, serif;
        }
        .titulo{
            font: normal bold 16px Console;
            text-align: center;
        }
        .lcampo{
            font: normal bold 11px Console;
            text-align: left;
        }
        .rcampo{
            font: normal bold 11px Console;
            text-align: right;
        }
        .lcampos{
            font: normal 11px Console;
            text-align: left;
        }
        .rcampos{
            font: normal 11px Console;
            text-align: right;
        }
        .campo{
            font: normal bold 11px Console;
            text-align: center;
            text-transform: uppercase;
        }
        </style>
    </head>
    <body onload="">';
        $row_folio = LCtFoliosCreditos::finder()->find(" id_ventas = ?", array($id_ventas));
		if($row_folio instanceof LCtFoliosCreditos){
			$row_venta = $row_folio->ms_ventas;
			$sucursal  = $row_venta->ct_sucursales;
			$folio_estatus = '';
			$catalogo5 = LBsCatalogosGenericos::finder()->find(" catalogo = ? AND valor = ?", array(5, $row_folio->estatus));
			if($catalogo5 instanceof LBsCatalogosGenericos){
				$folio_estatus = '<label class="'.$catalogo5->cssclass.'"><i class="'.$catalogo5->icon.'"></i> '. $catalogo5->opcion.'</label>';
			}else{
				$folio_estatus = '';
			}
			$caduca = $this->User->tiempotrascurrio(date('Y-m-d'),$row_folio->fecha_caducidad);
            $html .= '
        <table cellpadding="0" border="0" style="width: 300px;">
            <tr>
                <td class="campo">
                     <img src="'.$sucursal->ticket_logo.'" alt="Logo" height="128" /> 
                </td>
            </tr>
            <tr>
                <td class="campo">
                    '.$sucursal->ticket_head.'
                </td>
            </tr>
            <tr>
                <td class="campo">
                    '.$sucursal->sucursal.'&nbsp;
                </td>
            </tr>
            <tr>
                <td class="campo">
                    Folio de Credito '.(isset($this->request['status'])?($this->request['status']==2?'(reimpresión)':''):'').'
                </td>
            </tr>
            <tr>
                <td>
                    <table style="width: 100%;">
                        <tr>
                            <td class="lcampo">
                                Folio: '.$row_venta->id_ventas.'
                            </td>
                            <td class="rcampo">
                                Autorizo: '.$row_folio->bs_usuarios->nombre.'
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="lcampo">
                    ['.$row_venta->id_clientes.']: '.$row_venta->ms_clientes->nombre.'
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">
                    --------------------------------------------------
                </td>
            </tr>
            <tr>
                <td>
					<table style="width: 100%;">
                        <tr>
                            <td class="lcampo">
                                Folio:
                            </td>
                            <td >
                                '.$row_venta->id_ventas.'
                            </td>
                        </tr>
                        <tr>
                            <td class="lcampo">
                                Estatus:
                            </td>
                            <td >
                                '.$folio_estatus.'
                            </td>
                        </tr>
						<tr>
                            <td class="lcampo">
                                Credito:
                            </td>
							<td>
								'."$ ".number_format($row_folio->monto,2).'
							</td>
						</tr>
						<tr>
                            <td class="lcampo">
                                Caduca:
                            </td>
							<td>
								'.$this->User->fecha($row_folio->fecha_caducidad).' - '.$caduca.'
							</td>
						</tr>
                    </table>
                    
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">
                    --------------------------------------------------
                </td>
            </tr>
            <tr>
                <td class="campo">
					&nbsp; <br />
                    '.$sucursal->ticket_fool.'&nbsp; <br />
					&nbsp; <br />
					&nbsp; <br />
					&nbsp; <br />
                </td>
			</tr>
			<tr>
				<td style="font: normal bold 5px Console; text-align: center;">
					<label >triton.aletsnet.com</label>
				</td>
			</tr>
        </table>';
		}
        $html .= "
        <script>
            // set portrait orientation
            //jsPrintSetup.setOption('orientation', jsPrintSetup.kPortraitOrientation);
            // set top margins in millimeters
            jsPrintSetup.setOption('marginTop', -1);
            jsPrintSetup.setOption('marginBottom', 0);
            jsPrintSetup.setOption('marginLeft', 0);
            jsPrintSetup.setOption('marginRight', 0);
            // set page header
            jsPrintSetup.setOption('headerStrLeft', '');
            jsPrintSetup.setOption('headerStrCenter', '');
            jsPrintSetup.setOption('headerStrRight', '');
            // set empty page footerprint
            jsPrintSetup.setOption('footerStrLeft', '');
            jsPrintSetup.setOption('footerStrCenter', '');
            jsPrintSetup.setOption('footerStrRight', '');
            // clears user preferences always silent print value
            // to enable using 'printSilent' option
            jsPrintSetup.clearSilentPrint();
            // Suppress print dialog (for this context only)
            jsPrintSetup.setOption('printSilent', 1);
            // Do Print 
            // When print is submitted it is executed asynchronous and
            // script flow continues after print independently of completetion of print process! 
            jsPrintSetup.print();
            // next commands
			window.close();
         </script>
    </body>
    </html>";
        echo $html;
        exit(0);
    }
}