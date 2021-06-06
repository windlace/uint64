uint64
---
**unsigned integer 64-bit**

#### Install:
```php
composer require cast/uint64
```

#### Usage:
```php
<?php
use Cast\Crypto\unit64\Uint64 as uint64;
use function Cast\Crypto\unit64\add;

$a = new uint64("4f3f9abb2e7c8889");
$b = new uint64("68825a36ac081669");
$c = add($a, $b);
$c->hilo(); // 3082941681, 3666124530
$c->getHex(); // b7c1f4f1da849ef2

```

Based on https://github.com/shift-reality/php-crypto/blob/newlib/src/Util/UnsignedInt64.php

####Warninng!
It has no the integer overflow protection.\
```
Overflow:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$a      :  4aa537e02d4ff2d7 :  5378766765936341719 :   0100101010100101 0011011111100000 0010110101001111 1111001011010111
$b      :  e8c2a9e22698b38b : 16772154751056393099 :   1110100011000010 1010100111100010 0010011010011000 1011001110001011
add()   :  3367e1c253e8a662 :  3704177443283183202 :   0011001101100111 1110000111000010 0101001111101000 1010011001100010
gmp_add : 13367e1c253e8a662 : 22150921516992734818 : 1 0011001101100111 1110000111000010 0101001111101000 1010011001100010
-> - - - - - - - - - - - - - - - - - - - - - - - - - ^
```

**Note**: Use `composer require cast/base-convert` to get a decimal representation of an uint64 number.


Links:
* https://github.com/shift-reality/php-crypto
