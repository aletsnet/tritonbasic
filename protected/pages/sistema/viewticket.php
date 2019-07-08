<?php
 
class viewticket extends TPage
{
	public $i = 0;
	
    public function onLoad($param)
    {
        //print_r($this->request);
		$html = "";
		$sucursal = $this->request['sucursal'];
        $image = $this->request['foto'];
		$top = $this->request['top'];
		$button = $this->request['button'];
		$impresoras = $this->request['impresoras'];
		$modo = $this->request['modo'];
		
		$mostrarImpresoras = "";
		if($modo == 2){
			if(!$impresoras ){
				$mostrarImpresoras = "<script>
			try{
				// set portrait orientation
				//jsPrintSetup.setOption('orientation', jsPrintSetup.kPortraitOrientation);
				// set top margins in millimeters
				jsPrintSetup.setOption('marginTop', 0);
				jsPrintSetup.setOption('marginBottom', 0);
				jsPrintSetup.setOption('marginLeft', 10);
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
				//alert(jsPrintSetup.setOption)
				// Do Print 
				// When print is submitted it is executed asynchronous and
				// script flow continues after print independently of completetion of print process! 
				jsPrintSetup.print();
				// next commands
				window.close();
			}catch(err) {
				//alert(err.message);
				$('#linkRequerimiento').attr('href','https://addons.mozilla.org/es/firefox/addon/js-print-setup/');
				$('#linkRequerimiento').html('Componente requerido');
			}
			</script>";
			}else{
				$mostrarImpresoras = "<script>
				window.print();
				window.close();
			</script>";
			}
		}else{
			$row = LCtSucursales::finder()->find(" id_sucursales = 1 ");
			if($row instanceof LCtSucursales){
				$sucursal = $row->sucursal;
				$image = $row->ticket_logo;
				$top = $row->ticket_head;
				$button = $row->ticket_fool;
				//$impresoras = $row->sucursal;
				//$modo = $row->sucursal;
			}
		}
        //$html .= "ViewTicket";
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
		<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
    </head>
    <body onload=""> <a id="linkRequerimiento" target="_blank"></a>
	<table cellpadding="0" border="0" style="width: 300px;">
            <tr>
                <td class="campo">
                     <img src="'.$image.'" alt="Logo" height="128" /> 
                </td>
            </tr>
            <tr>
                <td class="campo">
                    '.$top.'
                </td>
            </tr>
            <tr>
                <td class="campo">
                    '.$sucursal.'&nbsp;
                </td>
            </tr>
            <tr>
                <td class="campo">
                    Ticket de Muestra
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
                                Articulo
                            </td>
                            <td class="campo" style="width: 10%;">
                                Cant.
                            </td>
                            <td class="campo" style="width: 23%;">
                                Precio
                            </td>
                            <td class="campo" style="width: 23%;">
                                SubT.
                            </td>
                        </tr>
						<tr>
                            <td class="campo" style="">
                                
                            </td>
                            <td class="campo" style="width: 10%;">
                                
                            </td>
                            <td class="campo" style="width: 23%;">
                                
                            </td>
                            <td class="campo" style="width: 23%;">
                                
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
                <td>
                    <table cellpadding="0" cellspacing="0" style="width: 100%;" border="0">
                        <tr>
                            <td class="rcampos" style="">
                                Total:
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format(0,2).'
                            </td>
                        </tr>
						<tr>
                            <td class="rcampos" style="">
                                Efectivo:
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format(0,2).'
                            </td>
                        </tr>
						<tr>
                            <td class="rcampos" style="">
                                Cambio:
                            </td>
                            <td class="rcampos" style="width: 30%;">
                                '."$ ".number_format(0,2).'
								
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="campo">
					&nbsp; <br />
                    '.$button.'
					&nbsp; <br />
					&nbsp; <br />
					&nbsp; <br />
					&nbsp; <br />
                </td>
			</tr>
        </table>'.
	$mostrarImpresoras.'
	</body>
</html>';
        echo $html;
        exit(0);
    }
}