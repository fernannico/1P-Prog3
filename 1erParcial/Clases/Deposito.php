<?php

    class Deposito implements JsonSerializable{
        private $_id;
        private $_fecha;
        private $_nroCuenta;
        private $_tipoCuenta;
        private $_moneda;
        private $_deposito;
        private $_saldo;

        public function __construct($id,$nroCuenta,$tipoCuenta,$moneda,$deposito){
            $this->_id = $id;
            $this->_fecha = date('d-m-Y');
            $this->_nroCuenta = $nroCuenta;
            $this->_tipoCuenta = $tipoCuenta;
            $this->_moneda = $moneda;
            $this->_deposito = $deposito;
        }
        public function __toString(){
            return 
                "id: " . $this->_id .
                "<br>fecha: " . $this->_fecha .
                "<br>nroCuenta: " . $this->_nroCuenta .
                "<br>tipoCuenta: " . $this->_tipoCuenta .
                "<br>moneda: " . $this->_moneda .
                "<br>deposito: " . $this->_deposito .
                "<br>saldo: " . $this->_saldo;
        }
        public function GetId() {
            return $this->_id;
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
        public function GetDeposito() {
            return $this->_deposito;
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

        //se registra en el archivo depósitos.json la operación con los datos de la cuenta y el depósito (fecha, monto) e id autoincremental)
        public function jsonSerialize(){
            return [
                'id' => $this->GetId(),
                'fecha' => $this->GetFecha(),
                'nroCuenta' => $this->GetNroCuenta(),
                'tipoCuenta' => $this->GetTipoCuenta(),
                'moneda' => $this->GetMoneda(),
                'deposito' => $this->GetDeposito(),
                'saldo' => $this->GetSaldo()
            ];
        }
        public static function JsonDeserialize($rutaJson) {
            $arrayDepositos = Array();
            if(file_exists($rutaJson)){
                $jsonString = file_get_contents($rutaJson);
                $depositos = json_decode($jsonString, true);
    
                if (is_array($depositos)) {
                    foreach ($depositos as $item) {
                        $deposito = new deposito($item['id'], $item['nroCuenta'], $item['tipoCuenta'], $item['moneda'], $item['deposito']);
                        $deposito->SetSaldo($item['saldo']);
                        $deposito->SetFecha($item['fecha']);
                        $arrayDepositos[] = $deposito;
                    }
                }
            }
    
            return $arrayDepositos;
        }
        public static function GuardarDepositoJSON($deposito,$rutaArchivoJson)
        {
            $retorno = false;
            $depositoInd = $deposito->JsonSerialize();
    
            $depositos = [];
            if (file_exists($rutaArchivoJson)) {
                $depositos = json_decode(file_get_contents($rutaArchivoJson), true);
            }
    
            $depositos[] = $depositoInd;
    
            if(file_put_contents($rutaArchivoJson, json_encode($depositos, JSON_PRETTY_PRINT))){
                $retorno = true;
            }
    
            return $retorno;
        }

        public function GuardarImagen($nombreImagen) {
            $retorno = false;
            $carpeta_archivos = "ImagenesDeDepositos2023/";
            $nombre_archivo = $this->GetTipoCuenta() . "_" . $this->GetNroCuenta() . "_" . $this->GetId() . ".jpg";       
            $ruta_destino = $carpeta_archivos . $nombre_archivo;
    
            if (move_uploaded_file($nombreImagen,  $ruta_destino)){
                $retorno = true;
            }     
            return $retorno;
        }
        
        public static function ObtenerDepositoPorId($idDeposito,$rutaArchivoJson) {
            $depositoJson = null;
            
            $depositosJson = Deposito::JsonDeserialize($rutaArchivoJson);
            if($depositosJson !== null && !empty($depositosJson)){
                foreach($depositosJson as $deposito){
                    if($idDeposito == $deposito->GetId()){
                        $depositoJson = $deposito;
                        break;
                    }
                }
            }        
            return $depositoJson;
        } 
        public static function ObtenerConjuntoFechas($fechaInicio, $fechaFin) {
            $fechas = Array();
        
            $fechaActual = strtotime($fechaInicio);
        
            while ($fechaActual <= strtotime($fechaFin)) {
                $fechas[] = date("d-m-Y", $fechaActual);
                $fechaActual = strtotime("+1 day", $fechaActual);
            }
        
            return $fechas;
        }
    
        public static function ObtenerDepositosEntreFechas($fechaInicio, $fechaFin,$rutaArchivoJson) {
            $depositosEntreFechas = Array();
            $fechas = Deposito::ObtenerConjuntoFechas($fechaInicio,$fechaFin);
            $depositosJson = Deposito::JsonDeserialize($rutaArchivoJson);
    
            if($fechas !== null && $depositosJson !== null && !empty($depositosJson)){
                foreach($fechas as $fecha){
                    foreach($depositosJson as $deposito){
                        if($deposito->GetFecha() == $fecha){
                            $depositosEntreFechas[] = $deposito;
                        }
                    }
                }
            }
    
            return $depositosEntreFechas;
        }
        public static function ObtenerDepositosPorTipoCuenta($tipoCuenta, $rutaArchivoJson) {
            $depositos = Array();
            $depositosJson = Deposito::JsonDeserialize($rutaArchivoJson);
            
            foreach($depositosJson as $deposito){
                // echo $deposito->__toString()."<br>";
                if($deposito->GetTipoCuenta() == $tipoCuenta) {
                    $depositos[] = $deposito;
                }
            }
    
            return $depositos;
        }
        public static function ObtenerDepositosPorMoneda($moneda, $rutaArchivoJson){
            $depositos = Array();
            $depositosJson = Deposito::JsonDeserialize($rutaArchivoJson);
            
            foreach($depositosJson as $deposito){
                if($deposito->GetMoneda() == $moneda) {
                    $depositos[] = $deposito;
                }
            }
    
            return $depositos;
        }

        public static function CompararPorNumeroDeCuenta($a, $b){
            return $a->GetNroCuenta() > $b->GetNroCuenta();
        }
        public static function OrdenarDepositosPorNumeroCuenta($depositos){
            usort($depositos, 'Deposito::CompararPorNumeroDeCuenta');
            return $depositos;
        }
    }

?>