<?php
/**
 * Auto generated by PRADO - WSAT on 2018-08-02 12:10:25.
 * @author 
 */
class LBsCatalogosGenericos extends TActiveRecord
{
	const TABLE='bs_catalogos_genericos';

	public $id_catalogos_genericos;
	public $catalogo;
	public $opcion;
	public $valor;
	public $icon;
	public $cssclass;
	public $picture;
	public $activo;

	public static function finder($className=__CLASS__) {
                return parent::finder($className);
	}



	public function __toString() {
		return $this->opcion;
	}
}