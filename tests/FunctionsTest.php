<?php

namespace Cast\Crypto\uint64\Tests;

use Cast\Crypto\unit64\Uint64 as uint64;
use PHPUnit\Framework\TestCase;
use function Cast\Crypto\unit64\_and;
use function Cast\Crypto\unit64\add;
use function Cast\Crypto\unit64\cmp;
use function Cast\Crypto\unit64\mod2;
use function Cast\Crypto\unit64\mul;
use function Cast\Crypto\unit64\neg;
use function Cast\Crypto\unit64\one;
use function Cast\Crypto\unit64\ROTL;
use function Cast\Crypto\unit64\ROTR;
use function Cast\Crypto\unit64\sub;
use function Cast\Crypto\unit64\SHR;
use function Cast\Crypto\unit64\_xor;
use function Cast\Crypto\unit64\uint64;

class FunctionsTest extends TestCase
{
    public function test_add()
    {
        $a = new uint64("4f3f9abb2e7c8889");
        $b = new uint64("68825a36ac081669");

        $this->assertEquals([3082941681, 3666124530], add($a, $b)->hilo());
        $this->assertEquals("b7c1f4f1da849ef2", add($a, $b)->getHex());
    }

    /**
     * Overflow
     * $a      :  4aa537e02d4ff2d7 :  5378766765936341719 :   0100101010100101 0011011111100000 0010110101001111 1111001011010111
     * $b      :  e8c2a9e22698b38b : 16772154751056393099 :   1110100011000010 1010100111100010 0010011010011000 1011001110001011
     * add()   :  3367e1c253e8a662 :  3704177443283183202 :   0011001101100111 1110000111000010 0101001111101000 1010011001100010
     * gmp_add : 13367e1c253e8a662 : 22150921516992734818 : 1 0011001101100111 1110000111000010 0101001111101000 1010011001100010
     * ~~~~~~~~> ^ ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~> ^
     */
    public function test_add_overflow()
    {
        $a = new uint64("4aa537e02d4ff2d7");
        $b = new uint64("e8c2a9e22698b38b");

        $this->assertEquals([1252341728, 760214231], $a->hilo());
        $this->assertEquals([3905071586, 647541643], $b->hilo());
        $this->assertEquals([5157413314, 1407755874], add($a, $b)->hilo());
        $this->assertEquals("3367e1c253e8a662", add($a, $b)->getHex());
    }

    public function test_sub()
    {
        $a = new uint64("68825a36ac081669");
        $b = new uint64("4f3f9abb2e7c8889");

        $this->assertEquals([4718772091, 6401265120], sub($a, $b)->hilo());
        $this->assertEquals("1942bf7b7d8b8de0", sub($a, $b)->getHex());
    }

    public function test_one()
    {
        $this->assertEquals('0000000000000001', one()->getHex());
    }

    public function test_neg()
    {
        $a = new uint64("4f3f9abb2e7c8889");
        $this->assertEquals("b0c06544d1837777", neg($a)->getHex());
    }

    public function test_sub_overflow()
    {
        $a = new uint64("4f3f9abb2e7c8889");
        $b = new uint64("68825a36ac081669");

        $this->assertEquals([3871162500, 2188669472], sub($a, $b)->hilo());
        $this->assertEquals("e6bd408482747220", sub($a, $b)->getHex());
    }

    public function test_shr()
    {
        $this->assertEquals(-5, -10 >> 1);
        $this->assertEquals(9223372036854775803, SHR(-10, 1));
    }

    public function test__xor()
    {
        $a = new uint64("4f3f9abb2e7c8889");
        $b = new uint64("68825a36ac081669");

        $this->assertEquals([666747021, 2188680928], _xor($a, $b)->hilo());
        $this->assertEquals("27bdc08d82749ee0", _xor($a, $b)->getHex());
    }

    public function test__and()
    {
        $a = new uint64("4f3f9abb2e7c8889");
        $b = new uint64("68825a36ac081669");

        $this->assertEquals([1208097330, 738721801], _and($a, $b)->hilo());
        $this->assertEquals("48021a322c080009", _and($a, $b)->getHex());
    }

    public function test_rotate()
    {
        $this->assertEquals("34560f0f0f0f0f12", ROTR(new uint64("0f0f0f0f0f123456"), 16)->getHex());
        $this->assertEquals("0f0f0f1234560f0f", ROTL(new uint64("0f0f0f0f0f123456"), 16)->getHex());
    }

    public function test_mul()
    {
        $a = new uint64('4f3f9abb2e7c8889');
        $b = new uint64(dechex(2));

        $this->assertEquals([2659136886, 1559826706], mul($a, $b)->hilo());
        $this->assertEquals('9e7f35765cf91112', mul($a, $b)->getHex());
    }

    public function test_cmp()
    {
        $a = new uint64("4f3f9abb2e7c8889");
        $b = new uint64("68825a36ac081669");

        $this->assertEquals(-1, cmp($a, $b));
        $this->assertEquals(1, cmp($b, $a));
        $this->assertEquals(0, cmp($a, $a));
    }

    public function test_mod2()
    {
        $a = uint64("4f3f9abb2e7c8889");
        $b = uint64("b7c1f4f1da849ef2");

        $this->assertEquals(1, mod2($a)->getInt64());
        $this->assertEquals(0, mod2($b)->getInt64());
    }
}
