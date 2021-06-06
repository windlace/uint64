<?php

namespace Cast\Crypto\unit64;

class Uint64 {
    public $hi;
    public $lo;
    public function __construct(string $hex = '')
    {
        if (!is_scalar($hex))  throw new \InvalidArgumentException('Not a scalar value.');
        if (strlen($hex) > 16) throw new \InvalidArgumentException('Too long value. Max 8 bytes.');
        $hex = str_pad($hex, 16, '0', STR_PAD_LEFT);
        [$this->hi, $this->lo] = array_values(unpack('N*', hex2bin($hex)));
    }
    public static function new(int $hi = 0, int $lo = 0) : Uint64
    {
        return new self(self::hex($hi, $lo));
    }
    public static function hex(int $hi, int $lo) : string
    {
        return bin2hex(pack('NN', $hi, $lo));
    }
    public function getHex() : string
    {
        return self::hex($this->hi, $this->lo);
    }
    function getInt64()
    {
        $hex = $this->getHex();
        $value = str_pad($hex, 16, '0', STR_PAD_LEFT);
        $bytes = array_map('hexdec', array_reverse(str_split($value, 2)));
        $packed = implode('', array_map('chr', $bytes));
        [, $int] = unpack("q", $packed);
        return $int;
    }
    public function hilo()
    {
        return [$this->hi, $this->lo];
    }
    public function xor(uint64 $b)
    {
        $a_h = $this->hi & 0xffffffff;
        $a_l = $this->lo & 0xffffffff;
        $b_h = $b->hi & 0xffffffff;
        $b_l = $b->lo & 0xffffffff;
        $this->hi = $a_h ^ $b_h;
        $this->lo = $a_l ^ $b_l;
        return $this;
    }
    function isZero() : bool
    {
        return ($this->lo === 0) && ($this->hi === 0);
    }
    public function set(int $hi, int $lo)
    {
        [$this->hi, $this->lo] = [$hi, $lo];
        return $this;
    }
    public function lessThan(uint64 $b) : bool
    {
        return cmp($this, $b) === -1;
    }
    public function greaterThan(uint64 $b) : bool
    {
        return cmp($this, $b) === 1;
    }
    public function equalTo(uint64 $b) : bool
    {
        return cmp($this, $b) === 0;
    }
    public function add($b)
    {
        $this_h   = $this->hi & 0xffffffff;
        $this_l   = $this->lo & 0xffffffff;
        $o_h      = $b->hi & 0xffffffff;
        $o_l      = $b->lo & 0xffffffff;
        //var lowest, lowMid, highMid, highest; //four parts of the whole 64 bit number..
        //need to add the respective parts from each number and the carry if on is present..
        $lowest   = (($this_l & 0XFFFF) + ($o_l & 0XFFFF));
        $lowMid   = (SHR($this_l, 16) + SHR($o_l, 16) + SHR($lowest, 16));
        $highMid  = ($this_h & 0XFFFF) + ($o_h & 0XFFFF) + SHR($lowMid, 16);
        $highest  = SHR($this_h, 16) + SHR($o_h, 16) + SHR($highMid, 16);
        //now set the hgih and the low accordingly..
        $this->lo = (($lowMid << 16) | ($lowest & 0XFFFF));
        $this->hi = (($highest << 16) | ($highMid & 0XFFFF));
        return $this;
    }
    public function inc(){
        return $this->add(one());
    }
}
