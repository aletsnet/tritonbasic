<?php
class ticket extends TPage
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
		<!-- Font Awesome -->
		<link rel="stylesheet" href="plugins/font-awesome-4.7.0/css/font-awesome.min.css">
		<!-- Ionicons -->
		<link rel="stylesheet" href="plugins/ionicons-2.0.1/css/ionicons.min.css">
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
        $row_venta = LMsVentas::finder()->find(" id_ventas = ?", array($id_ventas));
		if($row_venta instanceof LMsVentas){
			$neta = 0;
            $sucursal = $row_venta->ms_cortes->ct_sucursales;
			//Tipo de venta
			$catalogo7 = LBsCatalogosGenericos::finder()->find(" catalogo = ? AND valor = ?", array(7, $row_venta->tipo_venta));
			$tipo_venta = "".$catalogo7->opcion."";
			//Estatus de venta
			$estatus_venta = "";
			$catalogo3 = LBsCatalogosGenericos::finder()->find(" catalogo = ? AND valor = ?", array(3, $row_venta->estatus));
			$estatus_venta = "".$catalogo3->opcion."";
			//lista de productos del ticket
			$lista = "";
			
			//forma de pago
			$formapago = "";
			$catalogoFormaPago = LBsCatalogosGenericos::finder()->find(" catalogo = ? AND valor = ?", array(9, $row_venta->modo_pago));
			$formapago = "<i class=\"".$catalogoFormaPago->icon."\"></i> ".$catalogoFormaPago->opcion."";
			//$formapago = $row_venta->modo_pago;
			
            $rows_ventasdetalle = LCtVentasDetalle::finder()->findAll(" id_ventas = ? ",array($id_ventas));
            foreach($rows_ventasdetalle as $i => $row_ventasdetalle){
                $inventario = $row_ventasdetalle->ms_inventarios;
				$tneta      = $row_ventasdetalle->cantidad * $row_ventasdetalle->precio_publico;
                $total      = $row_ventasdetalle->cantidad * $row_ventasdetalle->precio_vendido;
				$neta = $neta + $tneta;
				$lpresio = $row_ventasdetalle->precio_vendido;
				if($row_ventasdetalle->precio_publico > $row_ventasdetalle->precio_vendido){
					$lpresio = $row_ventasdetalle->precio_publico;
				}
                $lista .= '
				<tr>
					<td style="text-align: center;" colspan="4">
						--------------------------------------------------
					</td>
				</tr>
                <tr>
                    <td class="lcampos">
                        '.$inventario->ms_productos->nombre.'
                    </td>
                    <td class="rcampos">
                        '.$row_ventasdetalle->cantidad.' '.$row_ventasdetalle->ms_inventarios->unidad.'
                    </td>
                    <td class="rcampos">
                        '."$ ".number_format($total,2).'
                    </td>
                </tr>';
            }
			//Descuentos
			$descuento = $neta - $row_venta->total;
			$html_descuento = "";
			/*if($descuento > 0 && $row_venta->total > 0	){
				$html_descuento ='
					<tr>
						<td class="rcampos" style="">
							SubTotal:
						</td>
						<td class="rcampos" style="width: 30%;">
							'."$ ".number_format(($neta),2).'
						</td>
					</tr>
					<tr>
						<td class="rcampos" style="">
							Descuento:
						</td>
						<td class="rcampos" style="width: 30%;">
							'."$ ".number_format(($descuento),2).'
						</td>
					</tr>
					<tr>
					<td style="text-align: center;" colspan="2">
						--------------------------------------------------
					</td>
				</tr>';
			}*/
			//Folio de credito utilizado
			$html_folio = "";
			$row_folios = LCtFoliosVentaCobrado::finder()->find(" id_ventas = ? AND borrado = 0", array($id_ventas));
			if($row_folios instanceof LCtFoliosVentaCobrado){
				$row_folio = $row_folios->ct_folios_creditos;
				$html_folio = '
			<tr>
                <td style="text-align: center;">
                    --------------------------------------------------
                </td>
            </tr>
            <tr>
                <td>
                    <table cellpadding="0" cellspacing="0" style="width: 100%;" border="0">
						<tr>
                            <td class="rcampos" style="">
                                Folio de credito:
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format($row_folio->monto,2).'
                            </td>
                        </tr>
					</table>
				</td>
			</tr>';
			}
			
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
                    Ticket de '.$tipo_venta.' '.(isset($this->request['status'])?($this->request['status']==2?'(reimpresión)':''):'').'
                </td>
            </tr>
            <tr>
                <td class="campo">
				'.$estatus_venta.'
                </td>
            </tr>
			<tr>
				<td class="fecha">
					fecha: '.$this->User->fecha($row_venta->fecha_termina).'
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
                                '.$row_venta->bs_usuarios->nombre.'
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="lcampo">
                    ['.$row_venta->id_clientes.']: '.$row_venta->ms_clientes->nombre.'
                </td>
            </tr>';
			if($row_venta->tipo_venta == 3){
				$html .= '<tr>
					<td class="lcampo">
						Teléfono: '.$row_venta->ms_clientes->telefono.'
					</td>
				</tr>
				<tr>
					<td class="lcampo">
						'.$row_venta->ms_clientes->direccion.' <br />
						
						'.$row_venta->ms_clientes->referencia.'
					</td>
				</tr>';
			}
            $html .= '<tr>
                <td style="text-align: center;">
                    --------------------------------------------------
                </td>
            </tr>
            <tr>
                <td>
                    <table cellpadding="0" cellspacing="0" style="width: 100%;" border="0">
                        <tr>
                            <td class="campo" style="">
                                Articulo
                            </td>
                            <td class="campo" style="width: 10%;">
                                Cant.
                            </td>
                            <td class="campo" style="width: 23%;">
                                SubT.
                            </td>
                        </tr>
                        '.$lista.'
                    </table>
                </td>
            </tr>
			'.$html_folio.'
            <tr>
                <td style="text-align: center;">
                    --------------------------------------------------
                </td>
            </tr>
            <tr>
                <td>
                    <table cellpadding="0" cellspacing="0" style="width: 100%;" border="0">
						'.$html_descuento.'
                        <tr>
                            <td class="rcampos" style="">
                                Total:
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format($row_venta->total,2).'
                            </td>
                        </tr>
						<tr>
                            <td class="rcampos" style="">
                                '.$formapago.':
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format($row_venta->efectivo,2).'
                            </td>
                        </tr>
						<tr>
                            <td class="rcampos" style="">
                                Cambio:
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format(($row_venta->efectivo - $row_venta->total),2).'
								
                            </td>
                        </tr>
                    </table>
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
					<a id="link" herf="https://addons.mozilla.org/es/firefox/addon/js-print-setup/"> </a>
				</td>
			</tr>
        </table>';
		}
        $html .= "
        <script>
		//window.print();
		//window.close();
		
        </script>
    </body>
    </html>";
        echo $html;
        exit(0);
    }
}
