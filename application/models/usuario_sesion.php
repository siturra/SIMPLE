<?php

class UsuarioSesion {

    private static $user;

    private function __construct() {
        
    }

    public static function usuario() {

        if (!isset(self::$user)) {

            $CI = & get_instance();

            if (!$user_id = $CI->session->userdata('usuario_id')) {
                return FALSE;
            }

            if (!$u = Doctrine::getTable('Usuario')->find($user_id)) {
                return FALSE;
            }

            self::$user = $u;
        }

        return self::$user;
    }

    public static function force_login() {
        $CI = & get_instance();

        if ($CI->lightopenid->mode == 'id_res') {
            self::login_open_id();
        }

        if (!self::usuario()) {
            //Elimino los antiguos
            Doctrine::getTable('Usuario')->cleanNoRegistrados();

            //Creo un usuario no registrado
            $usuario = new Usuario();
            $usuario->usuario = random_string('unique');
            $usuario->password = random_string('alnum', 32);
            $usuario->registrado = 0;
            $usuario->save();

            $CI->session->set_userdata('usuario_id', $usuario->id);
            self::$user = $usuario;
        }
    }

    public static function login($usuario, $password) {
        $CI = & get_instance();

        $autorizacion = self::validar_acceso($usuario, $password);

        if ($autorizacion) {
            $u = Doctrine::getTable('Usuario')->findOneByUsuario($usuario);

            //Si estaba con tramites en curso antes de loguearse, se los transferimos.
            if (self::usuario() && !self::usuario()->registrado) {
                foreach (self::$user->Etapas as $t) {
                    $t->usuario_id = $u->id;
                    $t->save();
                }
            }

            //Logueamos al usuario
            $CI->session->set_userdata('usuario_id', $u->id);
            self::$user = $u;

            return TRUE;
        }

        return FALSE;
    }

    public static function validar_acceso($usuario, $password) {
        $u = Doctrine::getTable('Usuario')->findOneByUsuario($usuario);

        if ($u) {

            // this mutates (encrypts) the input password
            $u_input = new Usuario();
            $u_input->password = $password;

            // password match (comparing encrypted passwords)
            if ($u->password == $u_input->password) {
                unset($u_input);


                return TRUE;
            }

            unset($u_input);
        }

        // login failed
        return FALSE;
    }

    private static function login_open_id() {
        $CI = & get_instance();
        if ($CI->lightopenid->validate()) {
            $atributos = $CI->lightopenid->getAttributes();
            $usuario = Doctrine::getTable('Usuario')->findOneByUsuario($CI->lightopenid->identity);
            if (!$usuario) {
                $usuario = new Usuario();
                $usuario->usuario = $CI->lightopenid->identity;
                $usuario->registrado = 1;
            }
            $usuario->email = $atributos['contact/email'];
            $usuario->nombre = $atributos['namePerson/first'];
            $usuario->apellidos = $atributos['namePerson/last'];
            $usuario->save();
            
            //Si estaba con tramites en curso antes de loguearse, se los transferimos.
            if (self::usuario() && !self::usuario()->registrado) {
                foreach (self::$user->Etapas as $t) {
                    $t->usuario_id = $usuario->id;
                    $t->save();
                }
            }

            $CI->session->set_userdata('usuario_id', $usuario->id);
            self::$user = $usuario;
        }
    }

    public static function logout() {
        $CI = & get_instance();
        self::$user = NULL;
        $CI->session->unset_userdata('usuario_id');
    }

    public function __clone() {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

}

?>