<?php declare(strict_types=1);

namespace App;

class Util
{
    /**
     * I can't figure out the difference between Excel Correlation and Pearson Correlation?
     * Figuring out this from the mathematical image from:
     * https://corporatefinanceinstitute.com/resources/excel/correl-function-correlation/
     *
     * @param int[] $x
     * @param int[] $y
     */
    public static function correlation(array $x, array $y): float
    {
        if (count($x) !== count($y)) {
            throw new \DomainException;
        }

        $length = count($x);
        if ($length === 0) {
            return 0;
        }

        $x = array_values($x);
        $y = array_values($y);

        $avgx = array_sum($x) / $length;
        $avgy = array_sum($y) / $length;

        $top = $bottomx = $bottomy = 0;
        for ($i = 0; $i < $length; $i++) {
            $minusx = $x[$i] = $avgx;
            $minusy = $y[$i] = $avgy;
            $top += $minusx * $minusy;
            $bottomx += pow($minusx, 2);
            $bottomy += pow($minusy, 2);
        }

        return $top / sqrt($bottomx * $bottomy);
    }
}
