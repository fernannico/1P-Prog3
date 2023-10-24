<?php
    include_once "./Clases/Ajuste.php";
    include_once "./Clases/Retiro.php";
    include_once "./Clases/Cuenta.php";
    include_once "./Clases/Deposito.php";
    $rutaBancoJson = './ArchivosJson/banco.json';
    $rutaDespositosJson = './ArchivosJson/depositos.json';
    $rutaRetirosJson = './ArchivosJson/retiro.json';
    $rutaAjustesJson = './ArchivosJson/ajustes.json';
    
    $tipoOperacion = null;
    $idOperacion = null;
    $monto = null;
    $motivo = null;
    if(!isset($_POST["IdOperacion"]) || !isset($_POST["monto"]) || !isset($_POST["motivo"])){
        echo "faltan parametros";
    }else{
        #validaciones param
        if($_POST["monto"] <= 0) {
            echo "<br>Ingresar un monto positivo y mayor a 0";
        }else{
            $monto = $_POST["monto"];
        }
        if($_POST["motivo"] !== "saldo positivo" && $_POST["motivo"] !== "saldo negativo"){
            echo "<br>Motivo incorrecto, indicar si es: 'saldo positivo' o 'saldo negativo'";
        }else{
            $motivo = $_POST["motivo"];
        }

        $idOperacion = $_POST["IdOperacion"];
        
        $depositoAjustable = Deposito::ObtenerDepositoPorId($idOperacion,$rutaDespositosJson);
        $retiroAjustable = Retiro::ObtenerRetiroPorId($idOperacion,$rutaRetirosJson);

        if($depositoAjustable !== null){
            $tipoOperacion = "deposito";
        }else if($retiroAjustable !== null){
            $tipoOperacion = "retiro";
        }else{
            echo "<BR>con ese ID no hay ni deposito ni retiro";
        }

        //verificar si la operacion ya fue ajustada
        $banderaYaAjustado = false;
        $ajustes = Ajuste::JsonDeserialize($rutaAjustesJson);
        foreach($ajustes as $ajuste){
            if($depositoAjustable !== null && $ajuste->GetIdOperacion() == $depositoAjustable->GetId()){
                $banderaYaAjustado = true;
                break;
            }elseif($retiroAjustable !== null && $ajuste->GetIdOperacion() == $retiroAjustable->GetId()){
                $banderaYaAjustado = true;
                break;
            }
        }

        #logica
        if(!$banderaYaAjustado){
        if($tipoOperacion !== null && $idOperacion !== null && $monto !== null && $motivo !== null){
            if($tipoOperacion == "deposito"){
                // echo "entra D";
                if($depositoAjustable !== null){
                    echo "<br>DEPOSITO:<br>".$depositoAjustable->__toString();
                    if($motivo == 'saldo positivo'){
                        // ej Si el deposito se registró como de 5000 pero realmente deposité 10k
                        // ajusteDeposito-> depositar en la cuenta de 0 a infinito
                        if(Ajuste::AjustarSaldo($depositoAjustable,$monto,$motivo,$rutaBancoJson)) {
                            echo "<br>ajuste guardado";
                        }else{
                            echo "<br>ajuste no guardado";
                        }
                    }else if($motivo == 'saldo negativo'){
                        //		Si el deposito se registró como que fue de 10000 pero realmente eran 1000: ajusteDeposito-> extraer de la cuenta de 0 al balance de la cta
                        $cuentaJson = Cuenta::ObtenerCuentaPorNroCuenta($depositoAjustable->GetNroCuenta(),$rutaBancoJson);
                        if($cuentaJson !== null && $monto > $cuentaJson->GetSaldo()){
                            echo '<br><br>El ajuste negativo no puede ser mayor al balance de la cuenta: <br>balance actual: $'. $cuentaJson->GetSaldo();
                        }else{
                            $monto = $monto * -1;
                            if(Ajuste::AjustarSaldo($depositoAjustable,$monto,$motivo,$rutaBancoJson)) {
                                echo "<br>ajuste guardado";
                            }else{
                                echo "<br>ajuste no guardado";
                            }
                        }
                    }else{
                        echo "<br>error de sistema";
                    }
                }else{
                    echo "<br>No hay un deposito con ese Id";
                }
            }elseif($tipoOperacion == "retiro"){
                // echo "entra R";
                if($retiroAjustable !== null){
                    echo "<br>RETIRO:<br>".$retiroAjustable->__toString();
                    if($motivo == 'saldo positivo'){
                        if($monto > $retiroAjustable->GetImporteRetirado()){
                            echo '<br>El importe retirado en el retiro ID:'.$retiroAjustable->GetId()."fue de $".$retiroAjustable->GetImporteRetirado().", el ajuste positivo tiene que ser un monto de 0 a " . $retiroAjustable->GetImporteRetirado();
                        }else{
                            // ej si se registró un retiro de 5000 pero no salio nada o realmente dio 4k , pero no mas xq sino seria negativo
                            if(Ajuste::AjustarSaldo($retiroAjustable,$monto,$motivo,$rutaBancoJson)) {
                                echo "<br>ajuste guardado";
                            }else{
                                echo "<br>ajuste no guardado";
                            }
                        }
                    }else if($motivo == 'saldo negativo'){
                        //ej si el retiro se registró como que fue de 500 pero realmente fueron 5000: retirar de la cuenta de 0 al balance (no podria haber retirado mas de lo que tenia en la cuenta)
                        $cuentaJson = Cuenta::ObtenerCuentaPorNroCuenta($retiroAjustable->GetNroCuenta(),$rutaBancoJson);
                        if($cuentaJson !== null && $monto > $cuentaJson->GetSaldo()){
                            echo '<br><br>El ajuste negativo no puede ser mayor al balance de la cuenta: <br>balance actual: $'. $cuentaJson->GetSaldo();
                        }else{
                            $monto = $monto * -1;
                            if(Ajuste::AjustarSaldo($retiroAjustable,$monto,$motivo,$rutaBancoJson)) {
                                echo "<br>ajuste guardado";
                            }else{
                                echo "<br>ajuste no guardado";
                            }
                        }
                    }else{
                        echo "<br>error de sistema";
                    }
                }else{
                    echo "<br>No hay un retiro con ese Id";
                }            
            }
        }
        }else{
            echo "<BR>La operacion ya fue ajustada";
        }
    }
?>