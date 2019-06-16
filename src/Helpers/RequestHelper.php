<?php

namespace App\Helpers;


class RequestHelper
{

  private $defaultOptions = array(
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_MAXREDIRS => 1,
                      CURLOPT_TIMEOUT => 5,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_CUSTOMREQUEST => "GET"
          );
  public $autorizathionCode = '';
  public $timeToTakeANewCode = '';

  /*
  * Get the actual temperature for the informed city and return the correct genre
  */
  public function getTempGenreForCity($city){
    if(!$city)
      throw new \Exception("Informe uma cidade", 1);

    $curl = curl_init();
    $curl_options = $this->defaultOptions;
    $curl_options[CURLOPT_URL] = 'http://api.openweathermap.org/data/2.5/weather?appid=4a34c8224269dc94f65174c53e9cd2d6&q='.$city;

    curl_setopt_array($curl, $curl_options);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        return 0;
    }

    $data = json_decode($response, true);
    if ($data['cod'] != 200) {
      return false;
    }
    $actualTempInC = round(($data['main']['temp'] - 273.15));
    if ($actualTempInC > 30) {
        $genre = "party";
    } elseif (($actualTempInC >= 21) && ($actualTempInC <= 30)) {
        $genre = "hip hop";
    } elseif (($actualTempInC >= 15) && ($actualTempInC <= 20)) {
        $genre = "classical";
    } else {
        $genre = "rock";
    }

    return array('temp' => $actualTempInC,'genre' => $genre);
  }

  /*
  * Get the songs for the informed genre
  */
  public function getSongsForGenre($genre){
    if(!$genre)
      throw new \Exception("Informe um gênero", 1);

    $autorizathion = self::getSpotifyAuthorization();
    $curl = curl_init();
    $curl_options = $this->defaultOptions;
    $genre = urlencode("genre:$genre");
    $curl_options[CURLOPT_URL] = "https://api.spotify.com/v1/search?q=$genre&type=track&limit=50";
    $curl_options[CURLOPT_HTTPHEADER] = array(
                                        'Accept: application/json',
                                        'Content-Type: application/json',
                                        "Authorization: Bearer $autorizathion"
                                    );
    curl_setopt_array($curl, $curl_options);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        return 0;
    }

    $response = json_decode($response, true);
    if(!isset($response['tracks']['items']))
      throw new \Exception("Tente novamente", 1);
    $data = $response['tracks']['items'];
    $results = array();
    if ($data) {
        foreach($data AS $k=>$p) {
          $results[] = array(
            'name' => $p['name'],
            'artist' => $p['album']['artists'][0]['name'],
            'image' => $p['album']['images'][0]['url'],
          );
        }
    }

    return $results;
  }

  public function getSpotifyAuthorization(){
    //The Spotify provide a token that expires in one hour
    //To keep it simple and fast, we just create a new one if has already passed at least 50 minutes
    if ($this->timeToTakeANewCode && $this->timeToTakeANewCode > date("h:i:s")) {
      return $this->autorizathionCode;
    }

    $client_id = 'fa9d9ab624874777bc01391a376e457d';
    $client_secret = '83e589b2078149769441ee4517d618c9';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL,            'https://accounts.spotify.com/api/token' );
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($curl, CURLOPT_POST,           1 );
    curl_setopt($curl, CURLOPT_POSTFIELDS,     'grant_type=client_credentials' );
    curl_setopt($curl, CURLOPT_HTTPHEADER,     array('Authorization: Basic '.base64_encode($client_id.':'.$client_secret)));

    $result=curl_exec($curl);
    $result = json_decode($result);

    $this->autorizathionCode = $result->access_token;
    $this->timeToTakeANewCode = date("h:i:s", time() + 50*60);

    return $this->autorizathionCode;
  }
}
