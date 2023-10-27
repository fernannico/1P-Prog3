<?php
    include_once "./Instancias/Retiro.php";
    include_once "./Instancias/Cuenta.php";
    include_once "./Instancias/Deposito.php";
    // $rutaDespositosJson = './ArchivosJson/depositos.json';
    // $rutaRetirosJson = './ArchivosJson/retiro.json';

    //4- ConsultaMovimientos.php: (por GET)
    // Datos a consultar:
    
    # a- El total depositado (monto) por tipo de cuenta y moneda en un día en particular (se envía por parámetro), si no se pasa fecha, se muestran las del día anterior.
    function ConsultarTotalDepositado(){
        if (!isset($_GET["tipoCuenta"]) || !isset($_GET["moneda"])) {
            echo "<br>faltan parametros.";
        }else {
            $rutaDespositosJson = './ArchivosJson/depositos.json';
            $fecha = null;
            $tipoCuenta = null;
            $moneda = null;
    
            if(Cuenta::ValidarTipoCuenta($_GET["tipoCuenta"])){
                $tipoCuenta = $_GET["tipoCuenta"];
            }else{
                echo "tipo de cuenta incorrecto";
            }
            if(Cuenta::ValidarMoneda($_GET["moneda"])){
                $moneda = $_GET["moneda"];
            }else{
                echo "moneda ingresada incorrecta";
            }
            if(isset($_GET["fecha"]) && !empty($_GET["fecha"])) {
                $fecha = $_GET["fecha"];
            }else{
                $fechaAnterior = date("d-m-Y", strtotime(date("d-m-Y") . "-1 day"));
                $fecha = $fechaAnterior;
            }

            if($fecha != null && $tipoCuenta != null && $moneda != null){
                echo "CONSULTA MOVIMIENTOS =>";
                echo " fecha: " . $fecha;
                echo '<BR>Depositos:';
    
                $totalDepositado = Deposito::CalcularDepositosPorTipoYFecha($tipoCuenta,$moneda,$fecha,$rutaDespositosJson);
                if($totalDepositado > 0) {
                    echo '<br>Total depositado: '. $totalDepositado;
                } else {
                    echo "<br>No hay depositos en esa fecha";
                }
            }                  
        }
    }    

    # b- El listado de depósitos para un usuario en particular.  
    function consultarDepositosPorUsuario() {

        $usuarioParametro = null;
        if (!isset($_GET["dni"]) || empty($_GET["dni"]) || trim($_GET["dni"]) === "") {
            echo "<br>faltan parametros.";
        }else {
            $rutaDespositosJson = './ArchivosJson/depositos.json';
            $rutaCuentasJson = './ArchivosJson/banco.json';

            $usuarioParametro = $_GET["dni"];
            
            if($usuarioParametro !== null) {
                // echo $usuarioParametro;
                $depositosUsuario = Deposito::ObtenerDepositosPorDni($usuarioParametro,$rutaCuentasJson,$rutaDespositosJson);
                if(!empty($depositosUsuario)){
                    foreach($depositosUsuario as $deposito) {
                        echo '<br>-----------<br>CUENTA: '. $deposito->GetNroCuenta();
                        echo '<br><br>Deposito: '. $deposito->__toString() . "<br>";
                    }
                }else{
                    echo "<br>El usuario no tiene depositos";
                }
            }else {
                echo "<br>falta cargar el usuario como parametro";
            }
        }
    }

    # c- El listado de depósitos entre dos fechas ordenado por nombre.
    function consultarDepositosEntreFechas(){
        $fechaInicio = null;
        $fechaFin = null;
        if (!isset($_GET["fechaInicio"]) || empty($_GET["fechaInicio"]) || trim($_GET["fechaInicio"]) === "" || 
            !isset($_GET["fechaFin"]) || empty($_GET["fechaFin"]) || trim($_GET["fechaFin"]) === "") {
            echo "<br>faltan parametros.";
        }else {
            $fechaInicio = $_GET["fechaInicio"];
            $fechaFin = $_GET["fechaFin"];
            
            if($fechaFin !== null && $fechaInicio !== null) {
                // echo 'entra';
                $depositosEntreFechas = Deposito::ObtenerDepositosEntreFechas($fechaInicio,$fechaFin,'./ArchivosJson/depositos.json');
                $depositos = Deposito::OrdenarDepositosPorNumeroCuenta($depositosEntreFechas);
                if($depositos !== null && !empty($depositos)){
                    foreach($depositos as $deposito) {
                        echo "<br>" . $deposito->__toString() . "<br>";
                    }
                }else {
                    echo "<br>no hay depositos entre estas fechas";
                }
            }
        }
    }

    # d- El listado de depósitos por tipo de cuenta.
    function consultarDepositosPorTipoCuenta(){
        
        $tipoCuenta = null;
        if (!isset($_GET["tipoCuenta"]) /*|| empty($_GET["tipoCuenta"]) || trim($_GET["tipoCuenta"]) === ""*/) {
            echo "<br>faltan parametros.";
        }else {
            if(Cuenta::ValidarTipoCuenta($_GET["tipoCuenta"])){
                $tipoCuenta = $_GET["tipoCuenta"];
            }else{
                echo "tipo de cuenta incorrecto";
            }
            
            if($tipoCuenta !== null) {
                // echo $tipoCuenta;
                $depositosTipoCuenta = Deposito::ObtenerDepositosPorTipoCuenta($tipoCuenta,'./ArchivosJson/depositos.json');
                // var_dump($depositosTipoCuenta);
                if($depositosTipoCuenta !== null && !empty($depositosTipoCuenta)) {
                    // echo "entra";
                    foreach($depositosTipoCuenta as $deposito){
                        echo '<br>Deposito: '. $deposito->__toString() . "<br>";
                    }
                }else {
                    echo "<br>No hay depositos";
                }
            }else {
                echo "<br>falta cargar el tipo de cuenta como parametro";
            }
        }
    }

    # e- El listado de depósitos por moneda.
    function consultarDepositosPorMoneda(){
                
        $moneda = null;
        if (!isset($_GET["moneda"]) ) {
            echo "<br>faltan parametros.";
        }else {
            if(Cuenta::ValidarMoneda($_GET["moneda"])){
                $moneda = $_GET["moneda"];
            }else{
                echo "tipo de moneda incorrecto";
            }
            
            if($moneda !== null) {
                $depositosMoneda = Deposito::ObtenerDepositosPorMoneda($moneda,'./ArchivosJson/depositos.json');
                
                if($depositosMoneda !== null && !empty($depositosMoneda)) {
                    foreach($depositosMoneda as $deposito){
                        echo '<br>Deposito: '. $deposito->__toString() . "<br>";
                    }
                }else {
                    echo "<br>No hay depositos";
                }
            }else {
                echo "<br>falta cargar el tipo de moneda como parametro";
            }
        }
    }

    #f- El listado de todas las operaciones (depósitos y retiros) por usuario
    function consultarOperacionesPorUsuario(){  
        $nroDocumento = null;
        if (!isset($_GET["nroDocumento"])) {
            echo "<br>faltan parametros.";
        }else {
            $nroDocumento = $_GET["nroDocumento"];
            $cuentasJson = Cuenta::ObtenerCuentasPorDni($nroDocumento,'./ArchivosJson/banco.json');
            $depositos = Deposito::JsonDeserialize('./ArchivosJson/depositos.json');
            $retiros = Retiro::JsonDeserialize('./ArchivosJson/retiro.json');
            
            if($cuentasJson !== null && !empty($cuentasJson) && $depositos !== null && !empty($depositos) && $retiros !== null && !empty($retiros)){
                // echo "entra";
                foreach($cuentasJson as $cuenta){
                    if($cuenta->GetEstado() !== "inactivo"){
                        $banderaCuentas = false;
                        foreach($depositos as $deposito){
                            if($deposito->GetNroCuenta() == $cuenta->GetNroCuenta()){
                                echo "<br><br>" . $deposito->__toString();
                                $banderaCuentas = true;
                            }    
                        }
                        foreach($retiros as $retiro){
                            if($retiro->GetNroCuenta() == $cuenta->GetNroCuenta()){
                                echo "<br><br>" . $retiro->__toString();
                                $banderaCuentas = true;
                            }    
                        }
                        if(!$banderaCuentas){
                            echo "<br>la cuenta nro: " . $cuenta->GetNroCuenta() . " no tiene depositos ni retiros";
                        }
                    }else{
                        echo "<br>la cuenta nro: ". $cuenta->GetNroCuenta() . " esta inactiva<br>";
                    }
                }
            }else{
                echo "<br>No se encontró el archivo de Cuentas/Depositos/retiros";
            }
        }
    }

    //funcion que me retorne el array de los dias que hay entre dos fechas para despues comparar si los depositos->getFecha matchea con alguno del array
    #a2
    function ConsultarTotalRetirado(){
        if (!isset($_GET["tipoCuenta"]) || !isset($_GET["moneda"])) {
            echo "<br>faltan parametros.";
        }else {
            $rutaRetirosJson = './ArchivosJson/retiro.json';
            $fecha = null;
            $tipoCuenta = null;
            $moneda = null;
    
            if(Cuenta::ValidarTipoCuenta($_GET["tipoCuenta"])){
                $tipoCuenta = $_GET["tipoCuenta"];
            }else{
                echo "tipo de cuenta incorrecto";
            }
            if(Cuenta::ValidarMoneda($_GET["moneda"])){
                $moneda = $_GET["moneda"];
            }else{
                echo "moneda ingresada incorrecta";
            }
            if(isset($_GET["fecha"]) && !empty($_GET["fecha"])) {
                $fecha = $_GET["fecha"];
            }else{
                $fechaAnterior = date("d-m-Y", strtotime(date("d-m-Y") . "-1 day"));
                $fecha = $fechaAnterior;
            }

            if($fecha != null && $tipoCuenta != null && $moneda != null){
                echo "CONSULTA MOVIMIENTOS =>";
                echo " fecha: " . $fecha;
                echo '<BR>Retiros:';
    
                $totalRetirado = Retiro::CalcularRetirosPorTipoYFecha($tipoCuenta,$moneda,$fecha,$rutaRetirosJson);
                if($totalRetirado > 0) {
                    echo '<br>Total Retirado: '. $totalRetirado;
                } else {
                    echo "<br>No hay Retiros en esa fecha";
                }
            }                  
        }

    }
    
    #b2
    function consultarRetirosPorUsuario(){

        $usuarioParametro = null;
        if (!isset($_GET["dni"]) || empty($_GET["dni"]) || trim($_GET["dni"]) === "") {
            echo "<br>faltan parametros.";
        }else {
            $rutaRetirosJson = './ArchivosJson/retiro.json';
            $rutaCuentasJson = './ArchivosJson/banco.json';

            $usuarioParametro = $_GET["dni"];
            
            if($usuarioParametro !== null) {
                // echo $usuarioParametro;
                $RetirosUsuario = Retiro::ObtenerRetirosPorDni($usuarioParametro,$rutaCuentasJson,$rutaRetirosJson);
                if(!empty($RetirosUsuario)){
                    foreach($RetirosUsuario as $Retiro) {
                        echo '<br>-----------<br>CUENTA: '. $Retiro->GetNroCuenta();
                        echo '<br><br>Retiro: '. $Retiro->__toString() . "<br>";
                    }
                }else{
                    echo "<br>El usuario no tiene Retiros";
                }
            }else {
                echo "<br>falta cargar el usuario como parametro";
            }
        }
    }

    #c2
    function consultarRetirosEntreFechas(){
        $fechaInicio = null;
        $fechaFin = null;
        if (!isset($_GET["fechaInicio"]) || empty($_GET["fechaInicio"]) || trim($_GET["fechaInicio"]) === "" || 
            !isset($_GET["fechaFin"]) || empty($_GET["fechaFin"]) || trim($_GET["fechaFin"]) === "") {
            echo "<br>faltan parametros.";
        }else {
            $fechaInicio = $_GET["fechaInicio"];
            $fechaFin = $_GET["fechaFin"];
            
            if($fechaFin !== null && $fechaInicio !== null) {
                // echo 'entra';
                $retirosEntreFechas = retiro::ObtenerRetirosEntreFechas($fechaInicio,$fechaFin,'./ArchivosJson/retiro.json');
                $retiros = retiro::OrdenarRetirosPorNumeroCuenta($retirosEntreFechas);
                if($retiros !== null && !empty($retiros)){
                    foreach($retiros as $retiro) {
                        echo "<br>" . $retiro->__toString() . "<br>";
                    }
                }else {
                    echo "<br>no hay retiros entre estas fechas";
                }
            }
        }

    }

    #d2
    function consultarRetirosPorTipoCuenta(){

        $tipoCuenta = null;
        if (!isset($_GET["tipoCuenta"]) ) {
            echo "<br>faltan parametros.";
        }else {
            if(Cuenta::ValidarTipoCuenta($_GET["tipoCuenta"])){
                $tipoCuenta = $_GET["tipoCuenta"];
            }else{
                echo "tipo de cuenta incorrecto";
            }
            
            if($tipoCuenta !== null) {
                // echo $tipoCuenta;
                $RetirosTipoCuenta = Retiro::ObtenerRetirosPorTipoCuenta($tipoCuenta,'./ArchivosJson/retiro.json');
                // var_dump($RetirosTipoCuenta);
                if($RetirosTipoCuenta !== null && !empty($RetirosTipoCuenta)) {
                    // echo "entra";
                    foreach($RetirosTipoCuenta as $Retiro){
                        echo '<br>Retiro: '. $Retiro->__toString() . "<br>";
                    }
                }else {
                    echo "<br>No hay Retiros";
                }
            }else {
                echo "<br>falta cargar el tipo de cuenta como parametro";
            }
        }
    }
    
    #e2
    function consultarRetirosPorMoneda(){
        
        $moneda = null;
        if (!isset($_GET["moneda"]) ) {
            echo "<br>faltan parametros.";
        }else {
            if(Cuenta::ValidarMoneda($_GET["moneda"])){
                $moneda = $_GET["moneda"];
            }else{
                echo "tipo de moneda incorrecto";
            }
            
            if($moneda !== null) {
                $RetirosMoneda = Retiro::ObtenerRetirosPorMoneda($moneda,'./ArchivosJson/retiro.json');
                
                if($RetirosMoneda !== null && !empty($RetirosMoneda)) {
                    foreach($RetirosMoneda as $Retiro){
                        echo '<br>Retiro: '. $Retiro->__toString() . "<br>";
                    }
                }else {
                    echo "<br>No hay Retiros";
                }
            }else {
                echo "<br>falta cargar el tipo de moneda como parametro";
            }
        }
    }

?>