<?php

$write_result = require '../utils/write_result.php';

function get_pattern($matches) {
    $range = $matches['from'] || $matches['to'] ?
        implode('|', range($matches['from'], $matches['to'])) : '';
    $patterns = [
        'S' => "/^<[\w\s]{{$matches['from']},{$matches['to']}}>$/ix",
        'N' => "/^<{$range}>$/",
        'P' => "/^<\+\d\s\(\d{3}\)\s\d{3}\-\d{2}\-\d{2}>$/",
        'D' => "/^<(\d{2}\.){2}\d{4}\s\d{2}\:\d{2}>$/",
        'E' => "/^<[^\_\W]\w{3,29}\@[a-zA-Z]{2,30}\.[a-z]{2,10}>$/",
    ];
    return $patterns[$matches['pattern_type']];
}

function main() {
    global $write_result;
    $file = fopen('./input.txt','r') or die("не удалось открыть файл с входными данными");
    $line_pattern = "/^(?'value'<.*>)\s+(?'pattern_type'[S|N|P|D|E])\s*(?'from'\d*)\s*(?'to'\d*)$/";
    $results = [];

    while ($line = trim(fgets($file))) {
        preg_match($line_pattern, $line, $matches);
        if (!$matches) { continue; }
        
        $pattern = get_pattern($matches);
        $results[] = preg_match($pattern, $matches['value']) ? 'OK' : 'FAIL';
    }
    
    fclose($file);
    $write_result(implode("\n", $results));
}

main();