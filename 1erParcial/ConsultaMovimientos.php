<?php
    include_once "./Clases/Retiro.php";
    include_once "./Clases/Cuenta.php";
    include_once "./Clases/Deposito.php";

    //4- ConsultaMovimientos.php: (por GET)
    // Datos a consultar:
    
    # a- El total depositado (monto) por tipo de cuenta y moneda en un día en particular (se envía por parámetro), si no se pasa fecha, se muestran las del día anterior.
    function ConsultarTotalDepositado(){
        if (!isset($_GET["tipoCuenta"]) || !isset($_GET["moneda"])) {
            echo "<br>faltan parametros.";
        }else {
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
    
                $depositosJson = Deposito::JsonDeserialize('./ArchivosJson/depositos.json');
    
                $totalDepositado= 0;
                
                echo '<BR>Depositos:';
                $hayDepositos = false;
                foreach($depositosJson as $deposito){
                    // echo '<br>' . $deposito->GetId();
                    if($deposito->GetTipoCuenta() == $tipoCuenta && $deposito->GetMoneda() == $moneda && $deposito->GetFecha() == $fecha) {
                        echo "<BR>ID: " . $deposito->GetId() . " - Fecha:" . $deposito->GetFecha();
                        $totalDepositado += $deposito->GetDeposito();
                        $hayDepositos = true;
                    }
                }
                if($hayDepositos) {
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
            $usuarioParametro = $_GET["dni"];
            
            if($usuarioParametro !== null) {
                // echo $usuarioParametro;
                $cuentasUsuario = Cuenta::ObtenerCuentasPorDni($usuarioParametro,'./ArchivosJson/banco.json');
                $depositosJson = Deposito::JsonDeserialize('./ArchivosJson/depositos.json');

                if($cuentasUsuario !== null && !empty($cuentasUsuario)) {
                    if($depositosJson !== null && !empty($depositosJson)) {
                        $banderaDepositos = false;
                        foreach($cuentasUsuario as $cuenta){         //hasta aca tengo las cuentas, pero necesito los depositos
                            //echo '<br> '. $cuenta->__toString();
                            foreach($depositosJson as $deposito){
                                if($cuenta->GetNroCuenta() == $deposito->GetNroCuenta()) {
                                    $banderaDepositos = true;
                                    echo '<br>-----------<br>Cuenta: '. $cuenta->__toString();
                                    echo '<br><br>Deposito: '. $deposito->__toString() . "<br>";
                                }
                            }
                        }
                        if(!$banderaDepositos){
                            echo "<br>El usuario no tiene depositos";
                        }
                    }else {
                        echo "<br>No hay depositos";
                    }
                } else {
                    echo "<br>No existe ese usuario";
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
        if (!isset($_GET["moneda"]) /*|| empty($_GET["moneda"]) || trim($_GET["moneda"]) === ""*/) {
            echo "<br>faltan parametros.";
        }else {
            if(Cuenta::ValidarMoneda($_GET["moneda"])){
                $moneda = $_GET["moneda"];
            }else{
                echo "tipo de moneda incorrecto";
            }
            
            if($moneda !== null) {
                // echo $moneda;
                $depositosMoneda = Deposito::ObtenerDepositosPorMoneda($moneda,'./ArchivosJson/depositos.json');
                
                if($depositosMoneda !== null && !empty($depositosMoneda)) {
                    // echo "entra";
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
            
            // var_dump($cuentasJson);

            if($cuentasJson !== null && !empty($cuentasJson)){
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
            }
        }
    }

    //funcion que me retorne el array de los dias que hay entre dos fechas para despues comparar si los depositos->getFecha matchea con alguno del array
    
?>