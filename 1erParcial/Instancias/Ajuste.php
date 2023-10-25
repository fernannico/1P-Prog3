<?php

    class Ajuste implements JsonSerializable{
        private $_IdAjuste;
        private $_IdCuenta;
        private $_IdOperacion;
        private $_monto;
        private $_motivo;

        public function __construct($idAjuste,$idOperacion,$IdCuenta="",$monto, $motivo){
            $this->_IdAjuste = $idAjuste;
            $this->_IdCuenta = $IdCuenta;
            $this->_IdOperacion = $idOperacion;
            $this->_monto = $monto;
            $this->_motivo = $motivo;
        }

        public function GetId() {
            return $this->_IdAjuste;
        }
        public function GetIdCuenta() {
            return $this->_IdCuenta;
        } 
        public function GetIdOperacion() {
            return $this->_IdOperacion;
        }
        public function GetMonto() {
            return $this->_monto;
        } 
        public function GetMotivo() {
            return $this->_motivo;
        } 

        public function __toString(){
            return   
            'ID: ' . $this->GetId() .
            '<br>Id cuenta: ' . $this->GetIdCuenta() .
            '<br>Id operacion: ' . $this->GetIdOperacion() .
            '<br>Monto: ' . $this->GetMonto() .
            '<br>Motivo: ' . $this->GetMotivo();
        }

        public function jsonSerialize(){
            return [
                'id' => $this->GetId(),
                'idCuenta' => $this->GetIdCuenta(),
                'idOperacion' => $this->GetIdOperacion(),
                'monto' => $this->GetMonto(),
                'motivo' => $this->GetMotivo()
            ];
        }
        public static function JsonDeserialize($rutaJson) {
            $arrayAjustes = Array();
            if(file_exists($rutaJson)){
                $jsonString = file_get_contents($rutaJson);
                $ajustes = json_decode($jsonString, true);
    
                if (is_array($ajustes)) {
                    foreach ($ajustes as $item) {
                        $ajuste = new Ajuste($item['id'], $item['idOperacion'], $item['idCuenta'], $item['monto'], $item['motivo']);
                        $arrayAjustes[] = $ajuste;
                    }
                }
            }
    
            return $arrayAjustes;
        }
        public static function GuardarAjusteJSON($ajuste,$rutaArchivoJson)
        {
            $retorno = false;
            $ajusteInd = $ajuste->JsonSerialize();
    
            $ajustes = [];
            if (file_exists($rutaArchivoJson)) {
                $ajustes = json_decode(file_get_contents($rutaArchivoJson), true);
            }
    
            $ajustes[] = $ajusteInd;
    
            if(file_put_contents($rutaArchivoJson, json_encode($ajustes, JSON_PRETTY_PRINT))){
                $retorno = true;
            }
    
            return $retorno;
        }

        public static function AjustarSaldo($operacionAjustable,$monto,$motivo,$rutaBancoJson){
            $retorno = false;
            $cuentaJson = Cuenta::ObtenerCuentaPorNroCuenta($operacionAjustable->GetNroCuenta(),$rutaBancoJson);
            if($cuentaJson !== null){
                echo "<br><br>CUENTA:<br>". $cuentaJson->__toString();
                if($cuentaJson->ActualizarSaldoCuentaJson($monto,$rutaBancoJson)){
                    echo "<br><br>AJUSTE REALIZADO<br>saldo de la cuenta " .$cuentaJson->GetNroCuenta(). " actualizado";
                    $nuevoAjuste = new Ajuste(rand(1,10000),$operacionAjustable->GetId(),$cuentaJson->GetNroCuenta(),$monto, $motivo);
                    if(Ajuste::GuardarAjusteJSON($nuevoAjuste,'./ArchivosJson/ajustes.json')){
                        echo "<br><br> AJUSTE:<br>" . $nuevoAjuste->__toString();
                        $retorno = true;
                    }
                }else{
                    echo "<br><br>AJUSTE NO REALIZADO<br>";
                }
            }else{
                echo "<br>No hay una cuenta con ese Nro de cuenta";
            }
            return $retorno;
        }
    }

?>