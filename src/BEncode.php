<?php

namespace Rickfo\BEncode;

use PHPUnit\Runner\Exception;


/**
 * Class BEncode
 * @package Rickfo\BEncode
 */
class BEncode
{

    /**
     * @param $input
     * @return string
     */
    public static function build($input)
    {
        if (is_int($input)) {
            return self::makeInt($input);
        }
        if (is_string($input)) {
            return self::makeString($input);
        }
        if (is_array($input)) {
            if (self::isDictionary($input)) {
                return self::makeDictionary($input);
            } else {
                return self::makeList($input);
            }
        }
        throw new InvalidValueException();
    }

    /**
     * @param $input
     * @param int $i
     * @return mixed
     */
    public static function decode($input, &$i = 0)
    {
        switch ($input[$i]) {
            case 'd':
                $dictionary = array();
                while (isset($input[++$i])) {
                    if ($input[$i] == 'e') {
                        return $dictionary;
                    } else {
                        $key = self::decode($input, $i);
                        if (isset($input[++$i])) {
                            $dictionary[$key] = self::decode($input, $i);
                        }
                    }
                }
                break;
            case 'l':
                $list = array();
                while (isset($input[++$i])) {
                    if ($input[$i] == 'e') {
                        return $list;
                    } else {
                        $list[] = self::decode($input, $i);
                    }
                }
                break;
            case 'i':
                $intBuffer = "";
                while (isset($input[++$i])) {
                    if ($input[$i] == 'e') {
                        return intval($intBuffer);
                    } elseif (ctype_digit($input[$i])) {
                        $intBuffer .= $input[$i];
                    } else {
                        throw new Exception('Could not find end of int at position: ' . $i);
                    }
                }
                break;
            case ctype_digit($input[$i]):
                $length = $input[$i];
                while (isset($input[++$i])) {
                    if ($input[$i] == ':') {
                        break;
                    } elseif (ctype_digit($input[$i])) {
                        $length .= $input[$i];
                    }
                }
                $stop = $i + intval($length);
                $string = "";
                while (isset($input[++$i])) {
                    if ($i <= $stop) {
                        $string .= $input[$i];
                        if ($i == $stop) {
                            return $string;
                        }
                    }
                }
                break;
        }
        throw new Exception('Failed to parse string at position: ' . $i);
    }

    /**
     * @param string $string
     * @return string
     */
    private static function makeString(string $string)
    {
        return strlen($string) . ':' . $string;
    }

    /**
     * @param int $int
     * @return string
     */
    private static function makeInt(int $int)
    {
        if (is_int($int)) {
            return 'i' . $int . 'e';
        }
        throw new Exception('Excpected an int');
    }

    /**
     * @param $list
     * @return string
     */
    private static function makeList($list)
    {
        $retString = 'l';
        foreach ($list as $item) {
            if (is_array($item)) {
                if (self::isDictionary($item)) {
                    $retString .= self::makeDictionary($item);
                } else {
                    $retString .= self::makeList($item);
                }
                continue;
            }
            if (is_int($item)) {
                $retString .= self::makeInt($item);
                continue;
            }
            if (is_string($item)) {
                $retString .= self::makeString($item);
                continue;
            }
            throw new InvalidValueException();
        }
        return $retString .= 'e';
    }

    /**
     * @param $dictionary
     * @return string
     */
    private static function makeDictionary($dictionary)
    {
        $retString = 'd';
        foreach ($dictionary as $key => $item) {
            $retString .= self::makeString($key);
            if (is_array($item)) {
                if (self::isDictionary($item)) {
                    $retString .= self::makeDictionary($item);
                } else {
                    $retString .= self::makeList($item);
                }
                continue;
            }
            if (is_int($item)) {
                $retString .= self::makeInt($item);
                continue;
            }
            if (is_string($item)) {
                $retString .= self::makeString($item);
                continue;
            }
            throw new InvalidValueException();
        }
        return $retString .= 'e';
    }

    /**
     * @param $dictionary
     * @return bool
     */
    private static function isDictionary($dictionary)
    {
        $i = 0;
        foreach ($dictionary as $key => $item) {
            if ($key !== $i++) {
                return true;
            }
        }
        return false;
    }
}

/**
 * Class InvalidValueException
 * @package Rickfo\BEncode
 */
class InvalidValueException extends Exception
{
    protected $message = 'Invalid value: Acceptable array, int and string';
}
