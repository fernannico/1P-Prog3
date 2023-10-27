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

        public static function CalcularRetirosPorTipoYFecha($tipoCuenta,$moneda,$fecha,$rutaArchivoJson) {

            $totalRetirado= 0;
            $RetirosJson = Retiro::JsonDeserialize($rutaArchivoJson);
    
            foreach($RetirosJson as $Retiro){
    
                if($Retiro->GetTipoCuenta() == $tipoCuenta && $Retiro->GetMoneda() == $moneda && $Retiro->GetFecha() == $fecha) {
                    echo "<BR>ID: " . $Retiro->GetId() . " - Retiro:" . $Retiro->GetImporteRetirado() . " - Fecha:" . $Retiro->GetFecha();
                    $totalRetirado += $Retiro->GetImporteRetirado();
                }
            }
            return $totalRetirado;
        }

        public static function ObtenerRetirosPorDni($nroDocumento,$rutaCuentasJson,$rutaRetirosJson){
            $Retiros = Array();
            $cuentasUsuario = Cuenta::ObtenerCuentasPorDni($nroDocumento,$rutaCuentasJson);
            $RetirosJson = Retiro::JsonDeserialize($rutaRetirosJson);
    
            if($cuentasUsuario !== null && !empty($cuentasUsuario)) {
                if($RetirosJson !== null && !empty($RetirosJson)) {
                    // $banderaRetiros = false;
                    foreach($cuentasUsuario as $cuenta){         //hasta aca tengo las cuentas, pero necesito los Retiros
                        foreach($RetirosJson as $Retiro){
                            if($cuenta->GetNroCuenta() == $Retiro->GetNroCuenta()) {
                                $Retiros[] = $Retiro;
                            }
                        }
                    }
                }else {
                    echo "<br>No hay Retiros";
                }
            } else {
                echo "<br>No existe ese usuario";
            }
            return $Retiros;
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
    
        public static function ObtenerRetirosEntreFechas($fechaInicio, $fechaFin,$rutaArchivoJson) {
            $retirosEntreFechas = Array();
            $fechas = retiro::ObtenerConjuntoFechas($fechaInicio,$fechaFin);
            $retirosJson = retiro::JsonDeserialize($rutaArchivoJson);
    
            if($fechas !== null && $retirosJson !== null && !empty($retirosJson)){
                foreach($fechas as $fecha){
                    foreach($retirosJson as $retiro){
                        if($retiro->GetFecha() == $fecha){
                            $retirosEntreFechas[] = $retiro;
                        }
                    }
                }
            }
    
            return $retirosEntreFechas;
        }
    
        public static function CompararPorNumeroDeCuenta($a, $b){
            return $a->GetNroCuenta() > $b->GetNroCuenta();
        }
        public static function OrdenarRetirosPorNumeroCuenta($Retiros){
            usort($Retiros, 'Retiro::CompararPorNumeroDeCuenta');
            return $Retiros;
        }
        public static function ObtenerRetirosPorTipoCuenta($tipoCuenta, $rutaArchivoJson) {
            $Retiros = Array();
            $RetirosJson = Retiro::JsonDeserialize($rutaArchivoJson);
            
            foreach($RetirosJson as $Retiro){
                // echo $Retiro->__toString()."<br>";
                if($Retiro->GetTipoCuenta() == $tipoCuenta) {
                    $Retiros[] = $Retiro;
                }
            }
    
            return $Retiros;
        }
        public static function ObtenerRetirosPorMoneda($moneda, $rutaArchivoJson){
            $Retiros = Array();
            $RetirosJson = Retiro::JsonDeserialize($rutaArchivoJson);
            
            foreach($RetirosJson as $Retiro){
                if($Retiro->GetMoneda() == $moneda) {
                    $Retiros[] = $Retiro;
                }
            }
    
            return $Retiros;
        }
    }

?>