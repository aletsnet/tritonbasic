<?php
/**
 * Auto generated by PRADO - WSAT on 2018-08-02 12:10:25.
 * @author 
 */
class LCtSucursales extends TActiveRecord
{
	const TABLE='ct_sucursales';

	public $id_sucursales;
	public $sucursal;
	public $direccion;
	public $corte_compartido;
	public $ticket_head;
	public $ticket_fool;
	public $ticket_logo;
	public $ticket_imprimir;
	public $ticket_automatico;
	public $borrado;

	public static function finder($className=__CLASS__) {
                return parent::finder($className);
	}

	public static $RELATIONS = array (
		'bs_usuarios' => array(self::HAS_MANY, 'LBsUsuarios', 'id_sucursales'),
		'ct_accesos' => array(self::HAS_MANY, 'LCtAccesos', 'id_sucursal'),
		'ct_bodegas' => array(self::HAS_MANY, 'LCtBodegas', 'id_sucursales'),
		'ct_tickets' => array(self::HAS_MANY, 'LCtTickets', 'id_sucursales'),
		'ms_cortes' => array(self::HAS_MANY, 'LMsCortes', 'id_sucursal'),
		'ms_inventarios' => array(self::HAS_MANY, 'LMsInventarios', 'id_sucursales'),
		'ms_movimientos' => array(self::HAS_MANY, 'LMsMovimientos', 'id_sucursales'),
		'ms_ventas' => array(self::HAS_MANY, 'LMsVentas', 'id_sucursales')
	);

	public function __toString() {
		return $this->sucursal;
	}
}