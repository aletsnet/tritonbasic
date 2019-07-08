<?php
class registro extends TPage
{
    public function onLoad($param)
    {
        //prototipo AguaBlanca
        $this->Response->redirect($this->Service->constructUrl("menu"));
        
        //modulos publicos
        $this->RpModulos->DataSource = LBsModulos::finder()->findAll(" borrado = ? AND activo = ? AND tipo = ?",array(0,1,4));
        $this->RpModulos->dataBind();
        
        //giros
        $this->cmdGiro->DataSource = LCgGiros::finder()->findAll(" borrado = ? ",array(0));
        $this->cmdGiro->dataBind();
    }
}