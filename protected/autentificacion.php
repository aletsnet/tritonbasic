<?php
// Include TDbUserManager.php file which defines TDbUser
Prado::using('System.Security.TDbUserManager');
 
/**
 * BlogUser Class.
 * BlogUser represents the user data that needs to be kept in session.
 * Default implementation keeps username and role information.
 */
class autentificacion extends TDbUser
{
    //public $nombre_proyecto="base";
    //public $idusuarios = "";
    /**
     * Creates a BlogUser object based on the specified username.
     * This method is required by TDbUser. It checks the database
     * to see if the specified username is there. If so, a BlogUser
     * object is created and initialized.
     * @param string the specified username
     * @return BlogUser the user object, null if username is invalid.
     */
    public function createUser($username)
    {
        // use UserRecord Active Record to look for the specified username
        $usuarios=LBsUsuarios::finder()->findByuser($username);
        if($usuarios instanceof LBsUsuarios) // if found
        {
            $user=new autentificacion($this->Manager);
            $user->Name=$username;  // set username
            $user->Roles= $usuarios->id_roles; // set role
            $user->IsGuest=false;   // the user is not a guest
            //$_SESSION['idusuarios'] = $usuarios->id_usuarios;
			$user->setState('idusuarios',$usuarios->id_usuarios);
            $user->setState('lusuario',$usuarios->nombre);
            $user->setState('pass',$usuarios->pass);
			$user->setState('idroles',$usuarios->id_roles);
            $user->setState('idsucursales',$usuarios->id_sucursales);
            $user->setState('sucursal',$usuarios->ct_sucursales->sucursal);
            $user->setState('lsucursal',$usuarios->ct_sucursales->sucursal);
			$user->setState('iddependencias',$usuarios->id_sucursales);
			$user->setState('dependencia',$usuarios->ct_sucursales->sucursal);
            return $user;
        }else{
            //$this->bitacora("usuario",1,"intento de acceso:".$username);
            return null;
        }
    }
 
    /**
     * Checks if the specified (username, password) is valid.
     * This method is required by TDbUser.
     * @param string username
     * @param string password
     * @return boolean whether the username and password are valid.
     */
    public function validateUser($username,$password)
    {
        // use UserRecord Active Record to look for the (username, password) pair.
        $ct_buscar = new TActiveRecordCriteria;
        $parametros = " activo = 1 ";
		$parametros .= " AND user = :login";
		$ct_buscar->Parameters[':login'] = $username;
		$parametros .= " AND pass = :password";
		$ct_buscar->Parameters[':password'] = sha1($password);
		//$parametros .= " AND id_accesos = :acceso";
		//$ct_buscar->Parameters[':acceso'] = $acceso;
        $ct_buscar->Condition = $parametros;
        return LBsUsuarios::finder()->find($ct_buscar)!==null;
    }
    
	public function ServicioActivo($id_servicios = ""){
		$valido = false;
		$roles = "";
		$id_usuarios = $this->getState("idusuarios");
		if($id_servicios != ""){
			$lista_servicios = explode(",",$id_servicios);
			$ct_buscar = new TActiveRecordCriteria;
			$parametros = "  activo = 1";
			$parametros .= " AND id_usuarios = :usuario";
			$ct_buscar->Parameters[':usuario'] = $id_usuarios;
			$ct_buscar->Condition = $parametros;
			$usuario_obj = LBsUsuarios::finder()->find($ct_buscar);
			if($usuario_obj instanceof LBsUsuarios){
				$lista_permitidos = array();
				$ct_buscar_rol = new TActiveRecordCriteria;
				$parametros = " borrado = 0 AND activo = 1";
				$parametros .= " AND id_roles = :id_roles";
				$parametros .= " AND id_servicios in (".$id_servicios.")";
				$ct_buscar_rol->Parameters[':id_roles'] = $usuario_obj->id_roles;
				//$ct_buscar_rol->Parameters[':id_servicios'] = $id_servicios;
				$ct_buscar_rol->Condition = $parametros;
				$lista_permitidos = LBsRolesServicios::finder()->findAll($ct_buscar_rol);
				$c = count($lista_permitidos);
				$valido = ($c > 0);
			}
		}
		return $valido;
	}
	
	public function logUsuario($actividad,$tipo){
		
	}
	
    public function getidusuarios()
    {
        return $this->getState("idusuarios");
    }
    
    public function getpass()
    {
        return $this->getState("pass");
    }
	
    public function getiddependencias()
    {
        return $this->getState("iddependencias");
    }
	
    public function getidroles()
    {
        return $this->getState("idroles");
    }
	
	public function getldependencia(){
		return $this->getState("ldependencia");
	}
    
    public function getlusuario(){
		return $this->getState("lusuario");
	}
    
    public function getidsucursales()
    {
        return $this->getState("idsucursales");
    }
	
    
    public function getsucursal()
    {
        return $this->getState("sucursal");
    }
	
	public function getlsucursal(){
		return $this->getState("lsucursal");
	}
	
    function fecha($fecha){
        if($fecha != ""){
            $fecha2 = explode(' ',$fecha);
            $n = explode("/",$fecha2[0]);
            if(count($n)>2){
                $fecha = (isset($n[2])?$n[2]:"0000")."-".(isset($n[1])?$n[1]:"00")."-".$n[0];
            }else{
                $n = explode("-",$fecha2[0]);
                $fecha = $n[2]."/".$n[1]."/".$n[0];
            }
            $fecha = $fecha . " " . (isset($fecha2[1])?$fecha2[1]:"");
        }
        return trim($fecha);
    }
    
	public function Actividad($id_usuarios){
        $usuario = LBsUsuarios::finder()->find("id_usuarios = ? ", $id_usuarios);
		if($usuario instanceof LBsUsuarios){
			$ultima_actividad = LBsUsuariosActivos::finder()->find("id_usuarios = ? AND status = ?", $id_usuarios, 1);
			if($ultima_actividad instanceof LBsUsuariosActivos){
				//tiempo
				//$tiempo = $this->tiempotrascurrio(strtotime("now"), strtotime($tiempo));
				$tiempo = strtotime("now") - strtotime($ultima_actividad->fecha_ultima);
				if($tiempo >= 1440){
					//$ultima_actividad = new LBsUsuariosActivos;
					//$ultima_actividad->id_usuarios = $id_usuarios;
					//$ultima_actividad->fecha_inicio = date('Y-m-d H:i:s');
					//$ultima_actividad->fecha_ultima = date('Y-m-d H:i:s');
					$ultima_actividad->status = 2;
					$ultima_actividad->save();
					
					$newrow = new LBsUsuariosActivos;
					$newrow->id_usuarios = $id_usuarios;
					$newrow->fecha_inicio = date('Y-m-d H:i:s');
					$newrow->fecha_ultima = date('Y-m-d H:i:s');
					$newrow->status = 1;
					$newrow->save();
			
				}else{
					$ultima_actividad->fecha_ultima = date('Y-m-d H:i:s');
					$ultima_actividad->save();
				}
				//Prado::log("t: ".$tiempo,TLogger::NOTICE,"---");
				
			}else{
				$ultima_actividad = new LBsUsuariosActivos;
				$ultima_actividad->id_usuarios = $id_usuarios;
				$ultima_actividad->fecha_inicio = date('Y-m-d H:i:s');
				$ultima_actividad->fecha_ultima = date('Y-m-d H:i:s');
				$ultima_actividad->status = 1;
				$ultima_actividad->save();
			}
		}
	}
    
    function bitacora_usuario($catalogo,$valor,$actividad){
		$id_usuario = $this->getState("idusuarios");
        $id_catalogo = 0;
        $catalogo = LBsCatalogosGenericos::finder()->find(" catalogo = ? AND valor = ?",$catalogo,$valor);
        if($catalogo instanceof LBsCatalogosGenericos){
            //
            $id_catalogo = $catalogo->id_catalogos_genericos;
            
            $row = new LBsBitacoraUsuarios;
            $row->id_catalogos_genericos = $id_catalogo;
            $row->id_usuarios            = $id_usuario;
            $row->actividad              = $actividad;
            $row->fecha                  = date("Y-m-d H:i:s");
            $row->save();          
            
        }else{
            Prado::log("[".$_SERVER['REMOTE_ADDR']."][".$this->getState("idusuarios").'][Opcion de catalogo no soportada]',TLogger::NOTICE,"bitacora");
        }
	}
	
    public function telefonos($telefono,$d){
        $t = explode("|",$telefono);
        $r = "";
        if(isset($t[$d])){
            $r = $t[$d];
        }else{
            $r = "";
        }
        return $r;
    }
    
    public function descripcion($texto){
        $nuevotxt = "";
        $texto = trim($texto);
        if(strlen($texto) > 30){
            $nuevotxt = substr($texto, 0, 27)."...";
        }else{
            $nuevotxt = $texto;
        }
        return $nuevotxt;
    }
    
    public function solonumeros($cifras){
        $numeros = trim(str_replace("'","",str_replace(",","",str_replace("$","",$cifras))));
        return $numeros;
    }
    
    function moneda($cifra){
        $cifra_orinal = $cifra;
        $numero = explode(".",$cifra_orinal);
        $enteros = "";
        $decimal = "";
        if(isset($numero[0])){
            $enteros = $numero[0];
            if(array_key_exists(1,$numero)){
                $decimal = $numero[1];
            }
            //echo "<br/>".$decimal;
            if($enteros==""){
                $enteros = "0";
            }
            if($decimal==""){
                $decimal = "00";
            }else{
                if(strlen($decimal)<2){
                    $decimal = $decimal."0";
                }
            }
            //echo "<br/>".$decimal;
            $coma = 0;
            $cantidad = "";
            $comilla = 0;
            for($i=strlen($enteros)-1;$i>=0; $i--){
                if($coma == 3){
                    $separador = ",";
                    $coma = 0;
                    $comilla++;
                    if($comilla == 2){
                        $separador = "'";
                        $comilla = 0;
                    }
                    $cantidad = $cantidad.$separador;
                }
                
                $cantidad = $cantidad.$enteros[$i];
                $coma ++;
            }
            $enteros = "";
            for($i=strlen($cantidad)-1;$i>=0; $i--){
                $enteros = $enteros.$cantidad[$i];
            }
            $cifra = "$ " . $enteros . "." . $decimal;
        }else{
            $cifra = "$ " . $numero . ".00";
        }
        return $cifra;
    }
    
    function textolimpio($texto){
        $string = trim($texto);
        return utf8_encode($string);
    }
    
    function textofechaactual(){
        $dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
        $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
         
        return $dias[date('w')]." ".date('d')." de ".$meses[date('n')-1]. " del ".date('Y') ;
    }
    
    function fechacompleta($fecha = "00/00/0000"){
        $fecha_w = explode(" ",$fecha);
        $tiempo = "";
        if(count($fecha_w)>1){
            $tiempo = $fecha_w[1];
        }
        $fecha_array = explode("/",$fecha_w[0]);
        $fecha_time = strtotime($fecha_array[2]."-".$fecha_array[1]."-".$fecha_array[0]);
        $dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
        $meses = array(" ","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        //print_r($fecha_array);
        //echo $dias[date('w',$fecha_time)];
        return $dias[date('w',$fecha_time)]." ".$fecha_array[0]." de ".$meses[(int) $fecha_array[1]]. " del ".$fecha_array[2] ." ".$tiempo ;
    }
    
    public function tiempopasado($tiempo){
		$ltime = " - ";
        $tiempo = strtotime("now") - strtotime($tiempo); //$item->Data->horainicio);
		if($tiempo < 60){
			$ltime = " hace un momento ";
		}else{
			$m = $tiempo / 60 ;
			if($m < 60){
				$ltime = round($m) . " m";
			}else{
				$h = $m / 60;
				if($h < 24){
					$ltime = round($h) . " hr".($h>1?"s":"");
				}else{
					$d = $h / 24;
					$ltime = round($d) . " día".($d>1?"s":"");
				}
			}
		}
        return " ".$ltime;
    }
	
	public function tiempoPasadoDias($tiempo){
		$ltime = " - ";
        $tiempo = strtotime("now") - strtotime($tiempo); //$item->Data->horainicio);
		if($tiempo < (60 * 60 * 24)){
			$ltime = " Hoy ";
		}else{
			$m = $tiempo / 60 ;
			$h = $m / 60;
			$d = $h / 24;
			if($d < 30){
				$ltime = round($d) . " día".($d>1?"s":"");
			}else{
				$i = $d / 30;
				if($i < 12){
					$ltime = round($i) . " mes".($i>1?"es":"");
				}else{
					$y = $i / 12;
					$ltime = round($y) . " año".($y>1?"s":"");
				}
			}
		}
        return " ".$ltime;
    }
    
	public function tiempotrascurrio($inico,$termino){
		$ltime = " - ";
        $tiempo = strtotime($termino) - strtotime($inico); //$item->Data->horainicio);
		if($tiempo < 0){
			$ltime = " ".$termino;
		}else{
			if($tiempo < 60){
				$ltime = " hace un momento ";
			}else{
				$m = $tiempo / 60 ;
				if($m < 60){
					$ltime = round($m) . " m";
				}else{
					$h = $m / 60;
					if($h < 24){
						$ltime = round($h) . " hr".($h>1?"s":"");
					}else{
						$d = $h / 24;
						//$ltime = round($d) . " día".($d>1?"s":"");
						if($d < 30){
							$ltime = round($d) . " día".($d>1?"s":"");
						}else{
							$i = $d / 30;
							if($i < 12){
								$ltime = round($i) . " mes".($i>1?"es":"");
							}else{
								$y = $i / 12;
								$ltime = round($y) . " año".($y>1?"s":"");
							}
						}
					}
				}
			}
		}
        return $ltime;
    }
	
    function edadactual($fecha = ''){
        $edad = 'No disponible';
        if($fecha != ""){
            $date2 = date('Y-m-d');
            $diff = abs(strtotime($date2) - strtotime($fecha));
            $years = floor($diff / (365*60*60*24));
            if($years > 0){
                $edad = $years . " año";
                if($years > 1){
                    $edad = $years . " años";
                }
            }
        }
        return $edad;
    }
	
	function bitacora_solicitud($idsolicitud,$catalogo,$valor,$actividad){
		$id_usuario = $this->getState("idusuarios");;
		$rowSolicitud = LMsSolicitudes::finder()->find(" borrado = 0 AND id_solicitudes = ?", $idsolicitud);
		if($rowSolicitud instanceof LMsSolicitudes){
			$id_catalogo = 0;
			$catalogo = LBsCatalogosGenericos::finder()->find(" catalogo = ? AND valor = ?",$catalogo,$valor);
			//Prado::log(TVarDumper::dump($catalogo,2),TLogger::NOTICE,"gestion");
			if($catalogo instanceof LBsCatalogosGenericos){
				$id_catalogo = $catalogo->id_catalogos_genericos;
				$ctseguimiento = new LCtSeguimiento;
				$ctseguimiento->id_catalogos_genericos = $id_catalogo;
				$ctseguimiento->id_solicitudes         = $idsolicitud;
				$ctseguimiento->id_usuarios            = $id_usuario;
				$ctseguimiento->actividad              = $actividad;
				$ctseguimiento->fecha_registrado       = date("Y-m-d H:i:s");
				$ctseguimiento->save();
			}else{
				Prado::log("[".$_SERVER['REMOTE_ADDR']."][".$this->getState("idusuarios").'][Opcion de catalogo]',TLogger::NOTICE,"bitacora");
			}
		}else{
			Prado::log("[".$_SERVER['REMOTE_ADDR']."][".$this->getState("idusuarios").'][Solicitud no encontrada]',TLogger::NOTICE,"bitacora");
		}
	}
    
    function SaldoActual($id_clientes){
		$saldo = 0;
		$clientes = LMsClientes::finder()->find(" id_clientes = ? ",$id_clientes);
        if($clientes instanceof LMsClientes)
        {
			$ventas_credito = LMsVentas::finder()->findAll(" id_clientes = ? AND estatus = 5 ",$id_clientes);
			$deuda = (float) 0;
			foreach($ventas_credito as $i => $v){
				if($v instanceof LMsVentas){
					$efectivo = (float) $v->efectivo;
					$total = (float) $v->total;
					$deuda = $deuda + ($total - $efectivo);
				}
			}
			$credito_pagado = LCtCreditosAbonos::finder()->findAll(" id_clientes = ? ",$id_clientes);
			$pagado = (float) 0;
			foreach($credito_pagado as $i => $v){
				if($v instanceof LCtCreditosAbonos){
					$pagado = $pagado + (float) $v->monto;
				}
			}
			$saldo = $deuda - $pagado;
		}
		return $saldo;
	}
	
	function idCorte(){
		$row_corte = array();
		
		$row_ticket = LCtSucursales::finder()->find(" id_sucursales = ? ", $this->idsucursales);
		if($row_ticket instanceof LCtSucursales){
			if(! $row_ticket->corte_compartido ){
				$row_corte = LMsCortes::finder()->find(" estatus = 1 AND id_sucursal = ? AND id_usuarios = ? ", $this->idsucursales, $this->idusuarios);
				//$this->lbModalidad->Text = "Modo corte por usuario";
			}else{
				$row_corte = LMsCortes::finder()->find(" estatus = 1 AND id_sucursal = ? ", $this->idsucursales);
				//$this->lbModalidad->Text = "Modo corte compartido";
			}
		}
		
		return $row_corte;
	}
	
}