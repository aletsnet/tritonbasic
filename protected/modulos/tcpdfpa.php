<?php
//require_once('tcpdf/config/tcpdf_config.php'); 
//require_once('tcpdf/config/lang/spa.php');
//require_once('tcpdf/tcpdf.php');
class tcpdfpa extends TCPDF {
    public $titulo;
	public $subtitulo;
    //Page header
    public function Header() {
		$this->SetY(5);
		$this->SetX(5);
		$html = '<table width="100%" border="0" cellspacing="2" cellpadding="2" style="font-family:Verdana, Geneva, sans-serif;">
				<tr>
					<td align="center" width="20%">
						<img src="favicon/ms-icon-150x150.png" width="64px" />
					</td>
					<td width="80%">
						<h4>'.$this->titulo.'</h4>
						<label>'.$this->subtitulo.'</label>
					</td>
				</tr>
			</table>';
			$this->writeHTML($html, true, false, true, false, '');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
		// Set font
        $this->SetFont('helvetica', 'I', 8);
		$html = '<table width="100%" border="0" cellspacing="0" cellpadding="0" >
				<tr>
					<td align="right" >
						<small>Sistema Tritón - megatec.mx</small>
					</td>
				</tr>
			</table>';
			$this->writeHTML($html, true, false, true, false, '');
        // Page number
        $this->Cell(0, 5, 'Pagina '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
} 
?>