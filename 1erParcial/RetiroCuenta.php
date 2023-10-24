<?php
    echo "RETIRO CUENTA";
    include_once("Cuenta.php");
    include_once("Deposito.php");
    include_once("Retiro.php");
    
    //6- RetiroCuenta.php: (por POST) se recibe el Tipo de Cuenta, Nro de Cuenta y Moneda; ademas el importe a retirar
        //Si la cuenta existe en banco.json, se decrementa el saldo existente según el importe extraído y se registra en el archivo retiro.json la operación con los datos de la cuenta y el retiro (fecha, monto) e id autoincremental.
        // Si la cuenta no existe o el saldo es inferior al monto a retirar, informar el tipo de error.

    if(!isset($_POST["tipoCuenta"]) || !isset($_POST["nroCuenta"]) || !isset($_POST["moneda"]) || !isset($_POST["importe"])){
        echo "<br>faltan parametros";
    }else{
        $tipoCuenta = null;
        $moneda = null;
        $importeRetiro = null;
        $nroCuenta = $_POST["nroCuenta"];

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
        if($_POST["importe"] >= 0){
            $importeRetiro = $_POST["importe"];
        }else{
            echo "<br>se ingresó un importe negativo";
        }
    
        if($moneda !== null && $tipoCuenta !== null && $importeRetiro !== null){
            // echo 'entra' . $moneda . $tipoCuenta;
            $cuentaJson = null;

            $cuentaJson = Cuenta::ValidarCuentaEnJson($moneda,$tipoCuenta,$nroCuenta,'banco.json');
            if($cuentaJson !== null){
                echo "<br>CUENTA:<br>" . $cuentaJson->__toString() . "<br>". "<br>";
                if($cuentaJson->GetSaldo() >= $importeRetiro){
                    // echo "<br>importe es menor";
                    $importeRetiro = $importeRetiro * -1;
                    if($cuentaJson->ActualizarSaldoCuentaJson($importeRetiro,"banco.json")){
                        // echo "<br>actualiza";
                        $cuentaJsonActualizada = Cuenta::ValidarCuentaEnJson($moneda,$tipoCuenta,$nroCuenta,"banco.json");
                        // var_dump($cuentaJson->GetSaldo());
                        if($cuentaJsonActualizada !== null){
                            echo "<br>RETIRADO!<br>Cuenta tras retiro:<br>" . $cuentaJsonActualizada->__toString();

                            $retiroNuevo = new Retiro(rand(1,10000),$cuentaJsonActualizada->GetNroCuenta(),$cuentaJsonActualizada->GetTipoCuenta(),$cuentaJsonActualizada->GetMoneda(),$cuentaJsonActualizada->GetSaldo(),$_POST["importe"]);

                            echo "<br><br>DATOS DEL RETIRO:<br>" . $retiroNuevo->__toString();

                            if(Retiro::GuardarRetiroJSON($retiroNuevo,'retiro.json')){
                                echo "<br><br>Gracias vuelva pronto";
                            }else{
                                echo "retiro NO guardado en json";

                            }
                        }
                    }
                }else{
                    echo "<br>". "No hay suficiente saldo para retirar ese monto" . "<br>";
                }
            }else{
                echo "<br> NO existe la cuenta con dichas caracteristicas";
            }
        }      
    }
?>