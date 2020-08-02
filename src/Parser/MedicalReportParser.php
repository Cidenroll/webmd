<?php
/**
 * Created by PhpStorm.
 * User: Delz
 * Date: 4/25/2020
 * Time: 9:24 PM
 */

namespace App\Parser;


class MedicalReportParser extends AbstractParser
{
    private $analysisTextEngine1 = '';
    private $analysisTextEngine2 = '';
    private $details = [];

    private $sex;
    private $cnp;
    private $age;
    private $institute;
    private $datesString;
    private $diagnostic;
    private $highestValues =[];

    private $tags = [ 'activ', 'inactiv', 'cerebral', 'leziuni', 'leziune', 'nerv' , 'rmn' ];


    public function __construct(array $analysisText=[], array $detailsArr=[])
    {
        if (isset($analysisText['OCRSPACEV2'])) {
            $this->analysisTextEngine2 = $analysisText['OCRSPACEV2'];
        }
        if (isset($explodedText['OCRSPACEV1'])) {
            $this->analysisTextEngine1 = $analysisText['OCRSPACEV1'];
        }
        $this->details = $detailsArr;
    }

    public function process(): void
    {
        $sex = $cnp = $age = $institute = $datesString = $diagnostic = '';
        $highestValues = [];

        // GET DIAGNOSTIC
        $diagnostic = $this->analysisTextEngine2;

        $textEngine1Array = array_filter(explode("\n",$this->analysisTextEngine1));
        $textEngine2Array = array_filter(explode("\n",$this->analysisTextEngine2));

        // GET INSTITUTE
        if ($textEngine1Array) {
            $institute = trim($textEngine1Array[0]);
        }


        $this->analysisTextEngine1 = preg_replace("/\r\n|\t\n|\r|\t|\n/", ' ', $this->analysisTextEngine1);
        $this->analysisTextEngine2 = preg_replace("/\r\n|\t\n|\r|\t|\n/", ' ', $this->analysisTextEngine2);


        // CHECK SEX

        if (preg_match("/(?<gender>(\s+F\s+)|(\s+M\s+))/", $this->analysisTextEngine1, $matches)) {
            $sex = trim($matches['gender']);
        }
        if (!$sex && preg_match("/(?<gender>(\s+F\s+)|(\s+M\s+))/", $this->analysisTextEngine2, $matches)) {
            $sex = trim($matches['gender']);
        }

        // CHECK CNP

        if (preg_match("/(?<cnp>([0-9]{13}))/", $this->analysisTextEngine1, $matches)) {
            $cnp = trim($matches['cnp']);
        }
        if (!$cnp && preg_match("/(?<cnp>([0-9]{13}))/", $this->analysisTextEngine2, $matches)) {
            $cnp = trim($matches['cnp']);
        }

        // CHECK AGE

        if (preg_match("/(?<=:)(?<age>(\s*[0-9]{2})\s(?=ani))/", $this->analysisTextEngine1, $matches)) {
            $age = trim($matches['age']);
        }
        if (!$age && preg_match("/(?<=:)(?<age>(\s*[0-9]{2})\s(?=ani))/", $this->analysisTextEngine2, $matches)) {
            $age = trim($matches['age']);
        }

        // CHECK DATES

        if (preg_match_all("/(?<date>([\d]{2}\.\d{2}\.\d{4}))/", $this->analysisTextEngine1, $matches)) {
            $allDates = [];
            foreach ($matches[0] as $matchDate) {
                $allDates[] = trim($matchDate);
            }
            $datesString = implode('; ',$allDates);
        }
        if (!$datesString && preg_match_all("/(?<date>([\d]{2}\.\d{2}\.\d{4}))/", $this->analysisTextEngine2, $matches)) {
            $allDates = [];
            foreach ($matches[0] as $matchDate) {
                $allDates[] = trim($matchDate);
            }
            $datesString = implode('; ',$allDates);
        }

        if ($this->details && $this->details['CreationDate']) {
            $datesString .="\nData export: ".$this->details['CreationDate'];
        }

        // CHECK DIAGNOSTIC ENGINE 1

        $foundTagsE1 = [];
        foreach ($this->tags as $tag) {
            if (preg_match_all("/\b($tag)\b/",$this->analysisTextEngine1, $matchesE1)) {
                $foundTagsE1[$tag] = count($matchesE1);
            }
        }

        if ($foundTagsE1) {
            $highestValues['NEUROLOGIE'] = '';
            foreach ($foundTagsE1 as $tag => $count) {
                $highestValues[$tag] = $count;
            }
        }


        $this->diagnostic = $diagnostic;
        $this->sex = $sex;
        $this->age = $age;
        $this->cnp = $cnp;
        $this->highestValues = $highestValues;
        $this->institute = $institute;
        $this->datesString = $datesString;

    }


    /**
     * @return array
     */
    public function getAllData(): array
    {

        return [
            'sex'   =>  trim($this->sex),
            'cnp'   =>  trim($this->cnp),
            'age'   =>  trim($this->age),
            'institute' =>  trim($this->institute),
            'dates' =>  $this->datesString,
            'diagnostic'    =>  trim($this->diagnostic),
            'highestVals'   =>  $this->highestValues
        ];
    }
}