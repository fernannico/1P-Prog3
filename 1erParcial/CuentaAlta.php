<?php
    include_once "Cuenta.php";

    // echo "alta cuenta";
    /* B- CuentaAlta.php: (por POST) se ingresa Nombre y Apellido, Tipo Documento, Nro. Documento, Email, Tipo de Cuenta (CA – caja de ahorro o CC – cuenta corriente), Moneda ($ o U$S), Saldo Inicial (0 por defecto).
    // Se guardan los datos en el archivo banco.json, tomando un id autoincremental de 6 dígitos como Nro. de Cuenta (emulado). */

    //Sí el nombre y tipo ya existen , se actualiza el precio y se suma al stock existente.
    // completar el alta con imagen/foto del usuario/cliente, guardando la imagen con Nro y Tipo de Cuenta (ej.: NNNNNNTT) como identificación en la carpeta: /ImagenesDeCuentas/2023.

    if(Cuenta::ValidarParametrosPost()){
        $tipoCuenta= null;
        $moneda = null;
        $nroCuentaNueva = null;
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
            if(isset($_POST["saldoInicial"])){
                $saldoInicial = $_POST["saldoInicial"];
            }else{
                $saldoInicial = 0;
            }
            if(isset($_POST['nroCuenta'])){
                if(!Cuenta::ValidarNroCuentaEnJson($_POST['nroCuenta'], 'banco.json')){
                    $nroCuentaNueva = $_POST['nroCuenta'];
                }else{
                    echo "<br>El nro de cuenta ya existe";
                }
            }else{
                do{
                    $nroCuentaNueva = rand(100000, 999999);
                } while (Cuenta::ValidarNroCuentaEnJson($nroCuentaNueva, 'banco.json'));
                
            }
            if($nroCuentaNueva !== null){
                $cuentaNueva = new Cuenta($_POST["nombre"], $_POST["apellido"], $_POST["tipoDocumento"], $_POST["nroDocumento"], 
                                    $_POST["email"], $_POST["tipoCuenta"], $_POST["moneda"], $saldoInicial,$nroCuentaNueva,"activa");
                                    $cuentaJson = Cuenta::ValidarUsuarioYTipoEnJson($cuentaNueva->GetNombre(),$cuentaNueva->GetApellido(),$cuentaNueva->GetNroDocumento(),$cuentaNueva->GetTipoCuenta(),$cuentaNueva->GetMoneda(),"banco.json");
            

            

                if($cuentaJson !== null){
                    echo "existe la cuenta <br>" /*. $cuentaJson->__toString()*/;
                    if($cuentaJson->ActualizarSaldoCuentaJson($saldoInicial,'banco.json')){
                        echo "<br>Saldo Actualizando "/*. $cuentaJson->__toString()*/;
                    }            
                }else if(Cuenta::GuardarCuentaJSON($cuentaNueva,'banco.json')){
                    echo "Cuenta guardada en json <br> " . $cuentaNueva->__toString();

                    if($cuentaNueva->GuardarImagen()){
                        echo "<br>El archivo ha sido cargado correctamente.<br>";
                    }else{
                        echo "<br>Ocurrió algún error al subir el fichero. No pudo guardarse.<br>";
                    }

                }else{
                    echo "Error de sistema";
                }
            }
        }
    }else{
        echo "faltan parametros";
    }


?>