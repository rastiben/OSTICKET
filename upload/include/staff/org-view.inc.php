<?php

/*require_once(SCP_DIR.'Request/Contrat.php');*/
require_once(SCP_DIR.'Request/Tickets.php');
//require_once(INCLUDE_DIR.'class.contrat.php');

if(!defined('OSTSCPINC') || !$thisstaff || !isset($_REQUEST['id'])) die('Invalid path');

//$orgsC = OrganisationCollection::getInstance();

//$org = $orgsC->findOneOccur($name)[0];
//var_dump($org);
//die();

$org = new Organisation(["411VDOC","150 RUE DES HAUTS DE LA CHAUME","","86280","SAINT BENOIT","",""]);

$apiKey = "AIzaSyB4pINEbEV_CczgRAhMhIza1OAEzSJV6JA";

$tickets = TicketModel::objects()->filter(array('user__org_name'=>$org->getName()));
$tickets->values('number','created','cdata__subject','topic__couleur','user__org_name');

?>

<div class="profile">
  <div class="col-md-3">
    <div class="block">
      <div class="logo">
        <img src="../assets/default/images/company_building.png" />
      </div>
      <hr />
      <div class="infos">
        <p><b>Adresse :</b></p>
        <p><?= $org->getAddress() . " " . $org->getComplement(); ?><br>
         <?= $org->getCP() . " " . $org->getCity(); ?></p>
        <p><b>Numéro de téléphone :</b></p>
        <p><?= $org->getPhone(); ?></p>
        <p><b>Site web</b></p>
        <p><?= $org->getWebSite(); ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-9 block">
    <div class="col-md-6">
      <div class="col-md-12">
        <?php
        include STAFFINC_DIR . 'templates/tickets.tmpl.php';
        ?>
      </div>
    </div>
    <div class="col-md-6">
      <div class="col-md-12">
        <?php
        include STAFFINC_DIR . 'templates/users.tmpl.php';
        ?>
      </div>
    </div>
    <div class="col-md-12">
      <?php
      //Recuperation des contrats

      $query = 'SELECT rapport.id,rapport.contrat,rapport.instal FROM ost_rapport rapport '
            . 'INNER JOIN ost_ticket ticket ON (rapport.id_ticket = ticket.ticket_id) '
            . 'INNER JOIN ost_user user ON (ticket.user_id = user.id AND user.org_name = \''.$org->getName().'\')';

      $result = db_query($query);
      $contrats = [];
      $typeContrats = [];
      while ($row = db_fetch_array($result)) {
        if(!empty($row['contrat']) && $row['contrat'] != '0' && !in_array($row['contrat'],$typeContrats)){
          array_push($typeContrats,$row['contrat']);
        }
        array_push($contrats,(object)array('id'=>$row['id'],'contrat'=>$row['contrat'],'instal'=>$row['instal']));
      }
      ?>
      <h5>Temps passé par type de contrat</h5>
      <?php
      $horaires = [];
      $totalHoraires = 0;

      foreach ($typeContrats as $key => $type) {

        $filteredContrats = array_filter($contrats, function($elem) use($type){
            return $elem->contrat == $type;
        });

        foreach ($filteredContrats as $key => $contrat) {

            $query = 'SELECT horaire.arrive_inter,horaire.depart_inter FROM ost_rapport_horaires horaire WHERE horaire.id_rapport = \''.$contrat->id.'\'';
            $result = db_query($query);
            while ($row = db_fetch_array($result)) {
              array_push($horaires,(object)array('arrive_inter'=>$row['arrive_inter'],'depart_inter'=>$row['depart_inter']));
            }
        }
        //Calcule du temps passé
        $totalHours = 0;

        foreach ($horaires as $key => $horaire) {
          $arrive_inter = DateTime::createFromFormat('Y-m-d H:i:s',$horaire->arrive_inter);
          $depart_inter = DateTime::createFromFormat('Y-m-d H:i:s',$horaire->depart_inter);
          //Hour difference
          $totalHours += $depart_inter->getTimestamp() - $arrive_inter->getTimestamp();
        }

        $totalHoraires += $totalHours;
        $typeContrats[$key] = ['contrat'=>$type,'hours'=>$totalHours];

      }

      foreach ($typeContrats as $key => $type) {
        //echo $type['hours'];
        $percentage = round(($type['hours']*100)/$totalHoraires);
        //echo $percentage;

        $totalHours = $type['hours'];
        $days = floor($totalHours / 27900);
        if($days >= 1){
            $totalHours = $totalHours - (days * 27900);
        }
        $hours = floor($totalHours / 3600);
        if($hours >= 1){
            $totalHours = $totalHours - ($hours * 3600);
        }
        $minutes = floor($totalHours / 60);
        //echo "<p>" . $days . "  Jours</p> <p>" . $hours . "  Heures & " . $minutes . " Minutes</p>";
        echo '<div class="c100 p'.$percentage.'">
                    <span>'.$percentage.'%</span>
                    <div class="slice">
                        <div class="bar"></div>
                        <div class="fill"></div>
                    </div>
                </div>';
      }



      ?>
      <hr />
      <h5>Temps passé en instal par type</h5>
      <hr />
      <h5>Temps passé en formation</h5>
      <hr />
    </div>
  </div>
</div>

<script async defer
      src="https://maps.googleapis.com/maps/api/js?key=<?php echo $apiKey; ?>">
</script>

<!--MAP-->
<div style="height:400px;width:800px;margin:auto" id="map"></div>

<br>
<div class="clear"></div>


<!--<div class="tab_content" id="contrat" style="display:none">

<?php

    //$contrat = Contrat::getInstance()->getContrat($org->getId());
    $contratC = contratCollection::getInstance();
    $contrat = $contratC->lookUpById($org->getId());
    print_r($contrat);

    if(empty($contrat) === FALSE){



    $types = explode(';',$contrat['types']);
    $debut = DateTime::createFromFormat('Y-m-d',$contrat['depart']);
    $fin = DateTime::createFromFormat('Y-m-d',$contrat['fin']);
?>

<label>Date de début : </label>
<input type="text" class="datepicker" id="1" style="display:inline-block;width:auto" value="" size="12" autocomplete="off">
<label>Date de fin : </label>
<input type="text" class="datepicker" id="2" style="display:inline-block;width:auto" value="" size="12" autocomplete="off">

<script>
    var debut = "<?php echo $debut->format('d/m/Y'); ?>";
    var fin = "<?php echo $fin->format('d/m/Y'); ?>";

    $('#1.datepicker').datepicker({
        startView: 1,
        defaultDate: debut,
        format: 'dd/mm/yyyy',
        autoclose: true
    }).datepicker('setDate',debut);

    $('#2.datepicker').datepicker({
        startView: 1,
        format: 'dd/mm/yyyy',
        autoclose: true
    }).datepicker('setDate',fin);

</script>

<table class="contrat table table-striped" id="<?php echo $contrat['id'] ?>"
   data_org_id="<?php echo $org->getId() ?>" width="100%">
    <thead>
        <th>Hotline</th>
        <th>Atelier/Sur site</th>
        <th>Régie</th>
        <th>Téléphonie</th>
    </thead>
    <tbody>
        <tr>
            <td><input type="checkbox" <?php if (in_array('1',$types)) echo 'checked'  ?>></td>
            <td><input type="checkbox" <?php if (in_array('2',$types)) echo 'checked'  ?>></td>
            <td><input type="checkbox" <?php if (in_array('3',$types)) echo 'checked'  ?>></td>
            <td><input type="checkbox" <?php if (in_array('4',$types)) echo 'checked'  ?>></td>
        </tr>
    </tbody>
</table>

<textarea name="commentaire" id="commentaire" cols="50"
                            placeholder="<?php echo __(
                            'Start writing your response here. Use canned responses from the drop-down above'
                            ); ?>"
                            rows="9" wrap="soft"
                            class="richtext"><?php echo $contrat['commentaire'] ?></textarea>

<button type="button" class="btn btn-success" id="insertOrUpdate">Valider</button>

<h3>Temps passé : </h3>
<?php if (in_array('1',$types)) echo '<p><b>Hotline : </b> </p>' ?>
<?php if (in_array('1',$types)) echo '<p><b>Hotline : </b> </p>' ?>
<?php if (in_array('1',$types)) echo '<p><b>Hotline : </b> </p>' ?>
<?php if (in_array('1',$types)) echo '<p><b>Hotline : </b> </p>' ?>

<canvas id="tempsPasse" height="400"></canvas>

<?php
        } else {

?>
<label>Date de début : </label>
<input type="text" class="datepicker" id="1" style="display:inline-block;width:auto" value="" size="12" autocomplete="off">
<label>Date de fin : </label>
<input type="text" class="datepicker" id="2" style="display:inline-block;width:auto" value="" size="12" autocomplete="off">

<script>
    $('#1.datepicker').datepicker({
        startView: 1,
        format: 'dd/mm/yyyy',
        autoclose: true
    });

    $('#2.datepicker').datepicker({
        startView: 1,
        format: 'dd/mm/yyyy',
        autoclose: true
    });

</script>

<table class="contrat table table-striped" data_org_id="<?php echo $org->getId() ?>" width="100%">
    <thead>
        <th>Hotline</th>
        <th>Atelier/Sur site</th>
        <th>Régie</th>
        <th>Téléphonie</th>
    </thead>
    <tbody>
        <tr>
            <td><input type="checkbox"></td>
            <td><input type="checkbox"></td>
            <td><input type="checkbox"></td>
            <td><input type="checkbox"></td>
        </tr>
    </tbody>
</table>

<textarea name="commentaire" id="commentaire" cols="50"
                            placeholder="<?php echo __(
                            'Start writing your response here. Use canned responses from the drop-down above'
                            ); ?>"
                            rows="9" wrap="soft"
                            class="richtext"></textarea>

<button type="button" class="btn btn-success" id="insertOrUpdate">Valider</button>

<h3>Temps passé : </h3>

<canvas id="tempsPasse" height="400"></canvas>

<?php

    }
?>


<?php

?>
<script>
    var data = {
        labels: ["Hotline","Atelier-Sur site","Régie","Téléphonie"],
        datasets: [{
            label: "Temps passé" ,
            backgroundColor: "#FC9775" ,
            data: [<?php echo $tempsPasseHotline?>,
                  <?php echo $tempsPasseAtelierSurSite?>,
                  <?php echo $tempsPasseRegie?>,
                  <?php echo $tempsPasseTelephonie?>] ,
        }]
    };

    var ctx = $('#tempsPasse');
    ctx[0].width = $('.container').width()-70;
    new Chart(ctx, {
        type: 'bar'
        , data: data
        , options: {
            animation: {
                duration: 2000
            }
            , tooltips: {
                callbacks: {
                  label: function(tooltipItem, data) {
                    var datasetLabel = data.datasets[tooltipItem.datasetIndex].label || 'Other';
                    var hours = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                    return datasetLabel + ': ' + hours + ' H ';
                  }
                }
              }
            , responsive: false
            , maintainAspectRatio: false
            , scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                        , userCallback: function (label, index, labels) {
                            // N'afficher que les nombres entiers.
                            if (Math.floor(label) === label) {
                                return label;
                            }
                        }
                    , }
                                         }]
            , }
        , }
    , });

</script>

</div>-->

<div class="tab_content" id="notes" style="display:none">
<?php
/*$notes = QuickNote::forOrganization($org);
$create_note_url = 'orgs/'.$org->getId().'/note';
include STAFFINC_DIR . 'templates/notes.tmpl.php';*/
?>
</div>
</div>

<script type="text/javascript">

    $(function() {
        $(document).on('click', 'a.org-action', function(e) {
            e.preventDefault();
            var url = 'ajax.php/'+$(this).attr('href').substr(1);
            $.dialog(url, [201, 204], function (xhr) {
                if (xhr.status == 204)
                    window.location.href = 'orgs.php';
                else
                    window.location.href = window.location.href;
             }, {
                onshow: function() { $('#org-search').focus(); }
             });
            return false;
        });
    });

    var map = null;
    var address = "<?php echo str_replace(" ","+",$org->getComplement() . " " . $org->getAddress() . " " . $org->getCP()) ?>";
    console.log(address);
    $.ajax({
        method:"GET",
        url:"https://maps.googleapis.com/maps/api/geocode/json?address="+address+"&key=<?php echo $apiKey; ?>",
        success: function(data){
            //if no result test without address or without complement.
            //alert(data.results[0].geometry.location);
            var location = data.results[0].geometry.location;
            var LatLng = {lat: location.lat, lng: location.lng};

            map = new google.maps.Map(document.getElementById('map'), {
                center: LatLng,
                zoom: 12
            });
            var marker = new google.maps.Marker({
                position: LatLng,
                map: map
            });
        }
    });
</script>
