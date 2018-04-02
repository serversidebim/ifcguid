<?php 

namespace Serversidebim\IFCGuid;

/**
*  IFCGuid - Convert to and from IFCGuid, GUID, and Binary format
*
*  The IFC Specifications declare a compressed GUID, the IFCGuid.
*  This class can be used to convert to and from this IFCGuid.
* 
*  For more information, see: 
*  http://www.buildingsmart-tech.org/implementation/get-started/ifc-guid
*
*  @author Maarten Veerman
*/
class IFCGuid{
    
    /**
     *
     * @var string $internalbin Internal storage of binary string
     */
    private $internalbin = null;

    /**
     * Empty constructor
     */
    function __construct() {
        
    }
    
    /**
     * Load an IFCGuid into the class for conversion
     * @param string $ifc The IFCGuid
     * @return boolean|\Serversidebim\IFCGuid\IFCGuid Returns false on error, $this on success
     */
    public function fromIfc($ifc) {
        $bin = self::ifcToBin($ifc);
        if ($bin) {
            $this->internalbin = $bin;
            return $this;
        }
        return false;
    }
    
    /**
     * Load a GUID into the class for conversion
     * @param string $guid The GUID
     * @return boolean|\Serversidebim\IFCGuid\IFCGuid Returns false on error, $this on success
     */
    public function fromGuid($guid) {
        $guid = str_replace("-", "", $guid);
        $guid = str_replace(" ", "", $guid);
        // now the guid is just a standard hex, so convert it
        return $this->fromHex($guid);
    }
    
    /**
     * Load a Hexadecimal value into the class for conversion
     * @param string $hex The Hexadecimal value
     * @return boolean|\Serversidebim\IFCGuid\IFCGuid Returns false on error, $this on success
     */
    public function fromHex($hex) {
        if (strlen($hex) !== 32) {
            // not an offical GUID length
            trigger_error('Given ifc code $ifc is not of length 22');
            return false;
        }
        
       $this->internalbin = self::hexToBin($hex);
        
       return $this;
    }
    
    /**
     * Load a binary string into the class for conversion
     * @param string $bin The binary string
     * @return boolean|\Serversidebim\IFCGuid\IFCGuid Returns false on error, $this on success
     */
    public function fromBin($bin) {
        if (!self::validateBin($bin)) return false;
        
        // seems ok...
        $this->internalbin = $bin;
        
        return $this;
    }
    
    /**
     * Load a decimal into the class for conversion
     * @param string $dec The decimal
     * @return boolean|\Serversidebim\IFCGuid\IFCGuid Returns false on error, $this on success
     */
    public function fromDec($dec) {
        $bin = decbin($dec);
        if (strlen($bin)>128) {
            trigger_error('Given decimal number is larger than the maximum allowed value');
            return false;
        }
        
        $bin = self::prependBin($bin);
        
        $this->internalbin = $bin;
        
        return $this;
        
    }
    
    /**
     * Convert the internal number to IFCGuid
     * @return string The IFCGuid
     */
    public function toIfc() {
        if (!$this->internalbin) return false;
        return self::binToIfc($this->internalbin);
    }
    
    /**
     * Convert the internal number to GUID
     * @param type $join The character to join the GUID parts
     * @return string the GUID
     */
    public function toGuid($join = "-") {
        if (!$this->internalbin) return false;
        
        $hex = $this->toHex();
        
        // 8 4 4 4 12
        $parts = [];
        $parts[] = substr($hex, 0, 8);
        $parts[] = substr($hex, 8, 4);
        $parts[] = substr($hex, 12, 4);
        $parts[] = substr($hex, 16, 4);
        $parts[] = substr($hex, 20, 12);
        
        return implode($join, $parts);
    }
    
    /**
     * Convert the internal number to Hexadecimal
     * @return string The hexadecimal code
     */
    public function toHex() {
        if (!$this->internalbin) return false;
        return self::binTohex($this->internalbin);
    }
    
    /**
     * Convert the internal number to binary
     * @return string The binary string
     */
    public function toBin() {
        if (!$this->internalbin) return false;
        return $this->internalbin;
    }
    
    /**
     * Convert the internal number to decimal
     * @return decimal The decimal
     */
    public function toDec() {
        if (!$this->internalbin) return false;
        return bindec($this->internalbin);
    }
    
    /**
     * Convert a binary string to IFCGuid
     * @param string $bin The binary string to convert
     * @return string The converted IFCGuid
     */
    static public function binToIfc($bin) {
        if (!self::validateBin($bin)) return false;
        
        // first the first 2 bits
        $ifc = "";
        $ifc .= self::binPartToIfc(substr($bin, 0, 2));
        
        // now the other parts
        $parts = str_split(substr($bin, 2), 6);
        
        foreach ($parts as $p) {
            $ifc .= self::binPartToIfc($p);
        }
        
        return $ifc;
    }
    
    /**
     * Convert a binary string of 2 or 6 characters to 1 IFCGuid character
     * @param string $bin The binary string to convert
     * @return string the IFCGuid chacter
     */
    static public function binPartToIfc($bin) {
        $code = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_$';
        $dec = bindec($bin);
        return $code[$dec];
    }        
    
    /**
     * Converts an IFCGuid to binary string
     * @param string $ifc The IFCGuid string to convert
     * @return boolean|string false on error, binary string on success
     */
    static public function ifcToBin($ifc) {
        if (strlen($ifc) !== 22) {
            // not an offical IFC code
            
            return false;
        }
        
        $bin = "";
        
        for ($i=0;$i<strlen($ifc);$i++) {
            $char = $ifc[$i];
            if ($binpart = self::ifcPartToBin($char)) {
                
                $length = $i == 0 ? 2 : 6;
                $binpart = self::prependBin($binpart, $length);
                
                $bin .= $binpart;
            }
            else {
                return false;
            }
        }
        
        return $bin;
    }
    
    /**
     * Convert 1 IFCGuid character to binary string
     * @param string $ifc The IFCGuid character to convert
     * @return boolean|string false on error, binary string on success
     */
    static public function ifcPartToBin($ifc) {
        $code = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_$';
        $pos = strpos($code, $ifc);
        
        if ($pos === false) {
            trigger_error("Character $ifc is not a valid ifc character");
            return false;
        }

        $bin = decbin($pos);
        
		return $bin;
    }
    
    /**
     * Validate if the provided binary string is valid
     * @param string $bin
     * @return boolean True on success, false on failure
     */
    static public function validateBin($bin) {
        if (strlen($bin) !== 128) {
            trigger_error('Given binary string is not of length 128');
            return false;
        }
        
        // check if it is a string of only 0's and 1's
        /*var_dump($bin);
        if (!preg_match("/[^0,1]/", trim($bin))) {
            trigger_error('Given binary string contains characters other than 0 or 1');
            return false;
        }*/
        return true;
    }
    
    /**
     * Prepend a binary string with 0's up to a given length
     * @param string $bin The binary string to prepend
     * @param integer $length The length up to which to prepend
     * @return string The prepend binary string
     */
    static public function prependBin($bin, $length = 128) {

            // prepend 0's
            while(strlen($bin) < $length) {
                $bin = "0" . $bin;
            }

        return $bin;
    }
    
    /**
     * Convert a hexadecimal value to binary value
     * @param string $hex The hexadecimal value to convert
     * @return string The resulting binary string
     */
    static public function hexToBin($hex) {
        $code = "0123456789abcdef";
        $bin = "";
        for ($i=strlen($hex)-1;$i>=0;$i--) {
            $char = $hex[$i];
            $dec = strpos($code, $char);
            $binpart = decbin($dec);
            $bin = self::prependBin($binpart,4) . $bin;
        }
        
        return $bin;
    }
    
    /**
     * Convert a binary string to hexadecimal
     * @param string $bin The binary string to convert
     * @return string The resulting hexadecimal string
     */
    static public function binToHex($bin) {
        $code = "0123456789abcdef";
        
        // check the length
        $rest = strlen($bin) % 4;
        if ($rest > 0) {
            // we need to prepend 0's....
            $newlength = strlen($bin) + (4-$rest);
            $bin = self::prependBin($bin, $newlength);
        }
        
        $parts = str_split($bin, 4);
        $res = "";
        foreach ($parts as $p) {
            $dec = bindec($p);
            $res .= $code[$dec];
        }
        
        return $res;
    }
   
}