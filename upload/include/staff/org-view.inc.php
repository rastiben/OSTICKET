<?php

/*require_once(SCP_DIR.'Request/Contrat.php');*/
require_once(SCP_DIR.'Request/Tickets.php');
//require_once(INCLUDE_DIR.'class.contrat.php');

if(!defined('OSTSCPINC') || !$thisstaff || !isset($_REQUEST['id'])) die('Invalid path');

$name = $_GET['id'];
$orgsC = OrganisationCollection::getInstance();
$org = $orgsC->findOneOccur($name)[0];

//var_dump($org);
//die();

/*if(empty($org))
  $org = new Organisation(["411VDOC","150 RUE DES HAUTS DE LA CHAUME","","86280","SAINT BENOIT","",""]);*/

$apiKey = "AIzaSyB4pINEbEV_CczgRAhMhIza1OAEzSJV6JA";

$tickets = TicketModel::objects()->filter(array('user__org_name'=>$org->getName()));
$tickets->values('number','created','cdata__subject','topic__couleur','user__org_name');

?>

<script src="http://cdn.jsdelivr.net/alasql/0.3/alasql.min.js"></script>

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
      <h5>Temps passé par type de contrat</h5>
      <div class="form" style="text-align:center">
        <label for="dateDebutC">Date de debut : </label>
        <input id="dateDebutC" name="dateDebutC" />
        <label for="dateFinC">Date de fin : </label>
        <input id="dateFinC" name="dateFinC" />
        <button class="reloadContrats" data-type="contrat" class="btn btn-success">Modifier</button>
      </div>
      <canvas id="tempsContrat" style="max-width:100%;max-height:300px;margin:0 auto;"></canvas>
      <hr />
      <h5>Temps passé en instal par type</h5>
      <div class="form" style="text-align:center">
        <label for="dateDebutI">Date de debut : </label>
        <input id="dateDebutI" name="dateDebutI" />
        <label for="dateFinI">Date de fin : </label>
        <input id="dateFinI" name="dateFinI" />
        <button class="reloadContrats" data-type="instal" class="btn btn-success">Modifier</button>
      </div>
      <canvas id="tempsInstal" style="max-width:100%;max-height:300px;margin:0 auto;"></canvas>
      <hr />
      <h5>Temps passé en formation</h5>
      <div class="form" style="text-align:center">
        <label for="dateDebutF">Date de debut : </label>
        <input id="dateDebutF" name="dateDebutF" />
        <label for="dateFinF">Date de fin : </label>
        <input id="dateFinF" name="dateFinF" />
        <button class="reloadContrats" data-type="formation" class="btn btn-success">Modifier</button>
      </div>
      <canvas id="tempsFormation" style="max-width:100%;max-height:300px;margin:0 auto;"></canvas>
      <hr />
    </div>
    <button class="btn btn-primary"><span class="glyphicon glyphicon-print"></span></button>
  </div>
</div>

<script async defer
      src="https://maps.googleapis.com/maps/api/js?key=<?php echo $apiKey; ?>">
</script>

<!--MAP-->
<div style="height:400px;width:800px;margin:auto" id="map"></div>

<div class="tab_content" id="notes" style="display:none">
<?php
/*$notes = QuickNote::forOrganization($org);
$create_note_url = 'orgs/'.$org->getId().'/note';
include STAFFINC_DIR . 'templates/notes.tmpl.php';*/
?>
</div>
</div>

<script type="text/javascript">

    $(document).ready(function(){
      var datepickers = ["#dateDebutC","#dateFinC","#dateDebutI","#dateFinI","#dateDebutF","#dateFinF"];
      $.each(datepickers,function(key,value){
        $(value).datepicker({
          dateFormat:"dd/mm/yy"
        });
      });
    });

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

    var colors = [
      "#00ffff",
      "#f0ffff",
      "#f5f5dc",
      "#000000",
      "#0000ff",
      "#a52a2a",
      "#00ffff",
      "#00008b",
      "#008b8b",
      "#a9a9a9",
      "#006400",
      "#bdb76b",
      "#8b008b",
      "#556b2f",
      "#ff8c00",
      "#9932cc",
      "#8b0000",
      "#e9967a",
      "#9400d3",
      "#ff00ff",
      "#ffd700",
      "#008000",
      "#4b0082",
      "#f0e68c",
      "#add8e6",
      "#e0ffff",
      "#90ee90",
      "#d3d3d3",
      "#ffb6c1",
      "#ffffe0",
      "#00ff00",
      "#ff00ff",
      "#800000",
      "#000080",
      "#808000",
      "#ffa500",
      "#ffc0cb",
      "#800080",
      "#800080",
      "#ff0000",
      "#c0c0c0",
      "#ffffff",
      "#ffff00"
  ];

  function hexToRgbA(hex){
      var c;
      if(/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)){
          c= hex.substring(1).split('');
          if(c.length== 3){
              c= [c[0], c[0], c[1], c[1], c[2], c[2]];
          }
          c= '0x'+c.join('');
          return 'rgba('+[(c>>16)&255, (c>>8)&255, c&255].join(',')+',0.2)';
      }
      throw new Error('Bad Hex');
  }

  var getChartData = function(datas){
      datas = $.parseJSON(datas);

      var datasets = [];
      //randomColors
      $.each(datas.data,function(kay,value){
        var color = colors[Math.floor(Math.random() * colors.length)];
        datasets.push({
          label: value.label,
          fill: true,
          backgroundColor: hexToRgbA(color),
          borderColor: color,
          pointBorderColor: "#fff",
          pointBackgroundColor: color,
          data: value.data
        });
      });

      var data = {
        labels: datas.labels,
        datasets: datasets
      };

      return data;
    }

    var options = {
      maintainAspectRatio: false,
      tooltips: {
        callbacks: {
          label: function(tooltipItem, chartData) {

            var totalHours = chartData.datasets[0].data[tooltipItem.index];
            var days = Math.floor(totalHours / 27900);
            if(days >= 1){
                totalHours = totalHours - (days * 27900);
            }
            var hours = Math.floor(totalHours / 3600);
            if(hours >= 1){
                totalHours = totalHours - (hours * 3600);
            }
            var minutes = Math.floor(totalHours / 60);

            return chartData.labels[tooltipItem.index] +': ' + days + ' Jours ' + hours + ' Heures & ' + minutes + " Minutes";
          }
        }
      }
    }
    var myChart = {"contrat":undefined,"instal":undefined,"formation":undefined};

    $('.reloadContrats').click(function(){
      var type = $(this).attr('data-type');
      var canvas = $(this).parent().next().attr('id');
      var char = type[0].toUpperCase();
      $.ajax({
        method: "GET",
        url:"/osTicket/upload/scp/orgs.php?stats="+type,
        data: {
          datedebut:$('#dateDebut'+char).val(),
          datefin:$('#dateFin'+char).val(),
        },
        success : function(response){
          if(myChart[type] != undefined)
            myChart[type].destroy();

          myChart[type] = new Chart($("#"+canvas), {
            type: 'radar',
            data: getChartData(response),
            options : options
          });
        }
      });
    });

</script>
