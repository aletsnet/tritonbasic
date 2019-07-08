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
        
        $id_movimiento = $this->request['ticket'];
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
        $row_movimiento = LMsMovimientos::finder()->find(" id_movimientos = ?", array($id_movimiento));
		if($row_movimiento instanceof LMsMovimientos){
			$neta = 0;
            $sucursal = $row_movimiento->ct_sucursales;
			$estatus_ticket = ($row_movimiento->tipo_movimiento == 1 ? "Entrada de mercancia" : "Salida de mercancia" );
            $lista = '';
            $rows_inventario = LCtMovimientosInventarios::finder()->findAll(" id_movimientos = ? ",array($id_movimiento));
            foreach($rows_inventario as $i => $row_detalle){
                $lista .= '
                <tr>
                    <td class="lcampos">
                        '.$row_detalle->ms_inventarios->ms_productos->codigo.'
                    </td>
                    <td class="lcampos">
                        '.$row_detalle->ms_inventarios->ms_productos->nombre.'
                    </td>
                    <td class="rcampos">
                        '.$row_detalle->cantidad.' '.$row_detalle->ms_inventarios->unidad . '
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
                    &nbsp; <br />&nbsp; <br />
                </td>
            </tr>
            <tr>
                <td class="campo">
				'.$estatus_ticket.'
                </td>
            </tr>
            <tr>
                <td class="rcampos" >
                    fecha: '.$this->User->fecha($row_movimiento->fecha_movimiento).'
                </td>
            </tr>
            <tr>
                <td class="lcampos" >
                    '.$row_movimiento->descripcion.'
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">
                    --------------------------------------------------
                </td>
            </tr>
            <tr>
                <td>
                    <table cellpadding="0" cellspacing="0" style="width: 100%;" border="0">
                        <tr>
                            <td class="campo" style="">
                                Codigo
                            </td>
                            <td class="campo" style="">
                                Articulo
                            </td>
                            <td class="campo" style="width: 10%;">
                                Cant.
                            </td>
                        </tr>
                        '.$lista.'
                    </table>
                </td>
            </tr>
            <tr>
                <td class="campo">
					&nbsp; <br />
                    &nbsp; <br />
					&nbsp; <br />
					&nbsp; <br />
					&nbsp; <br />
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
