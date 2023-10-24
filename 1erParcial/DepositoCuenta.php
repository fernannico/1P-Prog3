<?php
    include_once "Cuenta.php";
    include_once "Deposito.php";
    echo "DEPOSITO CUENTA: <BR>";

    // 3-
    // a- DepositoCuenta.php: (por POST) se recibe el Tipo de Cuenta, Nro de Cuenta y Moneda y el importe a depositar, si la cuenta existe en banco.json, se incrementa el saldo existente según el importe depositado y se registra en el archivo depósitos.json la operación con los datos de la cuenta y el depósito (fecha, monto) e id autoincremental). 
    //Si la cuenta no existe, informar el error.

    // b- Completar el depósito con imagen del talón de depósito con el nombre: Tipo de Cuenta, Nro. de Cuenta e Id de Depósito guardando la imagen en la carpeta /ImagenesDeDepositos2023.

    if(isset($_POST["nroCuenta"]) && isset($_POST["tipoCuenta"]) && isset($_POST["importe"]) && isset($_POST["moneda"]) && isset($_FILES["imagen"])) {
        $nroCuenta = $_POST["nroCuenta"];
        $importe = $_POST["importe"];
        $tipoCuenta= null;
        $moneda = null;

        if(Cuenta::ValidarTipoCuenta($_POST["tipoCuenta"])){
            $tipoCuenta = $_POST["tipoCuenta"];
        }else{
            echo "tipo de cuenta incorrecto";
        }
        if(Cuenta::ValidarMoneda($_POST["moneda"])){
            $moneda = $_POST["moneda"];
        }else{
            echo "moneda ingresada incorrecta";
        }

        if($moneda !== null && $tipoCuenta !== null){
            $cuentaJson = Cuenta::ValidarCuentaEnJson($moneda,$tipoCuenta, $nroCuenta,"banco.json");
            if($cuentaJson !== null) {
                if($cuentaJson->ActualizarSaldoCuentaJson($importe,"banco.json")){
                    $cuentaJsonActualizada = Cuenta::ValidarCuentaEnJson($moneda,$tipoCuenta, $nroCuenta,"banco.json");
                    // var_dump($cuentaJson->GetSaldo());
                    if($cuentaJsonActualizada !== null) {
                        $depositoNuevo = new Deposito(rand(1,10000),$nroCuenta,$tipoCuenta,$moneda,$importe);
                        $depositoNuevo->SetSaldo($cuentaJsonActualizada->GetSaldo());
                        if(Deposito::GuardarDepositoJSON($depositoNuevo,"depositos.json") && $depositoNuevo->GuardarImagen($_FILES['imagen']['tmp_name'])) {
                            echo "<br>Deposito realizado<br>";
                            echo "<br>Deposito<br>" . $depositoNuevo->__toString();
                        }else{
                            echo "falta imagen";
                        }
                    }
                }
            }else{
                echo "<br>No existe la cuenta";
            }
        }
    }else{
        echo "Faltan parametros<br>";
    }



?>