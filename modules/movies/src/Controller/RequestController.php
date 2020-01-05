<?php
class RequestController
{
  public $params = [];
  private $url = 'https://www.omdbapi.com/?apikey=2adc02b9&type=movie&r=json';

  protected function consult(){
    $cURLConnection = curl_init();

    if (!empty($this->params)){
      foreach ($this->params as $key=>$value){
        $this->url .= "&{$key}={$value}";
      }
    }

    curl_setopt($cURLConnection, CURLOPT_URL, $this->url);
    curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

    $moviesList = curl_exec($cURLConnection);
    curl_close($cURLConnection);
    unset($this->params);

    return json_decode($moviesList, TRUE);
  }
}
