<?php

require_once(INCLUDE_DIR.'class.contrats.php');

class ContratsAjaxAPI extends AjaxController {

  function index(){
    $contrats = ContratModel::objects();
    $temp = [];

    foreach ($contrats as $key => $value) {
      //echo json_encode($val)
      array_push($temp,$value->ht);
    }

    Http::response(200, json_encode($temp));
  }

  function add(){
    $vars = (array)json_decode(file_get_contents("php://input"));

    if (($contrat = Contrat::fromVars($vars)))
        Http::response(201, json_encode($contrat->ht));
  }

  function update(){
    $vars = (array)json_decode(file_get_contents("php://input"));

    if (($contrat = Contrat::fromVars($vars,false,true)))
        Http::response(201, json_encode($contrat->ht));
  }

  function delete($id){
    if (!($contrat = Contrat::lookup($id)))
        Http::response(404, 'Unknown organization');

    if ($contrat->delete())
        Http::response(204, json_encode($contrat->ht));
    //'Organization deleted successfully'
  }

}
