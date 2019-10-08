<?php

class PCA9685
{

    public $i2c_bus;
    public $i2c_address;
    public $i2c_set = '/usr/sbin/i2cset -y';

    public function __construct($i2c_bus = 1, $i2c_address = 0x40)
    {
        $this->i2c_bus     = $i2c_bus;
        $this->i2c_address = $i2c_address;
    }

    public function setPWMpercent($channel, $percent)
    {
        $value = round(4096 * $percent * .01);
        $this->setPWM($channel, $value);
    }

    public function make_exec($start_addr, $onLo, $onHi, $offLo, $offHi){
        return "$this->i2c_set $this->i2c_bus $this->i2c_address $start_addr $onLo $onHi $offLo $offHi i";
    }

    public function setPWM($channelNum, $countOff, $countOn = 0, $dump = true)
    {
        list($onHi, $onLo)   = $this->get12bit($countOn);
        list($offHi, $offLo) = $this->get12bit($countOff);

        $start_addr = 4 * $channelNum + 6;
        $exec = $this->make_exec($start_addr, $onLo, $onHi, $offLo, $offHi);
        if($dump) echo $exec;
        exec($exec);
    }

    public function setAll($countOff, $countOn = 0, $dump = true)
    {
        $this->setPWM(61, $countOff, $countOn, $dump);
    }

    public function setFrequency($frequency)
    {
        $value = round(25000000 / (4096 * $frequency)) - 1;

        // the PCA9685 has frequency limits, we'll be sure we're within those
        if($value <   3) $value = 3;
        if($value > 255) $value = 255;

        exec("$this->i2c_set $this->i2c_bus $this->i2c_address 254 $value");
    }

    private function get12bit($value)
    {
        if($value < 0)    $value = 0;
        if($value > 4095) $value = 4095;

        $hi = floor($value / 256);
        $lo = $value % 256;

        return [$hi,$lo];
    }
}
