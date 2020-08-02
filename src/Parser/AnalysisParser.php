<?php
/**
 * Created by PhpStorm.
 * User: Delz
 * Date: 4/24/2020
 * Time: 6:44 PM
 */

namespace App\Parser;


class AnalysisParser extends AbstractParser
{
    private $analysisText = [];
    private $details = [];

    private $sex = "";
    private $cnp = "";
    private $age= "";
    private $institute = "";
    private $datesString = "";
    private $diagnostic;
    private $highestValues =[];


    public function __construct(array $analysisText = [], array $detailsArr=[])
    {
        $this->details = $detailsArr;
        foreach ($analysisText as $engine => $text) {
            $new_string = preg_replace("/\r\n|\t\n|\r|\t|\n/", ' ', $text);
            $this->analysisText[$engine] = $new_string;
        }
    }

    public function process()
    {
        foreach ($this->analysisText as $engine => $text ) {

            if (preg_match("/(?<gender>(\s+F\s+)|(\s+M\s+))/", $text, $matches)) {
                if (!$this->sex) {
                    $this->sex = $matches['gender'];
                }
            }

            if (preg_match("/(?<cnp>([0-9]{13}))/", $text, $matches)) {
                if (!$this->cnp) {
                    $this->cnp = $matches['cnp'];
                }
            }

            if (preg_match("/(?<=:)(?<age>(\s*[0-9]{2})\s(?=ani))/", $text, $matches)) {
                if (!$this->age) {
                    $this->age = $matches['age'];
                }
            }

            if (preg_match("/\d+(?=\s)(?=.*SRL)(?<inst>(.*)(?>SRL))/", $text, $matches)) {
                $matchedString = $matches['inst'];
                $matchedString = preg_replace('/[0-9]*/','',$matchedString);
                $matchedString = preg_replace('/[^a-z]+/i', ' ', $matchedString);
                if (!$this->institute) {
                    $this->institute = $matchedString;
                }
            }

            if (preg_match_all("/(?<=Data)(.*?)(?>: )(?<date>([\d]{2}.\d{2}.\d{4}))((\s-\s\d{2}:\d{2})?)/", $text, $matches)) {
                $allDates = [];
                foreach ($matches[0] as $matchDate) {
                    $allDates[] = sprintf('Data %s', trim($matchDate));
                }
                if (!$this->datesString) {
                    $this->datesString = implode(" - ", $allDates);
                }
            }

            if (preg_match("/".self::REGINA_MARIA."/", $this->institute)) {
                $values = [];
                if (!$this->diagnostic && preg_match("/(?<=HEMATOLOGIE)(.*)(?>BIOCHIMIE)/", $text, $matches)) {
                    $this->diagnostic = 'HEMATOLOGIE '.trim($matches[0]);

                    if (preg_match_all("/(?<=\[)((?<min>(\d{1,3}(.\d{1,})?))\s-\s(?<max>(\d{1,3}(.\d{1,})?)))(?=\])|(?<absolute>(<\d{1,}))/", $this->diagnostic, $limitValues)) {

                        preg_match_all("/(?<actual>(\s+\=\s*\d{1,}(.\d{1,})?))/", $this->diagnostic, $actualValues);
                        preg_match_all("/((?<=\])(.*?)(?=\=))|((?<=\<)(?<specName>(.*?))(?=\=))/", $this->diagnostic, $actualNames);

                        $actVals = $actNames = [];

                        foreach ($actualValues[0] as $actVal) {
                            $actVals[] = trim(str_replace("=", "", $actVal));
                        }
                        foreach ($actualNames[0] as $actName) {
                            $actNames[] = trim(preg_replace('/\d{1,}/', '', $actName));
                        }

                        $i = 0;
                        foreach ($limitValues[0] as $matchedVal) {
                            if (strpos($matchedVal, "<") !== false) {
                                $rangeArr = explode('<', $matchedVal);
                                $values[$i]['min'] = '0';
                                $values[$i]['max'] = $rangeArr[1];
                                $values[$i]['actual'] = $actVals[$i];
                                $values[$i]['name'] = $actNames[$i];
                            } elseif (strpos($matchedVal, ">") !== false) {
                                $rangeArr = explode('>', $matchedVal);
                                $values[$i]['min'] = $rangeArr[0];
                                $values[$i]['max'] = '10000';
                                $values[$i]['actual'] = $actVals[$i];
                                $values[$i]['name'] = $actNames[$i];
                            } else {
                                $rangeArr = explode(' - ', $matchedVal);
                                $values[$i]['min'] = $rangeArr[0];
                                $values[$i]['max'] = $rangeArr[1];
                                $values[$i]['actual'] = $actVals[$i];
                                $values[$i]['name'] = $actNames[$i];
                            }
                            $i++;
                        }
                    }
                }

                if (!$this->highestValues) {
                    $outOfBoundsValues = array_filter($values, function ($elem, $key){
                        if ($elem['actual'] < $elem['min'] or $elem['actual'] > $elem['max']) {
                            return true;
                        }
                    }, ARRAY_FILTER_USE_BOTH);


                    foreach ($outOfBoundsValues as $vals) {
                        $this->highestValues[$vals['name']] = $vals['actual'];
                    }
                }
            }
            else {
                if (!$this->diagnostic) {
                    $this->diagnostic = $text;
                }
            }

        }
    }

    public function getAllData()
    {
        return [
            'sex'   =>  trim($this->sex),
            'cnp'   =>  trim($this->cnp),
            'age'   =>  trim($this->age),
            'institute' =>  trim($this->institute),
            'dates' =>  $this->datesString,
            'diagnostic'    =>  $this->diagnostic,
            'highestVals'   =>  $this->highestValues
        ];
    }

}