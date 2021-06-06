<?php

namespace Cast\Crypto\uint64\Tests;

use Cast\Crypto\unit64\Uint64 as uint64;
use PHPUnit\Framework\TestCase;

class Uint64Test extends TestCase
{
    public function test_construct()
    {
        $a = new uint64();
        $this->assertEquals([0, 0], [$a->hi, $a->lo]);

        $a = new uint64(false);
        $this->assertEquals([0, 0], [$a->hi, $a->lo]);

        $a = new uint64("4f3f9abb2e7c8889");
        $this->assertEquals([1329568443, 779913353], [$a->hi, $a->lo]);

        $a = new uint64("ffffffff00000000");
        $this->assertEquals([4294967295, 0], [$a->hi, $a->lo]);

        $a = new uint64("00000000ffffffff");
        $this->assertEquals([0, 4294967295], [$a->hi, $a->lo]);

        $a = new uint64("ffffffff");
        $this->assertEquals([0, 4294967295], [$a->hi, $a->lo]);

        $a = new uint64("4f");
        $this->assertEquals([0, 79], [$a->hi, $a->lo]);

        $a = new uint64("f");
        $this->assertEquals([0, 15], [$a->hi, $a->lo]);

        $a = new uint64(123); // hex 123 -> dec 291
        $this->assertEquals([0, 291], [$a->hi, $a->lo]);
    }

    public function test_construct_null()
    {
        $this->expectException(\TypeError::class);
        new uint64(null);
    }

    public function test_construct_array()
    {
        $this->expectException(\TypeError::class);
        new uint64([]);
    }

    public function test_construct_object()
    {
        $this->expectException(\TypeError::class);
        new uint64(new \stdClass());
    }

    public function test_hex()
    {
        $this->assertEquals("4f3f9abb2e7c8889", uint64::hex(1329568443, 779913353));
    }

    public function test_getHex()
    {
        $this->assertEquals("4f3f9abb2e7c8889", uint64::new(1329568443, 779913353)->getHex());
    }

    public function test_new()
    {
        $a = uint64::new();
        $this->assertEquals([0, 0], [$a->hi, $a->lo]);

        $a = uint64::new(1329568443, 779913353);
        $this->assertEquals([1329568443, 779913353], [$a->hi, $a->lo]);
        $this->assertInstanceOf(uint64::class, $a);
    }

    public function test_hilo()
    {
        $this->assertEquals([1329568443, 779913353], (new uint64('4f3f9abb2e7c8889'))->hilo());
    }

    public function test_xor()
    {
        $this->assertEquals([666747021, 2188680928], (new uint64("4f3f9abb2e7c8889"))->xor(new uint64("68825a36ac081669"))->hilo());
        $this->assertEquals("27bdc08d82749ee0", (new uint64("4f3f9abb2e7c8889"))->xor(new uint64("68825a36ac081669"))->getHex());
    }

    public function test_toInt64()
    {
        $i = -401364645913;
        $this->assertEquals([4294967202, 2362279911], (new uint64(dechex($i)))->hilo());
        $this->assertEquals($i, (new uint64(dechex($i)))->getInt64());
    }

    public function test_isZero()
    {
        $this->assertTrue(uint64::new(0, 0)->isZero());
        $this->assertFalse(uint64::new(0, 1)->isZero());
        $this->assertFalse(uint64::new(1, 0)->isZero());
        $this->assertFalse(uint64::new(1, 1)->isZero());
    }

    public function test_set()
    {
        $this->assertEquals("4aa537e02d4ff2d7", uint64::new(0,0)->set(1252341728, 760214231)->getHex());
        $this->assertEquals("0000000000000000", uint64::new(1252341728, 760214231)->set(0,0)->getHex());
    }

    public function test_lessThan()
    {
        $a = new uint64("4aa537e02d4ff2d7");
        $b = new uint64("e8c2a9e22698b38b");
        $this->assertTrue($a->lessThan($b));
        $this->assertFalse($b->lessThan($a));
        $this->assertFalse($a->lessThan($a));
    }

    public function test_greaterThan()
    {
        $a = new uint64("4aa537e02d4ff2d7");
        $b = new uint64("e8c2a9e22698b38b");
        $this->assertTrue($b->greaterThan($a));
        $this->assertFalse($a->greaterThan($b));
        $this->assertFalse($a->greaterThan($a));
    }

    public function test_equalTo()
    {
        $a = new uint64("4aa537e02d4ff2d7");
        $b = new uint64("e8c2a9e22698b38b");
        $this->assertTrue($a->equalTo($a));
        $this->assertFalse($b->equalTo($a));
        $this->assertFalse($a->equalTo($b));
    }

    public function test_add()
    {
        $a = new uint64("4f3f9abb2e7c8889");
        $b = new uint64("68825a36ac081669");
        $a->add($b);

        $this->assertEquals([3082941681, 3666124530], $a->hilo());
        $this->assertEquals("b7c1f4f1da849ef2", $a->getHex());
    }

    public function test_inc()
    {
        $this->assertEquals([1329568443, 779913354], (new uint64("4f3f9abb2e7c8889"))->inc()->hilo());
        $this->assertEquals("4f3f9abb2e7c888a", (new uint64("4f3f9abb2e7c8889"))->inc()->getHex());
    }
}
