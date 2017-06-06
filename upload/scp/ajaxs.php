<?php



require_once('staff.inc.php');
require_once(INCLUDE_DIR . 'class.org.php');
require_once(INCLUDE_DIR . 'class.users.php');
require_once(INCLUDE_DIR . 'class.stats.php');
require_once(INCLUDE_DIR . 'class.stock.php');
require_once(INCLUDE_DIR . 'class.docSage.php');
require_once(INCLUDE_DIR . 'class.pdf.php');

//METHOD
$method = $_SERVER['REQUEST_METHOD'];

//ROOT
$url = $_SERVER[REQUEST_URI];

//Récupération des paramétre
$url = substr($url,strpos($url,".php")+5);

//Récupération des paramétre
//A = Action sur un élément
//V = variable
//C = Action sur un ensemble d'élément

$routes = array
(
    // actual path => filter
    'org' => array('org', ':id'),
    'orgA' => array('org', 'find', ':id'),
    'orgV' => array('org', ':id', ':variable'),
    'orgs' => array('orgs', ':name'),
    'orgsTypeahead' => array('orgs', 'typeahead' , ':name'),
    'contratTypeahead' => array('contrats','typeahead'),
    'userC' => array('user',':action'),
    'userA' => array('user', ':action', ':id'),
    'stats' => array('stats',':objet',':id'),
    'stock' => array('stock',':agent'),
    'stockSN' => array('stock','sn',':reference',':agent'),
    'stocks' => array('stocks'),
    'docSage' => array('docSage',':action'),
    'print' => array('print',':doc',':id'),
    'printNoId' => array('print',':doc')
);

function dispatcher($url, $routes)
{
    $final_path         = FALSE;

    $url_path           = explode('/', $url);
    $url_path_length    = count($url_path);

    foreach($routes as $original_path => $filter)
    {
        // reset the parameters every time in case there is partial match
        $parameters     = array();

        // this filter is irrelevent
        if($url_path_length <> count($filter))
        {
            continue;
        }

        foreach($filter as $i => $key)
        {
            if(strpos($key, ':') === 0)
            {
                $parameters[substr($key, 1)]    = $url_path[$i];
            }
            // this filter is irrelevent
            else if($key != $url_path[$i])
            {
                continue 2;
            }
        }

        $final_path = $original_path;

        break;
    }

    return $final_path ? array('path' => $final_path, 'parameters' => $parameters) : FALSE;
}

$url = dispatcher($url, $routes);
/*if(strstr($url['path'],"toto")){
    $org = OrganisationCollection::getInstance();
    $org->toto();
}*/

/*REQUETES ORGANISATION*/
if(strstr($url['path'],"org")){
    $org = OrganisationCollection::getInstance();
    if(strstr($url['path'],"Typeahead")){
      $orgs = $org->searchByName($url['parameters']['name']);

      foreach ($orgs as $O) {
        $matched[] = array('name' => $O->getName(), 'info' => $O->getName(),
            'id' => $O->getName(), '/bin/true' => $url['parameters']['name']);
      }

      echo json_encode(array_values($matched));

    } else if($url['path'] == "orgs"){
      switch($method){
          case "GET":
              echo json_encode($org->searchByName($url['parameters']['name']));
              break;
      }
    } else {
        if($url['path'] == "orgA"){
          echo json_encode($org->findOneOccur($url['parameters']['id']));
        } else if($variable = isset($url['parameters']['variable'])) {
            $org = $org->lookUpById($url['parameters']['id'])[0];
            if($variable == "name"){
                echo $org->getName();
            }
        } else {
            switch($method){
                case "GET":
                    echo json_encode($org->lookUpById($url['parameters']['id']));
                    break;
            }
        }
    }
/*REQUETES USERS*/
}  else if(strstr($url['path'],"user")){
    $users = userCollection::getInstance();
    if($action = isset($url['parameters']['action'])){
        if($id = isset($url['parameters']['id'])){

        } else {
            if($action == "maj"){
                $users->majBaseUser();
            }
            if($action == "setOrg"){

            }
        }
    }
/*REQUETES STATISTIQUE*/
} else if(strstr($url['path'],"stats")){
    $stats = new stats();
    if(isset($url['parameters']['objet'])){
        $objet = $url['parameters']['objet'];
        if($objet == "org"){
            $id = $url['parameters']['id'];
            $date1 = $_POST['sDate'];
            $date2 = $_POST['eDate'];
            echo $stats->statsTicketFromOrg($id,$date1,$date2);
        } else if($objet == "agent"){
            $id = $url['parameters']['id'];
            $date1 = $_POST['sDate'];
            $date2 = $_POST['eDate'];
            echo $stats->statsTicketForAgent($id,$date1,$date2);
        }
    }
/*REQUETES CONTRAT*/
}  else if(strstr($url['path'],"stock")){
    if($url['path'] == "stocks"){
        $stocks = stock::getStocks();
        echo json_encode($stocks);
    }else if($url['path'] == 'stockSN'){
        $reference = $url['parameters']['reference'];
        $agent = $url['parameters']['agent'];
        $stock = stock::getSN($reference,$agent);
        echo json_encode($stock);
    }else if(isset($url['parameters']['agent'])){
        $agent = urldecode($url['parameters']['agent']);
        $stock = new stock($agent);
        echo json_encode($stock->articles);
    }
    //orgid
} else if(strstr($url['path'],'docSage')){
    if(isset($url['parameters']['action'])){
      $action = $url['parameters']['action'];
        if($action == 'createDoc') {
            $angular_http_params = (array)json_decode(trim(file_get_contents('php://input')));
            $org = $angular_http_params['org'];
            $stock = $angular_http_params['stock'];
            $lines = $angular_http_params['lines'];
            docSage::createDocument($org,$stock,$lines);
        } else if($action == 'contrats'){
          $angular_http_params = (array)json_decode(trim(file_get_contents('php://input')));
          docSage::createDocumentF($angular_http_params);
        }
    }
} else if(strstr($url['path'],"print")){
    if($url['parameters']['doc'] == 'fs'){
        //impression de la fiche de suivi
        $id = $url['parameters']['id'];
        $pdf = new Ticket2PDF(null, 'Letter', null, "fs", $id);
        $output = $pdf->Output($name, 'S');
        $pdfBase64 = base64_encode($output);
        echo 'data:application/pdf;base64,' . $pdfBase64;
    } else if($url['parameters']['doc'] == 'atelier'){
        $pdf = new Ticket2PDF(null, 'A3-L', null, "atelier");
        $output = $pdf->Output($name, 'S');
        $pdfBase64 = base64_encode($output);
        echo 'data:application/pdf;base64,' . $pdfBase64;
    }
} else if(strstr($url['path'],"contrat")){
  $org = OrganisationCollection::getInstance();
  if($url['path'] == "contratTypeahead"){
    //[F_ARTICLE] where AR_Ref LIKE '%CONTRAT%'
    $contrats = $org->getContrats();
    echo json_encode(array_values($contrats));
  }
}

?>
