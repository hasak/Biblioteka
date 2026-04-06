<?php
/**
 * Created by hasak on 05.04.26 @ 23:26
 **/

function countryCodeToFlag($countryCode){
    $code = strtoupper($countryCode);
    $flag = implode('', array_map(
        fn($char) => mb_chr(0x1F1E6 + ord($char) - ord('A')),
        str_split($code)
    ));
    return $flag;
}
