<?php

use Rickfo\BEncode\BEncode;
use PHPUnit\Framework\TestCase;

class BEncodeTest extends TestCase
{

    public function testInvalidDictionaryDecode()
    {
        $invalidString = 'd1:05:dolor3:key4:data1:25:Lorem5:Ipsume';
        $expected = 'Failed to parse string at position: 39';

        try {
            $result = BEncode::decode($invalidString);

            $this->fail('No exception thrown');
        } catch (Exception $e) {
            $this->assertEquals($expected, $e->getMessage());
        }
    }

    public function testDictionaryDecode()
    {
        $testString = 'd1:05:dolor3:key4:data1:2i42e5:Lorem5:Ipsume';
        $expected = [
            0 => 'dolor',
            'key' => 'data',
            2 => 42,
            'Lorem' => 'Ipsum'
        ];

        try {
            $result = BEncode::decode($testString);

            $this->assertEquals(sizeof($expected), sizeof($result));

            foreach ($expected as $key => $value) {
                $this->assertArrayHasKey($key, $result);
                $this->assertEquals($value, $result[$key]);
            }
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testBuildDictionary()
    {
        $expected = 'd1:05:dolor3:key4:data1:2i42e5:Lorem5:Ipsume';
        $testData = [
            0 => 'dolor',
            'key' => 'data',
            2 => 42,
            'Lorem' => 'Ipsum'
        ];

        try {
            $result = BEncode::build($testData);

            $this->assertEquals($expected, $result);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testBuildInvalidList()
    {
        $expected = 'Invalid value: Acceptable array, int and string';
        $testData = [2, 5, 2, 'Lorem', 2.42, '23.62'];

        try {
            $result = BEncode::build($testData);

            $this->fail('No exception thrown');
        } catch (Exception $e) {
            $this->assertEquals($expected, $e->getMessage());
        }
    }
}
