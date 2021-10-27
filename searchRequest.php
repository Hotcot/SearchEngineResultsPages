<?php

class SearchRequest
{
    static $url = "https://search.ipaustralia.gov.au/trademarks/search/advanced"; // url site
    static $urlPost = "https://search.ipaustralia.gov.au/trademarks/search/doSearch"; // url for make post request site
    static $userAgent = 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.54 Mobile Safari/537.36 Edg/95.0.1020.30';


    public function __construct()
    {
    }

    public function getHtmlPage()
    {
        $ch = curl_init(self::$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, realpath('cookie.txt'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, self::$userAgent);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function requestPost($fields) // make a POST request to the site form with the following request parameters 
    {
        $chPost = curl_init();
        curl_setopt($chPost, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chPost, CURLOPT_URL, self::$urlPost);
        curl_setopt($chPost, CURLOPT_POST, 1);
        curl_setopt($chPost, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($chPost, CURLOPT_COOKIEFILE, realpath("cookie.txt"));
        curl_setopt($chPost, CURLOPT_USERAGENT, self::$userAgent);
        curl_setopt($chPost, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($chPost, CURLOPT_SSL_VERIFYPEER, 0); //for console
        curl_setopt($chPost, CURLOPT_SSL_VERIFYHOST, 0); //for console    

        $response = curl_exec($chPost);
        curl_close($chPost);
        return $response;
    }

    public function requestGetPage($dom, $page)
    {
        $ch = curl_init($page);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, self::$userAgent);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        @$dom->loadHTML($response);
        return $dom;
    }
}