<?php
/**
 * Auto generated by PRADO - WSAT on 2018-08-02 12:10:25.
 * @author 
 */
class LCtMovimientosInventarios extends TActiveRecord
{
	const TABLE='ct_movimientos_inventarios';

	public $id_movimientos_inventarios;
	public $id_movimientos;
	public $id_inventarios;
	public $cantidad;
	public $preciopublico;
	public $precioadquisicion;
	public $borrado;

	public static function finder($className=__CLASS__) {
                return parent::finder($className);
	}

	public static $RELATIONS = array (
		'ms_inventarios' => array(self::BELONGS_TO, 'LMsInventarios', 'id_inventarios'),
		'ms_movimientos' => array(self::BELONGS_TO, 'LMsMovimientos', 'id_movimientos')
	);

	public function __toString() {
		return $this->cantidad;
	}
}