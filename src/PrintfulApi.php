<?php

namespace Krisss\Printful;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Krisss\Printful\FileCache;

class PrintfulApi
{
    /**
     * @var Client|null
     */
    private ?Client $client;

    /**
     * @var FileCache
     */
    private FileCache $cache;

    /**
     * @param FileCache $cache
     * @param Client|null $client
     */
    public function __construct(FileCache $cache, ?Client $client = null)
    {
        $this->cache = $cache;
        $this->client = $client ?? new Client(['base_uri' => 'https://api.printful.com/']);
    }

    /**
     * Get Product data by id and size list
     * @param int $productId
     * @param string $size
     * @return array
     * @throws GuzzleException
     */
    public function getProductAndSizeTables(int $productId, string $size): array
    {
        $cacheKey = 'product_' . $productId . '_size_' . $size;
        $cachedData = $this->cache->get($cacheKey);

        if ($cachedData !== null) {
            return $cachedData;
        }

        $productData = $this->getApiData('products/' . $productId);
        $productSizeData = $this->getApiData('products/'. $productId . '/sizes');

        if ($productData['code'] === 401 || $productSizeData['code'] === 404 ) {
            return $productData['error'];
        }

        $product = $productData['result']['product'];
        $productSize = $productSizeData['result']['size_tables'];

        $result = [
            'product' => [
                'id' => $product['id'],
                'title' => $product['title'],
                'description' => $product['description'],
            ],
            'size' => $this->filterProductSizeData($productSize, $size),
        ];
         $this->cache->set($cacheKey, $result, 300);

        return $result;
    }

    /**
     * Make a request to API and return response body
     * @param string $uri
     * @return array
     * @throws GuzzleException
     */
    public function getApiData(string $uri): array
    {
        $response = $this->client->get($uri);
        return json_decode($response->getBody(), true);
    }

    /**
     * Filter measurements arrays with required size
     * @param array $sizeList
     * @param $size
     * @return array
     */
    public function filterProductSizeData(array $sizeList, $size) :array
    {
        return array_map(function ($table) use ($size) {
            $table['measurements'] = array_map(function ($measurement) use ($size) {
                $measurement['values'] = array_filter($measurement['values'], function ($value) use ($size) {
                    return $value['size'] === $size;
                });
                return $measurement;
            }, $table['measurements']);
            return $table;
        }, $sizeList);
    }
}