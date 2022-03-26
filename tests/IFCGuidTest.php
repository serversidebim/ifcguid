<?php

use PHPUnit\Framework\TestCase;

class IFCGuidTest extends TestCase
{

    public function testIsThereAnySyntaxError()
    {
        $var = new Serversidebim\IFCGuid\IFCGuid;
        $this->assertTrue(is_object($var));
        unset($var);
    }

    public function testConversions()
    {
        $guid = "01cf62c8-e9bc-bf88-0000-000000000005";
        $bin = Serversidebim\IFCGuid\IFCGuid::prependBin("1110011110110001011001000111010011011110010111111100010000000000000000000000000000000000000000000000000000000000000000101");
        $dec = "2406037039613475731795058764298584069";
        $ifcguid = '01psB8wRo$Y00000000005';

        $ifc = new Serversidebim\IFCGUid\IFCGuid();
        $ifc->fromGuid($guid);

        $this->assertEquals($guid, $ifc->toGuid());
        $this->assertEquals($bin, $ifc->toBin());
        //$this->assertEquals($dec,$ifc->toDec());
        $this->assertEquals($ifcguid, $ifc->toIfc());

        $ifc->fromBin($bin);
        $this->assertEquals($guid, $ifc->toGuid());
        $this->assertEquals($bin, $ifc->toBin());
        //$this->assertEquals($dec,$ifc->toDec());
        $this->assertEquals($ifcguid, $ifc->toIfc());

        $ifc->fromIfc($ifcguid);
        $this->assertEquals($guid, $ifc->toGuid());
        $this->assertEquals($bin, $ifc->toBin());
        //$this->assertEquals($dec,$ifc->toDec());
        $this->assertEquals($ifcguid, $ifc->toIfc());
    }

}