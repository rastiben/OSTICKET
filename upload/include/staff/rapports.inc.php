
<?php

$labelsTotal = [];
$dataTotal = [];
$colorTotal = [];

$rapports = RapportModel::objects();
$rapports->values('topic__topic','topic__couleur');

foreach($rapports as $rapport){

    //arraycharts
    if(in_array($rapport['topic__topic'],$labelsTotal)){
        $index = array_search($rapport['topic__topic'],$labelsTotal);
        $dataTotal[$index] = $dataTotal[$index] + 1;
    } else {
        array_push($labelsTotal,$rapport['topic__topic']);
        $index = count($labelsTotal)-1;
        $dataTotal[$index] = 1;
        $colorTotal[$index] = $rapport['topic__couleur'];
    }

}

$labelsWeek = [];
$dataWeek = [];
$colorWeek = [];

//date('Y-m-d', strtotime('previous monday')
$rapports = RapportModel::objects();
$rapports->filter(Q::any(array('date_rapport__gt'=>date('Y-m-d', strtotime('previous monday')),
                        'date_rapport'=>date('Y-m-d', strtotime('previous monday')))));
$rapports->values('topic__topic','topic__couleur');

foreach($rapports as $rapport){

    if(in_array($rapport['topic__topic'],$labelsWeek)){
        $index = array_search($rapport['topic__topic'],$labelsWeek);
        $dataWeek[$index] = $dataWeek[$index] + 1;
    } else {
        array_push($labelsWeek,$rapport['topic__topic']);
        $index = count($labelsWeek)-1;
        $dataWeek[$index] = 1;
        $colorWeek[$index] = $rapport['topic__couleur'];
    }
}

if(count($labelsWeek) === 0){
    array_push($labelsWeek,'Aucun rapport');
    $dataWeek[0] = 0;
    $colorWeek[0] = 'black';
}

?>



<div class="rapportInfo">
    <div class="col-md-4">
        <div class="block r-2 col-md-12">
            <span class="col-md-12 title"><p><strong>Nombre de rapports par type</strong></p>
            <p>(total)</p></span>
            <canvas id="typeRapportTotal" style="max-height:200px;max-width:200px;width: content-box;display:inline" class="pie chart"></canvas>
            <div id="chartjs-legend-total" class="noselect col-md-12"></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="block r-2 col-md-12">
            <span class="col-md-12 title"><p><strong>Nombre de rapports par type</strong></p>
            <p>(sur la semaine)</p></span>
            <canvas id="typeRapportWeek" style="max-height:200px;max-width:200px;width: content-box;display:inline" class="pie chart"></canvas>
            <div id="chartjs-legend-week" class="noselect col-md-12"></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="col-md-12"><div class="block">Nombre de rapports</div></div>
        <div class="col-md-12"><div class="block">Nombre de rapports aujourd'hui</div></div>
    </div>

    <div class="filtre">
        <input value="">
        <button class="btn btn-success"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></button>
        <button class="btn btn-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>
    </div>
</div>

<div ng-controller="rapportCtrl" id="rapportCtrl" class="rapportHoraires col-md-12" ng-init="initRapport(<?php echo htmlspecialchars(json_encode($rapportl)); ?>)">
<?php

require(INCLUDE_DIR.'staff/templates/rapports.tmpl.php');

?>
</div>

<script src="./js/moment.js" type="application/javascript"></script>
<script src="./js/rapport.js" type="application/javascript"></script>

<script>

    function $_GET(url,param) {
        var vars = {};
        if(url != null){
            url.replace( location.hash, '' ).replace(
                /[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
                function( m, key, value ) { // callback
                    vars[key] = value !== undefined ? value : '';
                }
            );
        } else {
            window.location.href.replace( location.hash, '' ).replace(
                /[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
                function( m, key, value ) { // callback
                    vars[key] = value !== undefined ? value : '';
                }
            );
        }
        if ( param ) {
            return vars[param] ? vars[param] : null;
        }
        return vars;
    }


    var clicked = undefined;
    var filtres = [];

    //remplissage du tableau de filtre avec les info GET dans l'url
    var maj = ['org','auteur','type'];
    var GETS = $_GET();
    filtres['org'] = GETS['org'];
    filtres['auteur'] = GETS['auteur'];
    filtres['type'] = GETS['type'];

    //handle click on pagination
    $(document).on('click','.pagination a',function(e){
        e.stopImmediatePropagation();
        e.preventDefault();

        var href= $(this).attr('href');

        var url = window.location.href;
        url = url.substring(0,url.indexOf('?'));
        //Refresh table when click on pagination
        $('.loading.blank').css('display','block');

        $.ajax({
            method:"POST",
            url:url,
            data: {
                p:$_GET(href)['p'],
                org:filtres['org'],
                auteur:filtres['auteur'],
                type:filtres['type'],
            },
            success: function(data){
                data = $.parseJSON(data);
                $('.loading.blank').css('display','none');
                //$('.rapportListe').replaceWith(data);
                angular.element('#rapportCtrl').scope().initRapport(data.rapports);
                $('.pagination').replaceWith(data.pagination);

                //maj pagination
            }
        });
        //console.log($_GET(href)['p']);

        return false;
    });

    //handle click on valid filter
    $(document).on('click','.filtre .btn.btn-success',function(){
        var url = window.location.href;
        url = url.substring(0,url.indexOf('?'));
        filtres[$(clicked).attr('data-filter')] = $('.filtre input').val();
        params = $(clicked).attr('data-filter') + "=" + $('.filtre input').val();

        $('.filtre').css('display','none');

        $('.loading.blank').css('display','block');

        $.ajax({
            method:"POST",
            url:url,
            data: {
                org:filtres['org'],
                auteur:filtres['auteur'],
                type:filtres['type'],
            },
            success: function(data){
                data = $.parseJSON(data);
                $('.loading.blank').css('display','none');
                //$('.rapportListe').replaceWith(data);
                angular.element('#rapportCtrl').scope().initRapport(data.rapports);
                $('.pagination').replaceWith(data.pagination);

                //Mise a jour des filtres.
                maj.forEach(function(element,index,array){
                    if(filtres[element] != '' && filtres[element] != undefined)
                        $(".glyphicon.glyphicon-filter[data-filter='"+element+"']").css('color','#5DADE2');
                    else
                        $(".glyphicon.glyphicon-filter[data-filter='"+element+"']").css('color','black');
                });
            }
        });

    });


    //handle click on cancel filter
    $(document).on('click','.filtre .btn.btn-danger',function(){
        $('.filtre').css('display','none');
        //console.log(params);
    });

    //handle click on table th filter span
    $(document).on('click','.rapportListe table th span',function(){

        offset = $(this).offset();
        filtre = $('.filtre');

        //Partie affichage
        if(filtre.css('display') == "block" && clicked == this){

            $('.filtre').css('display','none');

        } else {

            $('input',filtre).val(filtres[$(this).attr('data-filter')]);

            //Calcule de l'offset de la div.
            var rightPosition = (offset.left-12) + 300;

            filtre.removeClass();

            if(rightPosition > $(window).width()){
                filtre.css('top',offset.top-90);
                filtre.css('left',offset.left-276);
                filtre.addClass('filtre toLeft');
                filtre.css('display','block');
            } else {
                filtre.css('top',offset.top-90);
                filtre.css('left',offset.left-12);
                filtre.addClass('filtre toRight');
                filtre.css('display','block');
            }

        }

        clicked = $(this).get(0);

    });

    //Gestion des charts

    var options = {
      legend: false,
      legendCallback: function(chart) {
        var text = [];
        text.push('<ul class="' + chart.id + '-legend">');
        for (var i = 0; i < chart.data.datasets[0].data.length; i++) {
          text.push('<li><span style="background-color:' + chart.data.datasets[0].backgroundColor[i] + '">');
          if (chart.data.labels[i]) {
            text.push(chart.data.labels[i]);
          }
          text.push('</span></li>');
        }
        text.push('</ul>');
        return text.join("");
      }
    };

    //TOTAL
    var data = {
        labels: <?= json_encode($labelsTotal); ?>,
        datasets: [
            {
                data: <?= json_encode($dataTotal); ?>,
                backgroundColor: <?= json_encode($colorTotal); ?>
            }]
    };

    //Chart.defaults.global.defaultFontColor = "#fff";
    var ctx = $("#typeRapportTotal");
    var myChart = new Chart(ctx, {
      type: 'pie',
      data: data,
      options: options
    });
    $("#chartjs-legend-total").html(myChart.generateLegend());

    //SEMAINE
    data = {
        labels: <?= json_encode($labelsWeek); ?>,
        datasets: [
            {
                data: <?= json_encode($dataWeek); ?>,
                backgroundColor: <?= json_encode($colorWeek); ?>
            }]
    };

    var ctx = $("#typeRapportWeek");
    var myChart = new Chart(ctx, {
      type: 'pie',
      data: data,
      options: options
    });
    $("#chartjs-legend-week").html(myChart.generateLegend());


</script>
