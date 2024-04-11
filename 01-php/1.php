<?php

    $anArray['first'] = 1;
    $anArray['second'] = 2;
    $anArray['third'] = 3;

    $anArray[123] = 4;

    $anArray[] = 5;

    foreach($anArray as $k => $v) {
        echo "<strong>{$k}</strong>: {$v} <br>\n";
    }


?>