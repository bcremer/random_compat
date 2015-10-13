<?php
/**
 * Random_* Compatibility Library 
 * for using the new PHP 7 random_* API in PHP 5 projects
 * 
 * The MIT License (MIT)
 * 
 * Copyright (c) 2015 Paragon Initiative Enterprises
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 * Fetch a random integer between $min and $max inclusive
 * 
 * @param int $min
 * @param int $max
 * 
 * @throws Exception
 * 
 * @return int
 */
function random_int($min, $max)
{
    /**
     * Type and input logic checks
     */
    if (!is_numeric($min)) {
        throw new TypeError(
            'random_int(): $min must be an integer'
        );
    }
    if (!is_numeric($max)) {
        throw new TypeError(
            'random_int(): $max must be an integer'
        );
    }

    $min = (int) $min;
    $max = (int) $max;

    if ($min > $max) {
        throw new Error(
            'Minimum value must be less than or equal to the maximum value'
        );
    }
    if ($max === $min) {
        return $min;
    }
    /**
     * At this point, $range is a positive number greater than 0. It might
     * overflow, however, if $max - $min > PHP_INT_MAX. PHP will cast it to
     * a float and we will lose some precision.
     */
    $range = $max - $min + 1;
    
    $int = 0;
    /**
     * Test for integer overflow:
     */
    if (!is_int($range)) {
        /**
         * As long as we generate a random number, it should be between
         * $min and $max.
         */
        if (PHP_INT_SIZE === 8) {
            $range0 = PHP_INT_MAX;
            $bits0 = 0;
            
            while ($range > 0) {
                $range >>= 1;
                $bits0++;
            }
            
            $bits = $bits0;
            $r = \Sodium\randombytes_uniform(0x40000000);
            $bits_comp = (int) min($bits, 30);
            $mask = 0x3fffffff >> (30 - $bits_comp);
            $int = ($r & $mask);
            $bits -= $bits_comp;

            $r = \Sodium\randombytes_uniform(0x40000000);
            $bits_comp = (int) min($bits, 30);
            $mask = 0x3fffffff >> (30 - $bits_comp);
            $int |= ($r & $mask) << 30;
            $bits -= $bits_comp;

            $r = \Sodium\randombytes_uniform(0x40000000);
            $mask = 0x3fffffff >> (30 - $bits);
            $int |= ($r & $mask) << 60;
        } else {
            // 32-bit
            $int = \Sodium\randombytes_uniform(2147483647) + $min;
        }
    } elseif ($range > 2147483647) {
        // $range is still an int (PHP_INT_SIZE === 8),
        // but randombytes_uniform() only accepts up to 2147483647
        
        $range0 = $range;
        $bits0 = 0;
        
        while ($range > 0) {
            $range >>= 1;
            $bits0++;
        }
        
        do {
            $bits = $bits0;
            $r = \Sodium\randombytes_uniform(0x40000000);
            $bits_comp = (int) min($bits, 30);
            $mask = 0x3fffffff >> (30 - $bits_comp);
            $int = ($r & $mask);
            $bits -= $bits_comp;

            $r = \Sodium\randombytes_uniform(0x40000000);
            $bits_comp = (int) min($bits, 30);
            $mask = 0x3fffffff >> (30 - $bits_comp);
            $int |= ($r & $mask) << 30;
            $bits -= $bits_comp;

            $r = \Sodium\randombytes_uniform(0x40000000);
            $mask = 0x3fffffff >> (30 - $bits);
            $int |= ($r & $mask) << 60;
        } while ($int > $range0);

        $int += $min;
    } else {
        $int = \Sodium\randombytes_uniform($range) + $min;
    }
    return $int;
}
