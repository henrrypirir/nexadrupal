<?php
namespace Drupal\movies\Controller;
include_once ('RequestController.php');
use Drupal\node\Entity\Node;

class MovieController extends \RequestController
{
  protected $items = [];
  protected $current_uid;
  private $id;
  public $image, $title, $year, $rating, $description;

  public function __construct()
  {
    $this->current_uid = \Drupal::currentUser()->id();
  }

  protected function print_json($data){
    header('Content-Type: application/json');
    echo json_encode($data);
  }

  private function insert_movie(){
    $node = Node::create([
      'type'        => 'pelicula',
      'title'       => $this->title,
      'field_imagen' => $this->image,
      'field_ano' => $this->year,
      'field_rating' => $this->rating,
      'field_id' => $this->id,
      'body' => $this->description
    ]);

    $node->save();
  }

  private function validate_favorite(){
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('field_id', $this->id)
      ->condition('type', 'pelicula')
      ->condition('uid', $this->current_uid);
    $nids = $query->execute();

    return empty($nids);
  }

  public function seach(){
    if (isset($_POST['title']) && !empty($_POST['title'])){
      $this->params["s"] = $_POST["title"];
      $arrMovies = $this->consult();

      foreach ($arrMovies["Search"] as $movie){
        $arrTMP = array(
          "id" => $movie["imdbID"],
          "image"=> ($movie["Poster"] != "N/A") ? $movie["Poster"] : "",
          "title"=> $movie["Title"],
          "year"=> $movie["Year"]
        );
        array_push($this->items, $arrTMP);
      }
    }

    return array(
      '#theme'=> 'movie_list',
      '#items'=> $this->items,
      '#title'=>'Todas las peliculas'
    );
  }

  public function add_favorite(){
    $arrResponse = array();
    $_REQUEST = json_decode(file_get_contents('php://input'), true);
    if (isset($_REQUEST["id"]) && !empty($_REQUEST["id"])){
      $this->id = $_REQUEST["id"];
      if ($this->validate_favorite()){
        $this->params["i"] = $this->id;
        $arrMovie = $this->consult();

        $this->id = $arrMovie["imdbID"];
        $this->image = ($arrMovie["Poster"] != "N/A") ? $arrMovie["Poster"] : "";
        $this->title = $arrMovie["Title"];
        $this->year = $arrMovie["Year"];
        $this->rating = $arrMovie["imdbRating"];
        $this->description = $arrMovie["Plot"];

        $this->insert_movie();
        $arrResponse = [
          "status" => 200,
          "message"=>"Pelicula agregada con exito"
        ];
      }else{
        $arrResponse = [
          "status" => 400,
          "message"=>"La pelicula ya fue agregada anteriormente"
        ];
      }
    }
    $this->print_json($arrResponse);
    die();
  }
}
