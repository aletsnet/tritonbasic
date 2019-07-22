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
        
        $id_cortes = $this->request['ticket'];
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
		.ccampos{
            font: normal 11px Console;
            text-align: center;
        }
        .campo{
            font: normal bold 11px Console;
            text-align: center;
            text-transform: uppercase;
        }
        </style>
    </head>
    <body onload="">';
        $row_corte = LMsCortes::finder()->find(" id_cortes = ?", array($id_cortes));
		if($row_corte instanceof LMsCortes){
			$neta = 0;
            $sucursal = $row_corte->ct_sucursales;
			$lista = "";
			$estatus_corte = "";
			$catalogo6 = LBsCatalogosGenericos::finder()->find(" catalogo = ? AND valor = ?", array(6, $row_corte->estatus));
			if($catalogo6 instanceof LBsCatalogosGenericos){
				$estatus_corte = '<img src="'.$catalogo6->picture.'" alt="Logo" height="12" />  '. $catalogo6->opcion.'';
			}
			
            //lista de retiros
			$html_retiros = "";
			$rows_retiros = LCtCorteRetiros::finder()->findAll(" id_cortes = ? AND borrado = 0 AND tipo = 1", array($id_cortes));
            $retiros = 0;
			$crow_retiros = count($rows_retiros);
			if($crow_retiros > 0){
				$html_retiros .= '<table cellpadding="0" border="0" style="width: 100%;">
					<tr>
						<th class="campo" style="width: 10%;">
							#
						</th>
						<th class="campo" style="width: 65%;">
							Descripción
						</th>
						<th class="campo" style="width: 25%;">
							Monto
						</th>
					</tr>';
				$c = 0;
				foreach($rows_retiros as $i => $v){
					$c++;
					$html_retiros .= '<tr>
						<td class="ccampos" style="width: 10%;">
							'.$c.'
						</td>
						<td class="lcampos" style="width: 65%;">
							'.$v->descripcion.'
						</td>
						<td class="rcampos" style="width: 25%;">
							'."$ ".number_format($v->monto,2).'
						</td>
					</tr>';
				}
				$html_retiros .= '</table>';
			}
			
			//lista de retiros
			$html_adicion = "";
			$rows_adicion = LCtCorteRetiros::finder()->findAll(" id_cortes = ? AND borrado = 0 AND tipo = 2", array($id_cortes));
            $retiros = 0;
			$crows_adicion = count($rows_adicion);
			if($crows_adicion > 0){
				$html_adicion .= '<table cellpadding="0" border="0" style="width: 100%;">
					<tr>
						<th class="campo" style="width: 10%;">
							#
						</th>
						<th class="campo" style="width: 65%;">
							Descripción
						</th>
						<th class="campo" style="width: 25%;">
							Monto
						</th>
					</tr>';
				$c = 0;
				foreach($rows_adicion as $i => $v){
					$c++;
					$html_adicion .= '<tr>
						<td class="ccampos" style="width: 10%;">
							'.$c.'
						</td>
						<td class="lcampos" style="width: 65%;">
							'.$v->descripcion.'
						</td>
						<td class="rcampos" style="width: 25%;">
							'."$ ".number_format($v->monto,2).'
						</td>
					</tr>';
				}
				$html_adicion .= '</table>';
			}
			
			//devoluciones
			$html_devoluciones = "";
			$rows_devoluciones = LCtCorteMovimientos::finder()->findAll(" id_cortes = ? AND borrado = 0", array($id_cortes));
            $retiros = 0;
			$crow_devoluciones = count($rows_devoluciones);
			if($crow_devoluciones > 0){
				$html_devoluciones .= '<table cellpadding="0" border="0" style="width: 100%;">
					<tr>
						<th class="campo" style="width: 10%;">
							#
						</th>
						<th class="campo" style="width: 60%;">
							Producto
						</th>
						<th class="campo" style="width: 15%;">
							C.
						</th>
						<th class="campo" style="width: 15%;">
							Monto
						</th>
					</tr>';
				$c = 0;
				foreach($rows_devoluciones as $ii => $vv){
					$c++;
					$html_devoluciones .= '<tr>
						<td class="ccampos" style="width: 10%;">
							'.$c.'
						</td>
						<td class="lcampos" style="width: 60%;">
							'.$vv->ct_ventas_detalle->ms_inventarios->ms_productos->nombre.'
						</td>
						<td class="lcampos" style="width: 15%;">
							'.$vv->ct_ventas_detalle->cantidad.'
						</td>
						<td class="rcampos" style="width: 15%;">
							'."$ ".number_format($vv->ct_ventas_detalle->precio_vendido,2).'
						</td>
					</tr>';
				}
				$html_devoluciones .= '</table>';
			}
			
			//Folios de creditos
			$html_folios = "";
			$rows_folios = LCtFoliosCreditos::finder()->findAll(" id_cortes = ? AND borrado = 0", array($id_cortes));
			$crow_folios = count($rows_folios);
			if($crow_folios > 0){
				$html_folios .= '<table cellpadding="0" border="0" style="width: 100%;">
					<tr>
						<th class="campo" style="width: 15%;">
							Folio
						</th>
						<th class="campo" style="width: 35%;">
							Monto
						</th>
						<th class="campo" style="width: 20%;">
							Caduca
						</th>
						<th class="campo" style="width: 30%;">
							*
						</th>
					</tr>';
				//$c = 0;
				foreach($rows_folios as $ii => $vv){
					$caduca = $this->User->tiempotrascurrio(date('Y-m-d'),$vv->fecha_caducidad);
					$estatus = "";
					$catalogo5 = LBsCatalogosGenericos::finder()->find(" catalogo = ? AND valor = ?", array(5, $vv->estatus));
					if($catalogo5 instanceof LBsCatalogosGenericos){
						$estatus = '<img src="'.$catalogo5->picture.'" alt="Logo" height="12" />  '. $catalogo5->opcion.'';
					}
					$html_folios .= '<tr>
						<td class="ccampos" style="width: 15%;">
							'.$vv->id_folios_creditos.'
						</td>
						<td class="rcampos" style="width: 35%;">
							'."$ ".number_format($vv->monto,2).'
						</td>
						<td class="rcampos" style="width: 20%;">
							'.$caduca.'
						</td>
						<td class="rcampos" style="width: 30%;">
							'.$estatus.'
						</td>
					</tr>';
				}
				$html_folios .= '</table>';
			}
			
			//Ventas canceladas
			$html_ventas = "";
			$rows_ventas = LMsVentas::finder()->findAll(" estatus = 4 and id_cortes = ? ", array($id_cortes));
			$vrows = count($rows_ventas);
			if($vrows > 0){
				$html_ventas .= '<table cellpadding="0" border="0" style="width: 100%;">
					<tr>
						<th class="campo" style="width: 10%;">
							Folio
						</th>
						<th class="campo" style="">
							Cliente
						</th>
						<th class="campo" style="width: 20%;">
							Monto
						</th>
					</tr>';
				//$c = 0;
				foreach($rows_ventas as $j => $r){
					$html_ventas .= '<tr>
						<td class="ccampos" style="width: 15%;">
							'.$r->id_ventas.'
						</td>
						<td class="ccampos" style="">
							'.$r->ms_clientes->nombre.'
						</td>
						<td class="rcampos" style="width: 20%;">
							'."$ ".number_format($r->total,2).'
						</td>
					</tr>';
				}
				$html_ventas .= '</table>';
			}
			//Productos Vendidos
			$html_productos = "";
			$Parametros = array("id_corte" => $id_cortes);
			$rows_listaproductos = $this->Application->Modules['query']->Client->queryForList("vwProductosVendidos",$Parametros);
			$lrows = count($rows_listaproductos);
			if($lrows > 0){
				$html_productos .= '<table cellpadding="0" border="0" style="width: 100%;">
					<tr>
						<th class="campo" style="width: 10%;">
							#
						</th>
						<th class="campo" style="">
							Producto
						</th>
						<th class="campo" style="width: 20%;">
							*
						</th>
						<th class="campo" style="width: 20%;">
							$
						</th>
					</tr>';
				$c = 0;
				foreach($rows_listaproductos as $ip => $vp){
					$c++;
					$html_productos .= '<tr>
						<td class="ccampos" style="width: 15%;">
							'.$c.'
						</td>
						<td class="lcampos" style="">
							'.$vp['nombre'].'
						</td>
						<td class="ccampos" style="">
							'.$vp['cantidad'].'
						</td>
						<td class="rcampos" style="width: 25%;">
							'."$ ".number_format($vp['monto'],2).'
						</td>
					</tr>';
				}
				$html_productos .= '</table>';
			}
			
			$html_departamentos = "";
			$Parametros = ["idcorte" => $id_cortes];
			$listDesglose = explode(",",$this->request['departamentos']);
			$desglose = [];
			foreach($listDesglose as $d => $v){ $desglose[$v] = $v; }
			
			$rows_departamentos = $this->Application->Modules['query']->Client->queryForList("vwDepartamentosVendidos",$Parametros);
			$lrows = count($rows_departamentos);
			if($lrows > 0){
				$html_departamentos .= '<table cellpadding="0" border="0" style="width: 100%;">
					<tr>
						<th class="campo" style="width: 10%;">
							#
						</th>
						<th class="campo" style="">
							Dep.
						</th>
						<th class="campo" style="width: 20%;">
							U.
						</th>
						<th class="campo" style="width: 20%;">
							$
						</th>
					</tr>';
				$c = 0;
				//$deparamento = (object) [];
				foreach($rows_departamentos as $id => $vd){
					$deparamento = (object) $vd;
					$html_departamentos .= '<tr>
						<td class="ccampos" style="width: 15%;">
							'.$deparamento->folio.'
						</td>
						<td class="lcampos" style="">
							'.$deparamento->nombre.'
						</td>
						<td class="ccampos" style="">
							'.$deparamento->unidades.'
						</td>
						<td class="rcampos" style="width: 25%;">
							'."$ ".number_format($deparamento->monto,2).'
						</td>
					</tr>';
					if(key_exists($deparamento->folio,$desglose)){
						$rows_desglose = $this->Application->Modules['query']->Client->queryForList("vwDepartamentosDesglose",["idcorte" => $id_cortes, "folio" => $deparamento->folio]);
						foreach($rows_desglose as $idesglose => $element){
							$element = (object) $element;
							$html_departamentos .= '<tr>
								<td class="ccampos" style="width: 15%;">
									
								</td>
								<td class="lcampos" style="">
									'.$element->folio.'-'.$element->nombre.'
								</td>
								<td class="ccampos" style="">
									'.$element->unidades.'
								</td>
								<td class="rcampos" style="width: 25%;">
									'."$ ".number_format($element->monto,2).'
								</td>
							</tr>';
						}
					}
				}
				$html_departamentos .= '</table>';
			}
			
			
            $html .= '
        <table cellpadding="0" border="0" style="width: 250px;">
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
                    Corte de hoy
                </td>
            </tr>
			<tr>
				<td class="fecha">
					fecha: '.$this->User->fecha($row_corte->fecha_inicio).'
				</td>
            </tr>
            <tr>
                <td>
                    <table style="width: 100%;">
                        <tr>
                            <td class="lcampo">
                                Corte: '.$row_corte->id_cortes.'
                            </td>
                            <td class="rcampo">
                                '.$row_corte->bs_usuarios->nombre.'
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
			<tr>
                <td>
                    <table cellpadding="0" cellspacing="0" style="width: 100%;" border="0">
						<tr>
                            <td class="rcampos" style="">
                                Inicio de caja:
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format($row_corte->inicio_caja,2).'
                            </td>
                        </tr>
						<tr>
                            <td class="rcampos" style="">
                                Gasto realizados:
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format($row_corte->gastos_realizados,2).'
                            </td>
                        </tr>
						<tr>
                            <td class="rcampos" style="">
                                Entradas adicionales:
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format($row_corte->entradas_adicionales,2).'
                            </td>
                        </tr>
						<tr>
                            <td class="rcampos" style="">
                                Ventas realizadas:
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format($row_corte->ventas_realizadas,2).'
                            </td>
                        </tr>
						<tr>
                            <td class="rcampos" style="">
                                Creditos realizados:
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format($row_corte->creditos,2).'
                            </td>
                        </tr>
						<tr>
                            <td class="rcampos" style="">
                                Abonos a creditos:
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format($row_corte->aportado_creditos,2).'
                            </td>
                        </tr>
						<tr>
							<td colspan="2" style="text-align: center;">
								--------------------------------------------------
							</td>
						</tr>
						<tr>
                            <td class="rcampos" style="">
                               Efectivo:
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format($row_corte->efectivo,2).'
                            </td>
                        </tr>
						<tr>
                            <td class="rcampos" style="">
                               Tarjeta:
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format($row_corte->tarjeta,2).'
                            </td>
                        </tr>
						<tr>
                            <td class="rcampos" style="">
                               Cheque:
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format($row_corte->cheque,2).'
                            </td>
                        </tr>
						<tr>
                            <td class="rcampos" style="">
                               Trasferencia:
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format($row_corte->trasferencia,2).'
                            </td>
                        </tr>
						<tr>
                            <td class="rcampos" style="">
                               Otro:
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format($row_corte->otro,2).'
                            </td>
                        </tr>
						<tr>
							<td colspan="2" style="text-align: center;">
								--------------------------------------------------
							</td>
						</tr>
						<tr>
                            <td class="rcampos" style="">
                               Total:
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format($row_corte->total,2).'
                            </td>
                        </tr>
						<tr>
                            <td class="rcampos" style="">
                               En caja:
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format($row_corte->encaja,2).'
                            </td>
                        </tr>
						<tr>
                            <td class="rcampos" style="">
                               Retiro a depositar:
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format($row_corte->retiro_deposito,2).'
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="ccampos">
                    Observaciones: <br />
					'.$row_corte->observaciones.'
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">
                    --------------------------------------------------
                </td>
            </tr>
            <tr>
                <td style="text-align: center;" class="campo">
                    Gastos
                </td>
            </tr>
            <tr>
                <td>
                    '.$html_retiros.'
                </td>
            </tr>
			<tr>
                <td style="text-align: center;">
                    --------------------------------------------------
                </td>
            </tr>
            <tr>
                <td style="text-align: center;" class="campo">
                    Entradas adicionales
                </td>
            </tr>
            <tr>
                <td>
                    '.$html_adicion.'
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">
                    --------------------------------------------------
                </td>
            </tr>
            <tr>
                <td style="text-align: center;" class="campo">
                    Devoluciones
                </td>
            </tr>
            <tr>
                <td>
                    '.$html_devoluciones.'
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">
                    --------------------------------------------------
                </td>
            </tr>
            <tr>
                <td style="text-align: center;" class="campo">
                    Folios de creditos
                </td>
            </tr>
            <tr>
                <td>
                    '.$html_folios.'
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">
                    --------------------------------------------------
                </td>
            </tr>
            <tr>
                <td style="text-align: center;" class="campo">
                    Ventas Canceladas
                </td>
            </tr>
            <tr>
                <td>
                    '.$html_ventas.'
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">
                    --------------------------------------------------
                </td>
            </tr>
            <tr>
                <td style="text-align: center;" class="campo">
                    Departamentos 
                </td>
            </tr>
            <tr>
                <td>
                    '.$html_departamentos.'
                </td>
            </tr>';
			/*
            <tr>
                <td style="text-align: center;" class="campo">
                    Productos 
                </td>
            </tr>
            <tr>
                <td>
                    '.$html_productos.'
                </td>
            </tr>*/
			$html .= '
            <tr>
                <td class="campo">
					&nbsp; <br />
                    Fin del Corte&nbsp; <br />
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
            window.print();
			window.close();
         </script>
    </body>
    </html>";
        echo $html;
        exit(0);
    }
}