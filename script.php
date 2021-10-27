<?php

require_once "searchRequest.php";
require_once "gatheringInf.php";

if (isset($argv[1])) {
    $objRequest = new SearchRequest();
    $objGathering = new GatheringInf($argv[1]);
    $resultArrRow = [];

    $getHtmlPage = $objRequest->getHtmlPage();
    $dom = new DOMDocument();
    @$dom->loadHTML($getHtmlPage);

    $token = $objGathering->getCsrfToken($dom);
    $fields = $objGathering->buildQuery($token);

    $responsePost = $objRequest->requestPost($fields);
    @$dom->loadHTML($responsePost);
   

    $countResultPage = $objGathering->getCountPage($dom);

    $uriPage = $objGathering->constructUriPageWithoutPagin($dom, $objGathering);


    $resultArrRow = $objGathering->getResultArrRow($dom, $countResultPage, $uriPage, $objRequest, $objGathering);
    print($resultArrRow);

} elseif (!isset($argv[1])) {
    print("Input required parameter for search!!!");
}