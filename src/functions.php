<?php

declare(strict_types=1);

namespace Cast\Crypto\uint64;

use Cast\Crypto\uint64\Uint64 as uint64;

function uint64($input = '')
{
    return new uint64($input);
}

/**
 * Logical bitwise shift right (zero-fill right shirt on 64-bit negative numbers)
 * Native PHP bitwise shift right uses arithmetical shift (non- zero-fill)
 *
 * @param $x
 * @param $c
 * @return int
 */
function SHR($x, $c)
{
    $x        = intval($x); // Because 13.5 >> 0 returns 13. We follow.
    $nmaxBits = PHP_INT_SIZE * 8;
    $c        %= $nmaxBits;
    if ($c)
        return $x >> $c & ~ (-1 << $nmaxBits - $c);
    else
        return $x;
}

/**
 * Rotate uint64-number to n-bits left and fill by zero's last n-bits
 *
 * @param Uint64 $a
 * @param int $bits
 * @return Uint64|mixed
 */
function _shl(Uint64 $a, int $bits)
{
    return _and(ROTL($a, $bits), sub(new Uint64('ffffffffffffffff'), uint64::new(0, (1 << $bits)-1)));
}

/**
 * Arithmetic addition
 *
 * @warning It has no the overflow control.
 *
 * Overflow
 * $a      :  4aa537e02d4ff2d7 :  5378766765936341719 :   0100101010100101 0011011111100000 0010110101001111 1111001011010111
 * $b      :  e8c2a9e22698b38b : 16772154751056393099 :   1110100011000010 1010100111100010 0010011010011000 1011001110001011
 * add()   :  3367e1c253e8a662 :  3704177443283183202 :   0011001101100111 1110000111000010 0101001111101000 1010011001100010
 * gmp_add : 13367e1c253e8a662 : 22150921516992734818 : 1 0011001101100111 1110000111000010 0101001111101000 1010011001100010
 * ~~~~~~~~> ^ ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~> ^
 *
 * @param uint64 $a
 * @param uint64 $b
 * @return uint64
 */
function add(uint64 $a, uint64 $b) : uint64
{
    $this_h   = $a->hi & 0xffffffff;
    $this_l   = $a->lo & 0xffffffff;
    $o_h      = $b->hi & 0xffffffff;
    $o_l      = $b->lo & 0xffffffff;
    //var lowest, lowMid, highMid, highest; //four parts of the whole 64 bit number..
    //need to add the respective parts from each number and the carry if on is present..
    $lowest   = (($this_l & 0XFFFF) + ($o_l & 0XFFFF));
    $lowMid   = (SHR($this_l, 16) + SHR($o_l, 16) + SHR($lowest, 16));
    $highMid  = ($this_h & 0XFFFF) + ($o_h & 0XFFFF) + SHR($lowMid, 16);
    $highest  = SHR($this_h, 16) + SHR($o_h, 16) + SHR($highMid, 16);
    //now set the hgih and the low accordingly..
    $c = new uint64();
    $c->lo = (($lowMid << 16) | ($lowest & 0XFFFF));
    $c->hi = (($highest << 16) | ($highMid & 0XFFFF));
    return $c;
}

/**
 * Arithmetic subtraction (a - b)
 *
 * @warning It has no the overflow control.
 *
 * Overflow
 * $a      : 23f4c41bc889043a :  02590911309252461626 : 0010001111110100110001000001101111001000100010010000010000111010
 * $b      : 300aff5546ce45e6 :  03461860005312873958 : 0011000000001010111111110101010101000110110011100100010111100110
 * sub()   : f3e9c4c681babe54 :  17575795377649139284 : 1111001111101001110001001100011010000001101110101011111001010100
 * gmp_sub : 0c163b397e4541ac :   -870948696060412332 : 0000110000010110001110110011100101111110010001010100000110101100
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~> ^
 *
 * @param uint64 $a The number being subtracted from.
 * @param uint64 $b The number subtracted from `a`.
 * @return uint64
 */
function sub(uint64 $a, uint64 $b) : uint64
{
    // a - b = a + (-b)
    return add($a, neg($b));
}

/**
 * @param uint64 $a
 * @return uint64
 */
function not(uint64 $a) : uint64
{
    return uint64::new(~$a->hi, ~$a->lo);
}

/**
 * @return uint64
 */
function one() : uint64
{
    return uint64::new(0, 1);
}

/**
 * @param $a
 * @return uint64
 */
function neg($a) : uint64
{
    // -a = a * (-1)
    return add(one(), not($a));
}

/**
 * @param uint64 $a
 * @param uint64 $b
 * @return uint64
 */
function _xor(uint64 $a, uint64 $b) : uint64
{
    $a_h    = $a->hi & 0xffffffff;
    $a_l    = $a->lo & 0xffffffff;
    $b_h    = $b->hi & 0xffffffff;
    $b_l    = $b->lo & 0xffffffff;
    $c      = uint64::new();
    $c->hi  = $a_h ^ $b_h;
    $c->lo  = $a_l ^ $b_l;
    return $c;
}

/**
 * @param uint64 $a
 * @param uint64 $b
 * @return mixed
 */
function _and(uint64 $a, uint64 $b) : uint64
{
    $c     = uint64::new();
    $c->hi = $a->hi & $b->hi;
    $c->lo = $a->lo & $b->lo;
    return $c;
}

/**
 * Rotates the bits of this word round to the right (max 32)..
 *
 * ROTR(0x0f0f0f0f0f123456, 16) -> 0x34560f0f0f0f0f12
 *
 * @param uint64 $a
 * @param int $bits
 * @return uint64
 */
function ROTR(uint64 $a, $bits) : uint64
{
    if ($bits > 32)
    {
        return ROTL($a, 64 - $bits);
    }
    $c = uint64::new();
    if ($bits === 0)
    {
        $c->lo = $a->lo >> 0;
        $c->hi = $a->hi >> 0;
    }
    else if ($bits === 32)
    { //just switch high and low over in this case..
        $c->lo = $a->hi;
        $c->hi = $a->lo;
    }
    else
    {
        $c->lo = (($a->hi << (32 - $bits)) | ($a->lo >> $bits)) & 0xffffffff;
        $c->hi = (($a->lo << (32 - $bits)) | ($a->hi >> $bits)) & 0xffffffff;
    }

    return $c; //for chaining..
}

/**
 * Rotates the bits of this word round to the left (max 32)..
 *
 * ROTL(0x0f0f0f0f0f123456, 16) -> 0x0f0f0f1234560f0f
 *
 * @param uint64 $a
 * @param int bits
 * @return uint64
 */
function ROTL(uint64 $a, int $bits) : uint64
{
    if ($bits > 32)
    {
        return ROTR($a, 64 - $bits);
    }
    $c = uint64::new();
    if ($bits === 0)
    {
        $c->lo = $a->lo >> 0;
        $c->hi = $a->hi >> 0;
    }
    else if ($bits === 32)
    { //just switch high and low over in this case..
        $c->lo = $a->hi;
        $c->hi = $a->lo;
    }
    else
    {
        $c->lo = (($a->lo << $bits) | ($a->hi >> (32 - $bits))) & 0xffffffff;
        $c->hi = (($a->hi << $bits) | ($a->lo >> (32 - $bits))) & 0xffffffff;
    }

    return $c; //for chaining..
}

function mul(uint64 $a, uint64 $b) : uint64
{
    #throw new \Exception('No plz');
    if ($a->isZero())
        return $a->zero();
    #if (!isLong(multiplier))
    #  multiplier = fromNumber(multiplier);
    if ($b->isZero())
        return $a->zero();
    // Divide each long into 4 chunks of 16 bits, and then add up 4x4 products.
    // We can skip products that would overflow.
    $a48 = $a->hi >> 16 & 0xFFFF;
    $a32 = $a->hi & 0xFFFF;
    $a16 = $a->lo >> 16 & 0xFFFF;
    $a00 = $a->lo & 0xFFFF;
    $b48 = $b->hi >> 16 & 0xFFFF;
    $b32 = $b->hi & 0xFFFF;
    $b16 = $b->lo >> 16 & 0xFFFF;
    $b00 = $b->lo & 0xFFFF;
    $c48 = 0;
    $c32 = 0;
    $c16 = 0;
    $c00 = 0;
    $c00 += $a00 * $b00;
    $c16 += $c00 >> 16;
    $c00 &= 0xFFFF;

    $c16 += $a16 * $b00;
    $c32 += $c16 >> 16;
    $c16 &= 0xFFFF;

    $c16 += $a00 * $b16;
    $c32 += $c16 >> 16;
    $c16 &= 0xFFFF;

    $c32 += $a32 * $b00;
    $c48 += $c32 >> 16;
    $c32 &= 0xFFFF;

    $c32 += $a16 * $b16;
    $c48 += $c32 >> 16;
    $c32 &= 0xFFFF;

    $c32 += $a00 * $b32;
    $c48 += $c32 >> 16;
    $c32 &= 0xFFFF;

    $c48 += $a48 * $b00 + $a32 * $b16 + $a16 * $b32 + $a00 * $b48;
    $c48 &= 0xFFFF;
    return uint64::new((($c48 << 16) | $c32) & 0xffffffff, (($c16 << 16) | $c00 ) & 0xffffffff);
}

/**
 * Compare two hex numbers
 *
 * if $a > $b : returns 1
 * if $a < $b : returns -1
 * if $a === $b : returns 0
 *
 * @param uint64 $a
 * @param uint64 $b
 * @return int
 */
function cmp(uint64 $a, uint64 $b) : int
{
    return array_reduce(
        array_map(
            function ($a, $b) {
                return [hexdec($a), hexdec($b)];
            },
            array_reverse(str_split(str_pad($a->getHex(), 16, '0', STR_PAD_LEFT), 2)),
            array_reverse(str_split(str_pad($b->getHex(), 16, '0', STR_PAD_LEFT), 2))
        ),
        function ($res, $el) {
            [$a, $b] = $el;
            if ($a === $b) {
                return $res ?? 0;
            }
            elseif($a > $b)
            {
                return 1;
            }
            else
            {
                return -1;
            }
        }
    );
}

/**
 * @param uint64 $a
 * @return uint64
 */
function mod2(uint64 $a) : uint64
{
    // a mod 2^i = a & (2^i–1)
    return _and($a, uint64::new(0, 1));
}
