<?php

class GatheringInf
{
    static $urlSite = "https://search.ipaustralia.gov.au"; // domen site
    private $argConsole;
    private $resultArrRow = [];

    public function __construct($argConsole)
    {
        $this->argConsole = $argConsole;
    }

    public function buildQuery($token)
    {
        $data = array(
            '_csrf' => $token,
            'wv[0]' => $this->argConsole,
        );
        $fields = http_build_query($data);
        return $fields;
    }

    public function getCsrfToken($dom)
    {
        $tags = $dom->getElementsByTagName('input');
        for ($i = 0; $i < $tags->length; $i++) {
            $grab = $tags->item($i);
            if ($grab->getAttribute('name') === '_csrf') {
                $token = $grab->getAttribute('value');
            }
        }
        return $token;
    }

    public function getCountPage($dom)
    {
        $countResultPage = $dom->getElementsByTagName("a");
        for ($i = 0; $i < $countResultPage->length; $i++) {
            if ($countResultPage->item($i)->getAttribute('title') === "Last page") {
                $countResultPage = $countResultPage->item($i)->getAttribute('href');
                if (empty($countResultPage)) {
                    $countResultPage = 0;
                    break;
                } elseif (!empty($countResultPage)) {
                    $countResultPage = substr($countResultPage, -1);
                    break;
                }
            }
        }
        return $countResultPage;
    }

    //get URN for construct URI with delete last symbols (number page)
    public function getUrlWithoutPagination($dom)
    {
        $tagsUrl = $dom->getElementsByTagName('a');

        for ($i = 0; $i < $tagsUrl->length; $i++) {
            $grab = $tagsUrl->item($i);
            if ($grab->getAttribute('data-gotopage') === '0') {
                $urlFirstPage = $grab->getAttribute('href');
                $urlTempPage = mb_substr($urlFirstPage, 0, -1);
                break;
            }
        }
        return $urlTempPage;
    }

    public function constructUriPageWithoutPagin($dom, GatheringInf $obj)
    {
        $urnPage = $obj->getUrlWithoutPagination($dom);
        $uriPage = self::$urlSite . $urnPage;
        return $uriPage;
    }

    public function getResultArrRow($dom, $countResultPage, $uriPage, SearchRequest $obj, GatheringInf $infData)
    {
        for ($k = 0; $k <= $countResultPage; $k++) {
            $page = $uriPage . $k;
            $dom = $obj->requestGetPage($dom, $page);
            $this->resultArrRow = array_merge($this->resultArrRow, $infData->getDataPageTable($dom, self::$urlSite));
        }
        return $infData->jsonEncodeAndClearData($this->resultArrRow);
    }

    public function getDataPageTable($dom, $urlSite)
    {
        $arrIdPage = []; // arr for Id page
        $arrDetailsPage = []; // arr for link Details page
        $arrNumber = []; // arr for number records
        $arrLogoUrl = []; // arr for link image
        $arrName = []; // arr for link image
        $arrClasses = []; // arr for classes 
        $arrStatus = []; // arr for status 
        $arrResult = []; // arr with result request 


        $rows = $dom->getElementsByTagName("tr");
        for ($i = 1; $i < $rows->length; $i++) {
            $arrDetailsPage[$i] = stristr($urlSite . $rows->item($i)->getAttribute('data-markurl'), '?', true);
            $cols = $rows->item($i)->getElementsbyTagName("td");
            for ($j = 0; $j < $cols->length; $j++) {

                if ($cols->item($j)->getAttribute('class') === 'col c-5 table-index ') {
                    $arrIdPage[$i] = $cols->item($j)->nodeValue;
                } elseif ($cols->item($j)->getAttribute('class') === 'number') {
                    $arrNumber[$i] = $cols->item($j)->nodeValue;
                } elseif ($cols->item($j)->getAttribute('class') === 'trademark image' && $j == 3) {
                    $colImg = $cols->item($j)->getElementsbyTagName("img");
                    $arrLogoUrl[$i] = $colImg->item(0)->getAttribute("src");
                } elseif ($cols->item($j)->getAttribute('class') !== 'trademark image' && $j == 3) {
                    $arrLogoUrl[$i] = "Not URL image";
                    if ($cols->item($j)->getAttribute('class') === 'trademark words') {
                        $arrName[$i] = $cols->item($j)->nodeValue;
                    }
                } elseif ($cols->item($j)->getAttribute('class') === 'trademark words' && $j == 4) {
                    $arrName[$i] = $cols->item($j)->nodeValue;
                } elseif ($cols->item($j)->getAttribute('class') === 'classes ') {
                    $arrClasses[$i] = $cols->item($j)->nodeValue;
                } elseif ($cols->item($j)->getAttribute('class') === 'status') {
                    $arrStatus[$i] = $cols->item($j)->nodeValue;
                }
            }
            $arrResult[] = [
                $arrIdPage[$i]=>[
                    'number' => $arrNumber[$i],
                    'logo_url' => $arrLogoUrl[$i],
                    'name' => $arrName[$i],
                    'classes' => $arrClasses[$i],
                    'status1' => $arrStatus[$i],
                    'details_page_url' => $arrDetailsPage[$i],
                ]
            ];
        }
        return $arrResult;
    }

    public function jsonEncodeAndClearData($resultArrRow)
    {
        $resultArrRow = json_encode($resultArrRow, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); // JSON_UNESCAPED_SLASHES,
        // $resultArrRow = json_encode($resultArrRow, JSON_PRETTY_PRINT);// JSON_UNESCAPED_SLASHES,

        $resultArrRow = str_replace(array('\n', '\u25cf'), '', $resultArrRow);


        return $resultArrRow;
    }
}
