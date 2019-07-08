<?php
/**
 * Auto generated by PRADO - WSAT on 2018-08-02 12:10:25.
 * @author 
 */
class LMsInventarios extends TActiveRecord
{
	const TABLE='ms_inventarios';

	public $id_inventarios;
	public $id_sucursales;
	public $id_bodegas;
	public $id_productos;
	public $precioadquisicion;
	public $preciopublico;
	public $stock;
	public $minimo_stock;
	public $maximo_stock;
	public $control_stock;
	public $unidad;
	public $borrado;

	public static function finder($className=__CLASS__) {
                return parent::finder($className);
	}

	public static $RELATIONS = array (
		'ct_movimientos_inventarios' => array(self::HAS_MANY, 'LCtMovimientosInventarios', 'id_inventarios'),
		'ct_ventas_detalle' => array(self::HAS_MANY, 'LCtVentasDetalle', 'id_inventarios'),
		'ct_sucursales' => array(self::BELONGS_TO, 'LCtSucursales', 'id_sucursales'),
		'ct_bodegas' => array(self::BELONGS_TO, 'LCtBodegas', 'id_bodegas'),
		'ms_productos' => array(self::BELONGS_TO, 'LMsProductos', 'id_productos')
	);

	public function __toString() {
		return $this->precioadquisicion;
	}
}