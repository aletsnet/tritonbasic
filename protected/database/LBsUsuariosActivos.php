<?php
/**
 * Auto generated by PRADO - WSAT on 2018-08-02 12:10:25.
 * @author 
 */
class LBsUsuariosActivos extends TActiveRecord
{
	const TABLE='bs_usuarios_activos';

	public $id_usuarios_activos;
	public $id_usuarios;
	public $fecha_inicio;
	public $fecha_ultima;
	public $status;

	public static function finder($className=__CLASS__) {
                return parent::finder($className);
	}

	public static $RELATIONS = array (
		'bs_usuarios' => array(self::BELONGS_TO, 'LBsUsuarios', 'id_usuarios')
	);

	public function __toString() {
		return $this->fecha_inicio;
	}
}