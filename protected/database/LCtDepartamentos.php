<?php
/**
 * Auto generated by PRADO - WSAT on 2018-08-02 12:10:25.
 * @author 
 */
class LCtDepartamentos extends TActiveRecord
{
	const TABLE='ct_departamentos';

	public $id_departamentos;
	public $nombre;
	public $icon;
	public $borrado;

	public static function finder($className=__CLASS__) {
                return parent::finder($className);
	}

	public static $RELATIONS = array (
		'ms_productos' => array(self::HAS_MANY, 'LMsProductos', 'id_departamentos')
	);

	public function __toString() {
		return $this->nombre;
	}
}