<?php
    include_once "./Instancias/Cuenta.php";
    $rutaBancoJson = './ArchivosJson/banco.json';
    // 5- ModificarCuenta.php (por PUT)
    // Debe recibir todos los datos propios de una cuenta (a excepción del saldo); si dicha cuenta existe (comparar por Tipo y Nro. de Cuenta) se modifica, de lo contrario informar que no existe esa cuenta.
    
    parse_str(file_get_contents("php://input"), $putData);

    $nroCuenta = null;
    $nombre = null;
    $apellido = null;
    $nroDocumento = null;
    $mail = null;
    $tipoCuenta = null;

    if(!isset($putData["nroCuenta"]) || !isset($putData["nombre"]) || !isset($putData["apellido"]) || !isset($putData["nroDocumento"]) || !isset($putData["mail"]) || !isset($putData["tipoCuenta"])){
        echo "<br>Faltan parametros";
    }else{
        $nroCuenta = $putData["nroCuenta"];
        $nombre =$putData["nombre"];
        $apellido =$putData["apellido"];
        $nroDocumento =$putData["nroDocumento"];
        $mail = $putData["mail"];
        
        if(Cuenta::ValidarTipoCuenta($putData["tipoCuenta"])){
            $tipoCuenta = $putData["tipoCuenta"];
        }else{
            echo "tipo de cuenta incorrecto";
        }

        $cuentaPorId = Cuenta::ObtenerCuentaPorNroCuenta($nroCuenta,$rutaBancoJson);
        if($cuentaPorId == null){
            echo "<br>No existe esa cuenta con ese Id";
        }elseif($cuentaPorId->GetTipoCuenta() !== $tipoCuenta){
            echo "<br>La cuenta nro " . $cuentaPorId->GetNroCuenta() . " no tiene ese tipo de cuenta";
            $cuentaPorId = null;
        }elseif($cuentaPorId !== null){
            echo "<br>Cuenta a modificar:<br>" . $cuentaPorId->__toString() ."<br>";
            $moneda = $cuentaPorId->GetMoneda();
            if($cuentaPorId->ModificarCuentaJson($nombre,$apellido,$nroDocumento,$mail,$rutaBancoJson)){
                echo "<br>Cuenta modificada en json";
                $cuentaJsonActualizada = Cuenta::ValidarCuentaEnJson($moneda,$tipoCuenta, $nroCuenta,$rutaBancoJson);
                if($cuentaJsonActualizada !== null)
                echo "<br>Cuenta modificada: <br>" . $cuentaJsonActualizada->__toString();
            }else{
                echo "<br>Cuenta no modificada en json";
            }
        }
    }

?>
