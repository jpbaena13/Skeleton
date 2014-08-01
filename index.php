<?php

/**
 * Página inicial del sitio
 * @author      JpBaena13
 * @since       PHP
 */

/**
 * Obliga al usuario a pasar primero por esta página. Es verificado
 * en el index del webroot.
 */
    define('INIT', '');

/**
 * Use DS para separar los directorios en otras definiciones
 */
    if (!defined('DS')) {
        define('DS', DIRECTORY_SEPARATOR);
    }

/**
 * Ruta completa al directorio donde está hospedada la aplicación.
 */
    if (!defined('ROOT')) {
        define('ROOT', (dirname(__FILE__)) . DS);
    }

    if (!include ROOT . DS . 'include' . DS . 'bootstrap.php') {
        trigger_error('Error al cargar el Bootstrap', E_USER_ERROR);
        exit();
    }

    try {

        require_once EXCEPTIONS . ('Exceptions.php');
        require_once CONTROLLER . ('Auth.controller.php');
        
        $auth = new AuthController();

        if (isset($_GET['controller'])) {
            $controller = $_GET['controller'];
        } else {
            $controller = 'HOME';
        }

        // Mostrando error 404 si no se encuentra la página
        if (! include_once CONTROLLER . ( $controller . '.controller.php' ) ) {
            throw new ControllerNotFoundException();
        }

        // Instanciando controlador
        $controller = eval( 'new ' . $controller . 'Controller();' );

    } catch (ControllerNotFoundException $exc) {
        include ERRORS . '404.html';        

    } catch (MethodNotImplementedException $exc) {
        include ERRORS . '404.html';

    } catch (InvalidTokenException $exc) {
        include ERRORS . 'invalidToken.html';

    } catch (AuthenticationRequiredException $exc) {
        $error['error'] = array( "msg" => $exc->getMessage(), "code" => $exc->getCode(), "type" => get_class( $exc ) );
        
        echo json_encode($error);

    } catch (NotAuthorizedException $exc) {
        $error['error'] = array( "msg" => $exc->getMessage(), "code" => $exc->getCode(), "type" => get_class( $exc ) );
        
        if ( isset(headers_list()[4]) ) {            
            if (headers_list()[4] == 'Content-Type: application/json') {
                echo json_encode($error);
            }
        } else {
            if ($auth->hasSession())
                header('Location:' . ROOT_URL);
            else
                header('Location:' . ROOT_URL . 'Login?uri=' . urlencode($_SERVER['REQUEST_URI']));
            exit();
        }

    } catch (BadRequestException $exc) {
        $error['error'] = array( "msg" => $exc->getMessage(), "code" => $exc->getCode(), "type" => get_class( $exc ) );

        echo json_encode($error);

     } catch (AJAXRequestException $exc) {
        $error['error'] = array( "msg" => $exc->getMessage(), "code" => $exc->getCode(), "type" => get_class( $exc ) );

        echo json_encode($error);
        
    } catch (DatetimeDeprecatedException $exc) {
        $error['error'] = array( "msg" => $exc->getMessage(), "code" => $exc->getCode(), "type" => get_class( $exc ) );

        echo json_encode($error);

    } catch (UserNotPhoneException $exc) {
        $error['error'] = array( "msg" => $exc->getMessage(), "code" => $exc->getCode(), "type" => get_class( $exc ) );

        echo json_encode($error);

    } catch (Exception $exc) {
        printLog($exc);
        
        header('HTTP/1.0 500 Internal Server Error');
        include ERRORS . 'requestNotProcessed.html';
    }

?>