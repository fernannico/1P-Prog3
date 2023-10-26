<?php

class Cuenta implements JsonSerializable{
    private $_nroCuenta;
    private $_nombre;
    private $_apellido;
    private $_tipoDocumento;
    private $_nroDocumento;
    private $_mail;
    private $_tipoCuenta;
    private $_moneda;
    private $_saldo;
    public $_estado;

    public function __construct($nombre,$apellido,$tipoDocumento,$nroDocumento,$mail,$tipoCuenta,$moneda,$saldo= 0,$nroCuenta="",$estado = "") {
        $this->_nroCuenta = $nroCuenta;
        $this->_nombre = $nombre;
        $this->_apellido = $apellido;
        $this->_tipoDocumento = $tipoDocumento;
        $this->_nroDocumento = $nroDocumento;
        $this->_mail = $mail;
        $this->_tipoCuenta = $tipoCuenta;
        $this->_moneda = $moneda;
        $this->_saldo = $saldo;
        $this->_estado = $estado;
    }

    public function GetNroCuenta() {
        return $this->_nroCuenta;
    }
    public function GetNombre() {
        return $this->_nombre;
    }
    public function GetApellido () {
        return $this->_apellido;
    } 
    public function GetTipoDocumento () {
        return $this->_tipoDocumento;
    } 
    public function GetNroDocumento () {
        return $this->_nroDocumento;
    } 
    public function GetMail () {
        return $this->_mail;
    } 
    public function GetTipoCuenta () {
        return $this->_tipoCuenta;
    } 
    public function GetMoneda () {
        return $this->_moneda;
    } 
    public function GetSaldo () {
        return $this->_saldo;
    }
    public function SetSaldo($cantidad) {
        $this->_saldo = $cantidad;
    }
    
    public function SetNombre($nombre) {
        $this->_nombre = $nombre;
    }
    
    public function SetApellido($apellido) {
        $this->_apellido = $apellido;
    }
    
    public function SetNroDocumento($nroDocumento) {
        $this->_nroDocumento = $nroDocumento;
    }
    
    public function SetMail($mail) {
        $this->_mail = $mail;
    }
    public function SetEstado($estado) {
        $this->_estado = $estado;
    }
    public function GetEstado() {
        return $this->_estado;
    }

    public static function ValidarParametrosPost() {
        $retorno = false;
        if(isset($_POST["nombre"]) && isset($_POST["apellido"]) && isset($_POST["tipoDocumento"]) && isset($_POST["nroDocumento"]) && isset($_POST["email"]) && isset($_POST["tipoCuenta"]) && isset($_POST["moneda"]) && isset($_FILES["imagen"])) {
            $retorno = true;
        }
        return $retorno;
    }

    public static function ValidarTipoCuenta($tipo) {
        $retorno = false;

        $tipoCAUSD= "CAU" . "$" . "S";
        $tipoCCUSS= "CCU" . "$" . "S";

        if($tipo == "CA$" || $tipo == $tipoCAUSD || $tipo == "CC$" || $tipo == $tipoCCUSS || (!empty($tipo) && !trim($tipo) === "")) {
            $retorno = true;
        }

        return $retorno;
    }
    public static function ValidarMoneda($moneda) {
        $retorno = false;
        $dolar = "U" . "$" . "S";

        if($moneda == "$" || $moneda == $dolar || (!empty($moneda) && !trim($moneda) === "")) {
            $retorno = true;
        }

        return $retorno;
    }

    public static function ValidarTipoCuentaConMoneda($tipoCuenta,$moneda) {
        $retorno = false;
        $dolar = "U" . "$" . "S";
        $tipoCAUSS= "CAU" . "$" . "S";
        $tipoCCUSS= "CCU" . "$" . "S";

        if(Cuenta::ValidarTipoCuenta($tipoCuenta) && Cuenta::ValidarMoneda($moneda) ) {
            if($moneda == "$" && ($tipoCuenta == "CA$" || $tipoCuenta == "CC$")){
                $retorno = true;
            }elseif($moneda == $dolar && ($tipoCuenta == $tipoCAUSS || $tipoCuenta == $tipoCCUSS)){
                $retorno = true;
            }
        }

        return $retorno;
    }

    public function __toString() {
        return 
        "nro Cuenta: " . $this->_nroCuenta .
        "<br>nombre: " . $this->_nombre .
        "<br>apellido: " . $this->_apellido .
        "<br>tipo Documento: " . $this->_tipoDocumento .
        "<br>nro Documento: " . $this->_nroDocumento .
        "<br>mail: " . $this->_mail .
        "<br>tipo Cuenta: " . $this->_tipoCuenta .
        "<br>moneda: " . $this->_moneda .
        "<br>saldo: " . $this->_saldo . 
        "<br>estado: " . $this->_estado; 
    }

    public function JsonSerialize() {
        return [
            'nroCuenta' => $this->GetNroCuenta(),
            'nombre' => $this->GetNombre(),
            'apellido' => $this->GetApellido(),
            'tipoDocumento' => $this->GetTipoDocumento(),
            'nroDocumento' => $this->GetNroDocumento(),
            'mail' => $this->GetMail(),
            'tipoCuenta' => $this->GetTipoCuenta(),
            'moneda' => $this->GetMoneda(),
            'saldo' => $this->GetSaldo(),
            'estado'=> $this->GetEstado()
        ];
    }

    public static function JsonDeserialize($rutaJson) {
        $arrayCuentas = Array();
        if(file_exists($rutaJson)){
            $jsonString = file_get_contents($rutaJson);
            $cuentas = json_decode($jsonString, true);

            if (is_array($cuentas)) {
                foreach ($cuentas as $item) {
                    $cuenta = new Cuenta($item['nombre'], $item['apellido'], $item['tipoDocumento'], $item['nroDocumento'], $item['mail'], $item['tipoCuenta'], $item['moneda'], $item['saldo'],$item['nroCuenta'],$item['estado']);
                    $arrayCuentas[] = $cuenta;
                }
            }
        }

        return $arrayCuentas;
    }
    public static function GuardarCuentaJSON($cuenta,$rutaArchivoJson)
    {
        $cuentaInd = $cuenta->JsonSerialize();

        $cuentas = [];
        if (file_exists($rutaArchivoJson)) {
            $cuentas = json_decode(file_get_contents($rutaArchivoJson), true);
        }

        $cuentas[] = $cuentaInd;

        file_put_contents($rutaArchivoJson, json_encode($cuentas, JSON_PRETTY_PRINT));

        return true;
    }
    
    public static function ValidarCuentaEnJson($moneda,$tipoCuenta,$nroCuenta,$rutaArchivoJson) {
            
        $existe = null;
        $cuentasCargadas = Cuenta::JsonDeserialize($rutaArchivoJson);

        foreach($cuentasCargadas as $cuentaCargada){

            if($cuentaCargada->GetNroCuenta()== $nroCuenta){
                if($cuentaCargada->GetMoneda() === $moneda && $cuentaCargada->GetTipoCuenta()=== $tipoCuenta){
                    $cuentaEncontrada = $cuentaCargada;
                    $existe= $cuentaEncontrada;                        
                    break;
                }
            }
        }
        return $existe;
    }

    public static function ValidarUsuarioYTipoEnJson($nombre,$apellido,$nroDocumento,$tipoCuenta,$moneda,$rutaArchivoJson){
        
        $existe = null;
        $cuentasCargadas = Cuenta::JsonDeserialize($rutaArchivoJson);

        foreach($cuentasCargadas as $cuentaCargada){

            if($cuentaCargada->GetNombre()== $nombre && $cuentaCargada->GetApellido() == $apellido && $cuentaCargada->GetNroDocumento() == $nroDocumento){
                if($cuentaCargada->GetMoneda() === $moneda && $cuentaCargada->GetTipoCuenta()=== $tipoCuenta){
                    $cuentaEncontrada = $cuentaCargada;
                    $existe= $cuentaEncontrada;                        
                    break;
                }
            }
        }
        return $existe;
    }

    //"ya existe ese DNI y pero con otro nombre!"
    public static function ValidarUsuarioEnJson($nroDocumento,$nombre,$apellido,$rutaArchivoJson){
        $usuarioValido = false;
        // $cuentasCargadas = Cuenta::JsonDeserialize($rutaArchivoJson);

        $cuentaPorDni = Cuenta::ValidarDniEnJson($nroDocumento,$rutaArchivoJson);

        if($cuentaPorDni == null){
            $usuarioValido = true;// El DNI no existe en el archivo JSON-> cualquier nombre y apellido .
        }else{
            if ($cuentaPorDni->GetNombre() == $nombre && $cuentaPorDni->GetApellido() == $apellido) {
                $usuarioValido = true; // El nombre y apellido coinciden.
            } else {
                $usuarioValido =  false; // El nombre y apellido no coinciden con el dni!.
            }    
        }
        return $usuarioValido;
    }

    public static function ValidarDniEnJson($numDni, $rutaArchivoJson){ 
        $existe = null;

        $cuentasCargadas = Cuenta::JsonDeserialize($rutaArchivoJson);
        
        foreach($cuentasCargadas as $cuenta){
            if($cuenta->GetNroDocumento() == $numDni){
                $existe = $cuenta;
                break;
            }
        }

        return $existe;
    }
    public static function ValidarNroCuentaEnJson($nroCuenta,$rutaArchivoJson){
        
        $existe = null;
        $cuentasCargadas = Cuenta::JsonDeserialize($rutaArchivoJson);

        foreach($cuentasCargadas as $cuentaCargada){
            if($cuentaCargada->GetNroCuenta()== $nroCuenta){
                $cuentaEncontrada = $cuentaCargada;
                $existe= $cuentaEncontrada;                        
                break;                
            }
        }
        return $existe;
    }
    public static function ObtenerCuentaPorNroCuenta($NroCuenta,$rutaArchivoJson) {
        $cuentaJson = null;
        
        $cuentasJson = Cuenta::JsonDeserialize($rutaArchivoJson);
        if($cuentasJson !== null && !empty($cuentasJson)){
            foreach($cuentasJson as $cuenta){
                if($NroCuenta == $cuenta->GetNroCuenta()){
                    $cuentaJson = $cuenta;
                    break;
                }
            }
        }        
        return $cuentaJson;
    } 
    
    public static function ObtenerCuentasPorDni($usuarioDni,$rutaArchivoJson) {
        $cuentasUsuario = Array();
        $cuentasJson = Cuenta::JsonDeserialize($rutaArchivoJson);
        
        foreach($cuentasJson as $cuenta){
            if($cuenta->GetNroDocumento() === $usuarioDni) {
                $cuentasUsuario []= $cuenta;
            }
        }

        return $cuentasUsuario;
    }  
    public function ActualizarSaldoCuentaJson($cantidad,$rutaArchivoJson) {
        $retorno = false;

        $cuentasCargadas = Cuenta::JsonDeserialize($rutaArchivoJson);

        foreach($cuentasCargadas as $cuentaCargada){
            if($cuentaCargada->GetNroCuenta() == $this->GetNroCuenta() &&  $cuentaCargada->GetTipoCuenta() === $this->GetTipoCuenta() && $cuentaCargada->GetMoneda() === $this->GetMoneda() ){
                $cuentaCargada->SetSaldo($this->_saldo + $cantidad);
                // Actualizar JSON
                $cuentas = [];
                foreach ($cuentasCargadas as $cuenta) {
                    $cuentas[] = $cuenta->JsonSerialize();
                }
                file_put_contents($rutaArchivoJson, json_encode($cuentas, JSON_PRETTY_PRINT));   
                $retorno = true; 
            }
        }   
        return $retorno;
    }

    public function GuardarImagen($nombreImagen,$directorioDestino) {
        $retorno = false;
        $carpeta_archivos = $directorioDestino;
        $nombre_archivo = $this->GetNroCuenta() . $this->GetTipoCuenta() . ".jpg";       
        $ruta_destino = $carpeta_archivos . $nombre_archivo;

        if (move_uploaded_file($nombreImagen,  $ruta_destino)){
            $retorno = true;
        }     
        return $retorno;
    }

    public static function MoverImagen($nombreArchivo,$carpetaOrigen,$carpetaDestino) {
        $retorno = false;
        $nombreImagen = $nombreArchivo; 

        $rutaOrigen = $carpetaOrigen . $nombreImagen;
        $rutaDestino = $carpetaDestino . $nombreImagen;
        
        // try {
        //     //code...
        //     if (rename($rutaOrigen, $rutaDestino)) {
        //        $retorno = true;
        //     }
        // }finally {
        //     $retorno = false;
        // }
        if (file_exists($rutaOrigen)) {
            try {
                if (rename($rutaOrigen, $rutaDestino)) {
                    $retorno = true;
                }
            } finally {
                // Manejar la excepción si ocurre algún error al mover el archivo
                $retorno = false;
            }
        }
    
        return $retorno;
    }

    public static function ConsultarCuenta($tipoCuenta,$nroCuenta,$rutaArchivoJson) {            
        $cuenta = null;
        $cuentasCargadas = Cuenta::JsonDeserialize($rutaArchivoJson);

        foreach($cuentasCargadas as $cuentaCargada){

            if($cuentaCargada->GetNroCuenta()== $nroCuenta){
                if($cuentaCargada->GetTipoCuenta()=== $tipoCuenta){
                    $cuentaEncontrada = $cuentaCargada;
                    $cuenta= $cuentaEncontrada;                        
                    break;
                }
            }
        }

        return $cuenta;
    }

    public function ModificarCuenta($nombre,$apellido,$nroDocumento,$mail) {
        $this->SetNombre($nombre);
        $this->SetApellido($apellido);
        $this->SetNroDocumento($nroDocumento);
        $this->SetMail($mail);
    }

    public function ModificarCuentaJson($nombre,$apellido,$nroDocumento,$mail,$rutaArchivoJson) {
        $retorno = false;

        $cuentasCargadas = Cuenta::JsonDeserialize($rutaArchivoJson);

        foreach($cuentasCargadas as $cuentaCargada){
            if($cuentaCargada->GetNroCuenta() == $this->GetNroCuenta() &&  $cuentaCargada->GetTipoCuenta() === $this->GetTipoCuenta() && $cuentaCargada->GetMoneda() === $this->GetMoneda() ){
                $cuentaCargada->ModificarCuenta($nombre,$apellido,$nroDocumento,$mail);
                // Actualizar JSON
                $cuentas = [];
                foreach ($cuentasCargadas as $cuenta) {
                    $cuentas[] = $cuenta->JsonSerialize();
                }
                file_put_contents($rutaArchivoJson, json_encode($cuentas, JSON_PRETTY_PRINT));   
                $retorno = true; 
            }
        }   
        return $retorno;
    }

    
    public function ModificarEstadoCuentaJson($estado,$rutaArchivoJson) {
        $retorno = false;

        $cuentasCargadas = Cuenta::JsonDeserialize($rutaArchivoJson);

        foreach($cuentasCargadas as $cuentaCargada){
            if($cuentaCargada->GetNroCuenta() == $this->GetNroCuenta() &&  $cuentaCargada->GetTipoCuenta() === $this->GetTipoCuenta() && $cuentaCargada->GetMoneda() === $this->GetMoneda()){
                $cuentaCargada->SetEstado($estado);
                // Actualizar JSON
                $cuentas = [];
                foreach ($cuentasCargadas as $cuenta) {
                    $cuentas[] = $cuenta->JsonSerialize();
                }
                file_put_contents($rutaArchivoJson, json_encode($cuentas, JSON_PRETTY_PRINT));   
                $retorno = true; 
            }
        }   
        return $retorno;
    }
}

?>