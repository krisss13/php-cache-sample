<?php

require_once 'vendor/autoload.php';

use GuzzleHttp\Exception\GuzzleException as GuzzleExceptionAlias;
use Krisss\Printful\FileCache;
use Krisss\Printful\PrintfulApi;

$fileCache = new FileCache('cache');
$printFullAPi =  new PrintfulApi($fileCache);
try {
    $product = $printFullAPi->getProductAndSizeTables(-1, 'L');

    if (!empty($product['error'])) {
        print_r($product['error']) ;
    } else {
        echo '<style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f2f2f2;
        }
    </style>';

        echo '<h2>Product Details</h2>';
        echo '<table>';
        echo '<tr><th>Product ID</th><th>Title</th><th>Description</th></tr>';
        echo '<tr><td>' . $product['product']['id'] . '</td><td>' . $product['product']['title'] . '</td><td>' . $product['product']['description'] . '</td></tr>';
        echo '</table>';

        echo '<h2>Size Tables</h2>';
        echo '<table>';
        echo '<tr><th>Type</th><th>Unit</th><th>Description</th><th>Measurements</th></tr>';

        foreach ($product['size'] as $table) {
            echo '<tr>';
            echo '<td>' . $table['type'] . '</td>';
            echo '<td>' . $table['unit'] . '</td>';
            echo '<td>' . $table['description'] . '</td>';

            $measurements = [];
            foreach ($table['measurements'] as $measurement) {
                foreach ($measurement['values'] as $value) {
                    $measurements[] = !empty($value['value'])
                        ? $measurement['type_label'] . ': ' . $value['value']
                        : $measurements[] = $measurement['type_label'] . ': ' . $value['min_value'] . ' - ' . $value['max_value'];
                }
            }
            echo '<td>' . implode(', ', $measurements) . '</td>';

            echo '</tr>';
        }
        echo '</table>';
    }

} catch (GuzzleExceptionAlias $e) {
    echo $e->getMessage();
}

