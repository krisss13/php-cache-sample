<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Krisss\Printful\FileCache;
use Krisss\Printful\PrintfulApi;
use PHPUnit\Framework\MockObject\Exception as ExceptionAlias;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PrintfulApiTest extends TestCase
{
    /**
     * @throws ExceptionAlias
     */
    protected function setUp(): void
    {
        $clientMock = $this->createMock(Client::class);
        $cacheMock = $this->createMock(FileCache::class);
        $this->api = new PrintfulApi($cacheMock, $clientMock);
    }

    /**
     * Test Get Product And Size Tables Returns Data
     * @throws GuzzleException
     * @throws ExceptionAlias
     */
    public function testGetProductAndSizeTablesReturnsData()
    {
        $cache = $this->createMock(FileCache::class);
        $cache->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $cache->expects($this->once())
            ->method('set')
            ->willReturn(null);

        $api = new PrintfulApi($cache);

        $result = $api->getProductAndSizeTables(438, "L");

        $this->assertArrayHasKey('product', $result);
        $this->assertArrayHasKey('id', $result['product']);
        $this->assertArrayHasKey('title', $result['product']);
        $this->assertArrayHasKey('description', $result['product']);
        $this->assertArrayHasKey('size', $result);
        $this->assertIsArray($result['size']);

        foreach ($result['size'] as $sizeTable) {
            $this->assertArrayHasKey('type', $sizeTable);
            $this->assertArrayHasKey('unit', $sizeTable);
            $this->assertArrayHasKey('description', $sizeTable);
            $this->assertArrayHasKey('measurements', $sizeTable);
            $this->assertIsArray($sizeTable['measurements']);

            foreach ($sizeTable['measurements'] as $measurement) {
                $this->assertArrayHasKey('type_label', $measurement);
                $this->assertArrayHasKey('values', $measurement);
                $this->assertIsArray($measurement['values']);

                foreach ($measurement['values'] as $value) {
                    $this->assertArrayHasKey('size', $value);
                    $this->assertTrue(array_key_exists('value', $value) || (array_key_exists('min_value', $value) && array_key_exists('max_value', $value)));
                }
            }
        }

        $expectedResult = [
            'product' => [
                'id' => 123,
                'title' => 'Test Product',
                'description' => 'This is a test product',
            ],
            'size' => [
                [
                    'type' => 'type1',
                    'unit' => 'unit1',
                    'description' => 'Description 1',
                    'measurements' => [
                        [
                            'type_label' => 'Length',
                            'values' => [
                                ['size' => 'L', 'value' => '20'],
                            ],
                        ],
                        [
                            'type_label' => 'Width',
                            'values' => [
                                ['size' => 'L', 'value' => '10'],
                            ],
                        ],
                    ],
                ],
            ],
        ];


        $ErrorResult = $api->getProductAndSizeTables(-1, "L");


        $this->assertEquals($expectedResult, $ErrorResult);
    }
}