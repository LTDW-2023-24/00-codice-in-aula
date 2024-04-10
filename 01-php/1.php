<?php

    Header("Content-type: text/plain");

    $anArray['first'] = 1;
    $anArray['second'] = 2;
    $anArray['third'] = 3;

    foreach($anArray as $k => $v) {
        echo "<strong>{$k}</strong>: {$v} <br>\n";
    }


?>