<?php
/**
 * Auto generated by PRADO - WSAT on 2018-08-02 12:10:25.
 * @author 
 */
class LBsUsuarioPermiso extends TActiveRecord
{
	const TABLE='bs_usuario_permiso';

	public $id_usuario_permiso;
	public $id_usuarios;
	public $id_permisos;

	public static function finder($className=__CLASS__) {
                return parent::finder($className);
	}

	public static $RELATIONS = array (
		'bs_usuarios' => array(self::BELONGS_TO, 'LBsUsuarios', 'id_usuarios'),
		'bs_permisos' => array(self::BELONGS_TO, 'LBsPermisos', 'id_permisos')
	);

	public function __toString() {
		return $this->id_usuario_permiso;
	}
}