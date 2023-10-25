<?php
    include_once "./Instancias/Cuenta.php";
    $rutaBancoJson = './ArchivosJson/banco.json';

    echo "CONSULTAR CUENTA: <BR>";

    // ConsultarCuenta.php: (por POST) Se ingresa Tipo y Nro. de Cuenta, si coincide con
    // algún registro del archivo banco.json, retornar la moneda/s y saldo de la cuenta/s. 

    // De lo contrario informar si no existe la combinación de nro y tipo de cuenta o, si existe el
    // número y no el tipo para dicho número, el mensaje: “tipo de cuenta incorrecto”.

    if(isset($_POST["tipoCuenta"]) && isset($_POST["nroCuenta"])){
        $nroCuenta = $_POST["nroCuenta"];
        $tipoCuenta= null;

        if(Cuenta::ValidarTipoCuenta($_POST["tipoCuenta"])){
            $tipoCuenta = $_POST["tipoCuenta"];
        }else{
            echo "tipo de cuenta incorrecto";
        }

        if($tipoCuenta !== null){
            // echo 'entro';

            $cuentaJson = Cuenta::ConsultarCuenta($tipoCuenta,$nroCuenta,$rutaBancoJson);
            if($cuentaJson !== null){
                echo "<br>Moneda: " . $cuentaJson->GetMoneda();
                echo "<br>Saldo: " . $cuentaJson->GetSaldo();
                // echo "<br>" . $cuentaJson->__toString();
            }else{
                $banderaCuenta = false;
                $cuentasCargadas = Cuenta::JsonDeserialize($rutaBancoJson);

                foreach($cuentasCargadas as $cuentaCargada){
        
                    if($cuentaCargada->GetNroCuenta()== $nroCuenta){
                        $banderaCuenta = true;
                        echo "<br>Existe el nro de cuenta: " . $cuentaCargada->GetNroCuenta();
                        if($cuentaCargada->GetTipoCuenta() !== $tipoCuenta){
                            echo '<br>el tipo de cuenta es incorrecto';
                        }
                    }
                }
                if($banderaCuenta == false){
                    echo "<br>no existe la cuenta";
                }    
            }
        }
    }else{
        echo "<br>Faltan parametros";
    }
?>