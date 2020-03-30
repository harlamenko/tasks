<?php

return function ($result) {
    $output = fopen('./output.txt','w');
    fwrite($output, $result);
    fclose($output);
};