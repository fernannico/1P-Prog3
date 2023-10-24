<?php
    include_once "./Clases/Cuenta.php";
    $rutaBancoJson = './ArchivosJson/banco.json';

    echo "<br>BORRAR CUENTA<br>";

    // 9- BorrarCuenta.php (por DELETE), debe recibir el tipo y número de cuenta y debe realizar la baja de la cuenta (soft-delete, no físicamente) y la foto relacionada a esa venta debe moverse a la carpeta /ImagenesBackupCuentas/2023.
    
    parse_str(file_get_contents("php://input"), $putData);
    
    $nroCuenta = null;
    $tipoCuenta = null;

    if(!isset($putData["nroCuenta"]) || !isset($putData["tipoCuenta"])){
        echo "faltan parametros";
    }else{
        // echo "entra";
        
        if(Cuenta::ValidarTipoCuenta($putData["tipoCuenta"])){
            $tipoCuenta = $putData["tipoCuenta"];
        }else{
            echo "tipo de cuenta incorrecto";
        }
        $nroCuenta = $putData["nroCuenta"];

        $cuentaJson = Cuenta::ObtenerCuentaPorNroCuenta($nroCuenta,$rutaBancoJson);
        if($cuentaJson !== null){
            $cuentaJsonTipo = Cuenta::ValidarCuentaEnJson($cuentaJson->GetMoneda(),$tipoCuenta,$nroCuenta,$rutaBancoJson);
            // $cuentaJson->__toString();
            // var_dump($cuentaJson);
            if($cuentaJsonTipo !== null){
                $cuentaJsonTipo->ModificarEstadoCuentaJson("inactivo",$rutaBancoJson);
                $cuentaJsonActualizada = Cuenta::ValidarCuentaEnJson($cuentaJsonTipo->GetMoneda(),$cuentaJsonTipo->GetTipoCuenta(), $cuentaJsonTipo->GetNroCuenta(),$rutaBancoJson);
                if($cuentaJsonActualizada !== null){
                    // echo 'entra';
                    echo $cuentaJsonActualizada->__toString();
                    // var_dump($cuentaJsonActualizada);
                    $nombreArchivo = $cuentaJsonActualizada->GetNroCuenta() . $cuentaJsonActualizada->GetTipoCuenta() . ".jpg";;
                    $carpetaOrigen = "ImagenesDeCuentas/2023/";
                    $carpetaDestino = "ImagenesBackupCuentas/2023/";
                    // $ruta_destino = $carpeta_archivos . $nombre_archivo;
                    if(Cuenta::MoverImagen($nombreArchivo,$carpetaOrigen,$carpetaDestino)) {
                        echo "<br>Imagen movida a la carpeta " .$carpetaDestino ;
                    }else{
                        echo "<br>imagen no movida";
                    }
                }
            }else{
                echo 'La cuenta existe pero no es de ese tipo';
            }
        }else{
            echo "<br>No existe esa cuenta";
        }

    }

?>