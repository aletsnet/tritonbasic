<?php
class PublicLayout extends TTemplateControl
{
    public function onLoad($param)
    {
		$this->lVersion->Text = $this->Application->Parameters["version"];
		//$this->lAplication->Text = $this->Application->Parameters["proyecto"];
		$this->lProyecto->Text= $this->Application->Parameters["proyecto"];
		$this->lProyecto->NavigateUrl = "http://".$this->Application->Parameters["urlProyecto"];
		$this->lnkAutor->Text = $this->Application->Parameters["desarrollo"];
		$this->lnkAutor->NavigateUrl = "#";
		
	}
}
?>