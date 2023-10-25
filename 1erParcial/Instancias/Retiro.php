<?php

    // include_once("Cuenta.php");

    class Retiro implements JsonSerializable{
        private $_IdRetiro;
        private $_fecha;
        private $_nroCuenta;
        private $_tipoCuenta;
        private $_moneda;
        private $_saldo;
        private $_importeRetirado;
    
        public function __construct($IdRetiro,$nroCuenta,$tipoCuenta,$moneda,$saldo,$importeRetirado){
            $this->_IdRetiro = $IdRetiro;
            $this->_fecha = date("d-m-Y");
            $this->_nroCuenta = $nroCuenta;
            $this->_tipoCuenta = $tipoCuenta;
            $this->_moneda = $moneda;
            $this->_saldo = $saldo;
            $this->_importeRetirado = $importeRetirado;
        }
        public function __toString(){
            return 
            "id Retiro: " . $this->_IdRetiro .
            "<br>fecha: " . $this->_fecha.
            "<br>nro Cuenta: " . $this->_nroCuenta.
            "<br>tipo Cuenta: ". $this->_tipoCuenta.
            "<br>moneda: ". $this->_moneda.
            "<br>importe Retirado: ". $this->_importeRetirado.
            "<br>saldo: ". $this->_saldo;
        }

        public function GetId() {
            return $this->_IdRetiro;
        }
        public function GetFecha() {
            return $this->_fecha;
        } 
        public function GetNroCuenta() {
            return $this->_nroCuenta;
        }
        public function GetTipoCuenta () {
            return $this->_tipoCuenta;
        } 
        public function GetMoneda () {
            return $this->_moneda;
        } 
        public function GetImporteRetirado() {
            return $this->_importeRetirado;
        } 
        public function GetSaldo () {
            return $this->_saldo;
        }
        private function SetFecha($fecha) {
            $this->_fecha = $fecha;
        }
        public function SetSaldo($cantidad) {
            $this->_saldo = $cantidad;
        }
        public function jsonSerialize(){
            return [
                'id' => $this->GetId(),
                'fecha' => $this->GetFecha(),
                'nroCuenta' => $this->GetNroCuenta(),
                'tipoCuenta' => $this->GetTipoCuenta(),
                'moneda' => $this->GetMoneda(),
                'importeRetirado' => $this->GetImporteRetirado(),
                'saldo' => $this->GetSaldo()
            ];
        }
        public static function JsonDeserialize($rutaJson) {
            $arrayRetiros = Array();
            if(file_exists($rutaJson)){
                $jsonString = file_get_contents($rutaJson);
                $retiros = json_decode($jsonString, true);
    
                if (is_array($retiros)) {
                    foreach ($retiros as $item) {
                        $retiro = new Retiro($item['id'], $item['nroCuenta'], $item['tipoCuenta'], $item['moneda'],  $item['saldo'], $item['importeRetirado']);
                        // $retiro->SetSaldo($item['saldo']);
                        $retiro->SetFecha($item['fecha']);
                        $arrayRetiros[] = $retiro;
                    }
                }
            }
            return $arrayRetiros;
        }
        
        public static function GuardarRetiroJSON($retiro,$rutaArchivoJson)
        {
            $retorno = false;
            $retiroInd = $retiro->JsonSerialize();
    
            $retiros = [];
            if (file_exists($rutaArchivoJson)) {
                $retiros = json_decode(file_get_contents($rutaArchivoJson), true);
            }
    
            $retiros[] = $retiroInd;
    
            if(file_put_contents($rutaArchivoJson, json_encode($retiros, JSON_PRETTY_PRINT))){
                $retorno = true;
            }
    
            return $retorno;
        }

        public static function ObtenerRetiroPorId($idRetiro,$rutaArchivoJson) {
            $retiroJson = null;

            $retirosJson = Retiro::JsonDeserialize($rutaArchivoJson);
            if($retirosJson !== null && !empty($retirosJson)){
                foreach($retirosJson as $retiro){
                    if($idRetiro == $retiro->GetId()){
                        $retiroJson = $retiro;
                        break;
                    }
                }
            }        
            return $retiroJson;
        }
    }

?>