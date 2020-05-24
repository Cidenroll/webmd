<?php


namespace App\Parser;


abstract class AbstractParser
{
    public const REGINA_MARIA = 'Regina Maria';
    public const SANADOR = 'Sanador';
    public const LOUIS_TURCANU = 'Spitalul Clinic de Urgenta pentru copii `Louis Turcanu`';

    abstract public function process();

}