<?php

use Illuminate\Support\Facades\DB;

 

if (!function_exists('number_format_indian')) {
    function number_format_indian($number, $decimals = 0)
    {
        $number = sprintf("%." . $decimals . "f", $number);
        $parts = explode('.', $number);

        $integer = $parts[0];
        $decimal = $parts[1] ?? '';

        $lastThree = substr($integer, -3);
        $rest = substr($integer, 0, -3);

        if ($rest !== '') {
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $integer = $rest . ',' . $lastThree;
        }

        return $decimal ? $integer . '.' . $decimal : $integer;
    }
}

if (!function_exists('numberToWords')) {
    function numberToWords($number)
    {
        $no = floor($number);
        $decimal = round($number - $no, 2) * 100;

        $words = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
            5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen',
            14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen',
            17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen',
            20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty',
            50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
            80 => 'Eighty', 90 => 'Ninety'
        ];

        $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];
        $str = [];
        $i = 0;

        while ($no > 0) {
            $divider = ($i == 1) ? 10 : 100;
            $numberPart = $no % $divider;
            $no = floor($no / $divider);

            if ($numberPart) {
                $plural = (($numberPart > 9) && ($i > 0)) ? '' : '';
                $hundred = ($i == 1 && $str) ? ' and ' : '';
                $str[] = ($numberPart < 21)
                    ? $words[$numberPart] . ' ' . $digits[$i] . $plural . $hundred
                    : $words[floor($numberPart / 10) * 10] . ' ' . $words[$numberPart % 10] . ' ' . $digits[$i] . $plural . $hundred;
            }

            $i++;
        }

        $result = implode(' ', array_reverse($str));

        if ($decimal) {
            $result .= ' and ' . numberToWordsIndian($decimal) . ' Paise';
        }

        return trim($result);
    }
}
