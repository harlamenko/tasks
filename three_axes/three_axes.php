<?php

$write_result = require '../utils/write_result.php';

function parse_lines($file) {
    $lines_count = fgets($file);
    $lines = [];

    for ($i=0; $i < $lines_count; $i++) { 
        $lines[] = array_map('trim', explode(' ', fgets($file)));
    }
    
    return $lines;
}

function write_result($result) {
    $output = fopen('./output.txt','w');
    fwrite($output, $result);
    fclose($output);
}

function humanize_bet_info($bet) {
    $new_bet = [];
    
    $new_bet['id'] = $bet[0];
    $new_bet['money'] = $bet[1];
    $new_bet['winner'] = $bet[2];

    return $new_bet;
}

function humanize_match_info($match) {
    $new_match = [];

    $new_match['coefs']['L'] = $match[1];
    $new_match['coefs']['R'] = $match[2];
    $new_match['coefs']['D'] = $match[3];
    $new_match['winner'] = $match[4];
    
    return $new_match;
}

function main() {
    global $write_result;

    $file = fopen('./input.txt','r') or die("не удалось открыть файл с входными данными");
    $money = 0;
    $bets = parse_lines($file);
    $matches = parse_lines($file);

    foreach ($bets as $bet) {
        $bet = humanize_bet_info($bet);
        
        $match_id = array_search($bet['id'], array_column($matches, 0));
        $match = $matches[$match_id];

        $match = humanize_match_info($match);
        
        $money_after_win = $bet['money'] * $match['coefs'][$match['winner']];
        $money += ($bet['winner'] == $match['winner'] ? $money_after_win : 0) - ($bet['money']);
    }

    write_result($money);
    
    fclose($file);
}

main();