<?php
$report = new OverviewReport($_POST['start'], $_POST['period']);
$plots = $report->getPlotData();

$agents = Staff::objects()
        ->annotate(array(
            'teams_count'=>SqlAggregate::COUNT('teams', true),
        ))
        ->select_related('dept', 'group');


?>
    <script type="text/javascript" src="js/raphael-min.js?901e5ea"></script>
    <script type="text/javascript" src="js/g.raphael.js?901e5ea"></script>
    <script type="text/javascript" src="js/g.line-min.js?901e5ea"></script>
    <script type="text/javascript" src="js/g.dot-min.js?901e5ea"></script>
    <script type="text/javascript" src="js/dashboard.inc.js?901e5ea"></script>
    <link rel="stylesheet" type="text/css" href="css/dashboard.css?901e5ea" />
    <form method="post" action="dashboard.php">
        <div id="basic_search">
            <div style="min-height:25px;">
                <!--<p><?php //echo __('Select the starting time and period for the system activity graph');?></p>-->
                <?php echo csrf_token(); ?>
                    <label>
                        <?php echo __( 'Report timeframe'); ?>:
                            <input type="text" class="dp input-medium search-query" name="start" placeholder="<?php echo __('Last month');?>" value="<?php
                        echo Format::htmlchars($report->getStartDate());
                    ?>" /> </label>
                    <label>
                        <?php echo __( 'period');?>:
                            <select name="period">
                                <option value="now" selected="selected">
                                    <?php echo __( 'Up to today');?>
                                </option>
                                <option value="+7 days">
                                    <?php echo __( 'One Week');?>
                                </option>
                                <option value="+14 days">
                                    <?php echo __( 'Two Weeks');?>
                                </option>
                                <option value="+1 month">
                                    <?php echo __( 'One Month');?>
                                </option>
                                <option value="+3 months">
                                    <?php echo __( 'One Quarter');?>
                                </option>
                            </select>
                    </label>
                    <button class="green button action-button muted" type="submit">
                        <?php echo __( 'Refresh');?>
                    </button> <i class="help-tip icon-question-sign" href="#report_timeframe"></i> </div>
        </div>
        <div class="clear"></div>
        <div style="margin-bottom:20px; padding-top:5px;">
            <div class="pull-left flush-left">
                <h2><?php echo __('Ticket Activity');
            ?>&nbsp;<i class="help-tip icon-question-sign" href="#ticket_activity"></i></h2> </div>
        </div>
        <div class="clear"></div>
        <!-- Create a graph and fetch some data to create pretty dashboard -->
        <div style="position:relative">
            <div id="line-chart-here" style="height:300px"></div>
            <div style="position:absolute;right:0;top:0" id="line-chart-legend"></div>
        </div>
        <hr/>
        <h2>Statistiques</h2>
        <p>Affichage du nombre de tickets ouverts et fermées pour une organisation sur une période donnée</p>
        <!--CHART.JS-->
        <label>Organisation : </label>
        <input class="user_org selectpicker1"> </input>
        <div class="orgsList" style="display:none">

        </div>
        <label>De : </label>
        <input type="text" id="sDate">
        <label>À : </label>
        <input type="text" id="eDate">
        <canvas id="myChart"></canvas>
        <p>Affichage du nombre de tickets ouverts et fermées par un agent sur une période donnée</p>
        <!--CHART.JS-->
        <label>Agent : </label>
        <select class="selectpicker2">
        <?php foreach ($agents as $agent) { ?>

            <option id="<?php echo $agent->getId() ?>"><?php echo $agent->getFirstName().' '.$agent->getLastName()?></option>

        <?php } ?>
        </select>
        <label>De : </label>
        <input type="text" id="saDate">
        <label>À : </label>
        <input type="text" id="eaDate">
        <canvas id="agentCanvas"></canvas>
        <script>


            $(document).ready(function(){
              $.datepicker.regional['fr'] = {
            		closeText: 'Fermer',
            		prevText: 'Précédent',
            		nextText: 'Suivant',
            		currentText: 'Aujourd\'hui',
            		monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin',
            		'Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
            		monthNamesShort: ['Janv.','Févr.','Mars','Avril','Mai','Juin',
            		'Juil.','Août','Sept.','Oct.','Nov.','Déc.'],
            		dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
            		dayNamesShort: ['Dim.','Lun.','Mar.','Mer.','Jeu.','Ven.','Sam.'],
            		dayNamesMin: ['D','L','M','M','J','V','S'],
            		weekHeader: 'Sem.',
            		dateFormat: 'dd/mm/yy',
            		firstDay: 1,
            		isRTL: false,
            		showMonthAfterYear: false,
            		yearSuffix: ''};
            	$.datepicker.setDefaults($.datepicker.regional['fr']);
              //INITIALISATION DU DATEPICKER START ORGANISATION
              $('#sDate').datepicker({
                format: "dd/mm/yyyy"
                , startView: 2
                , language: "fr"
                , autoclose: true
                , orientation: "bottom auto"
              });
              //INITIALISATION DU DATEPICKER END ORGANISATION
              $('#eDate').datepicker({
                  format: "dd/mm/yyyy"
                  , startView: 2
                  , language: "fr"
                  , autoclose: true
                  , orientation: "bottom auto"
              , });
              //INITIALISATION DU DATEPICKER START AGENT
              $('#saDate').datepicker({
                  format: "dd/mm/yyyy"
                  , startView: 2
                  , language: "fr"
                  , autoclose: true
                  , orientation: "bottom auto"
              , });
              //INITIALISATION DU DATEPICKER END AGENT
              $('#eaDate').datepicker({
                  format: "dd/mm/yyyy"
                  , startView: 2
                  , language: "fr"
                  , autoclose: true
                  , orientation: "bottom auto"
              , });
            });

            var org_id;
            var staff_id = <?php echo $agents[0]->getId(); ?>;

            //CHANGEMENT DE DATE POUR ORGANISATION
            $('#sDate').change(function () {
                if($('#sDate').val() != '' && $('#eDate').val() != '')
                    updateChart();
            });
            //CHANGEMENT DE DATE POUR ORGANISATION
            $('#eDate').change(function () {
                if($('#sDate').val() != '' && $('#eDate').val() != '')
                    updateChart();
            });
            //CHANGEMENT D'AGENT
            $('.selectpicker2').change(function () {
                staff_id = $(this).attr('id');
                if($('#saDate').val() != '' && $('#eaDate').val() != '')
                    updateChartAgent();
            });
            //CHANGEMENT DE DATE POUR AGENT
            $('#saDate').change(function () {
                if($('#saDate').val() != '' && $('#eaDate').val() != '')
                    updateChartAgent();
            });
            //CHANGEMENT DE DATE POUR AGENT
            $('#eaDate').change(function () {
                if($('#saDate').val() != '' && $('#eaDate').val() != '')
                    updateChartAgent();
            });

            //OBTENTION DES DONNEES TICKETS & MISE A JOUR DU GRAPHIQUE
            function updateChart() {
                $.ajax({
                    type: "POST"
                    , data: {
                        sDate: $('#sDate').val()
                        , eDate: $('#eDate').val()
                    }
                    , url: "./ajaxs.php/stats/org/"+org_id
                    , dataType: "html"
                    , async: false
                    , success: function (data) {
                        var json = JSON.parse(data);
                        var arrayObj1 = [];
                        var arrayObj2 = [];
                        var labels = [];
                        for (var i = 0; i < json['2'].length; i++) {
                            labels[i] = json['1'][i];
                            arrayObj1[i] = parseInt(json['2'][i]);
                            arrayObj2[i] = parseInt(json['3'][i]);
                        }
                        var data = {
                            labels: labels
                            , datasets: [{
                                label: "Nombre de tickets ouverts"
                                , backgroundColor: "#FC9775"
                                , data: arrayObj1
                            , }, {
                                label: "Nombre de tickets fermés"
                                , backgroundColor: "#5A69A6"
                                , data: arrayObj2
                            , }]
                        };
                        $('#myChart').replaceWith('<canvas id="myChart" height="400"></canvas>');
                        $('#myChart')[0].width = $('.container').width()-70;
                        var ctx = $('#myChart');
                        new Chart(ctx, {
                            type: 'bar'
                            , data: data
                            , options: {
                                animation: {
                                    duration: 2000
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
                    }
                });
            }

            //OBTENTION DES DONNEES TICKETS PAR AGENT & MISE A JOUR DU GRAPHIQUE
            function updateChartAgent(agent) {
                $.ajax({
                    type: "POST"
                    , data: {
                        sDate: $('#saDate').val()
                        , eDate: $('#eaDate').val()
                    }
                    , url: "./ajaxs.php/stats/agent/"+staff_id
                    , dataType: "html"
                    , async: false
                    , success: function (data) {
                        var json = JSON.parse(data);
                        var array = [];
                        var labels = [];
                        for (var i = 0; i < json['2'].length; i++) {
                            labels[i] = json['1'][i];
                            array[i] = parseInt(json['2'][i]);
                        }
                        var data = {
                            labels: labels
                            , datasets: [{
                                label: "Nombre de tickets fermées"
                                , backgroundColor: "#FC9775"
                                , data: array
                            , }]
                        };
                        $('#agentCanvas').replaceWith('<canvas id="agentCanvas" height="400"></canvas>');
                        $('#agentCanvas')[0].width = $('.container').width()-70;
                        var ctx = $('#agentCanvas');
                        new Chart(ctx, {
                            type: 'bar'
                            , data: data
                            , options: {
                                animation: {
                                    duration: 2000
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
                    }
                });
            }



            /**/
            var clicky;

            $(document).mousedown(function(e) {
                // The latest element clicked
                clicky = $(e.target);
            });

            $(document).on('focusout','.user_org',function(e){
                if(!$(clicky).is('p')){
                    $(".orgsList").css('display','none');
                    //$("tr td:contains('Organisation:')").siblings().find('input').focus();
                } else {
                    $(".user_org").focus();
                }
            });

            $(document).on('focusin','.user_org',function(e){
                if($('.user_org').val().length > 0){
                    var orgInput = $(this);
                    var pos = orgInput.position();
                    var top = pos.top + 27;
                    var left = pos.left;
                    $(".orgsList").css('top',top);
                    $(".orgsList").css('left',left);
                    $(".orgsList").css('width','auto');
                    $(".orgsList").css('display','block');
                }
            });

            (function ($) {
                $.fn.delayKeyup = function(callback, ms){
                    var timer = 0;
                    $(this).keyup(function(){
                        clearTimeout (timer);
                        timer = setTimeout(callback, ms);
                    });
                    return $(this);
                };
            })(jQuery);

            $('.user_org').delayKeyup(function(){
                //alert("5 secondes passed from the last event keyup.");
                    var orgInput = $('.user_org');
                    var pos = orgInput.position();
                    var top = pos.top + 27;
                    var left = pos.left;

                    if(orgInput.val().length > 0){
                        $.ajax({
                            method: "GET",
                            url: "./ajaxs.php/orgs/"+orgInput.val()
                        })
                        .success(function( data ) {
                            data = $.parseJSON(data);
                            $(".orgsList").empty();
                            $(".orgsList").css('top',top);
                            $(".orgsList").css('left',left);
                            $(".orgsList").css('width','auto');
                            $(data).each(function(number,obj){
                                $(".orgsList").append('<p data-org-name="" id="3314">'+obj.data[0]+'</p>')
                            });
                            $(".orgsList").css('display','block');
                        });
                    } else {
                        $(".orgsList").css('display','none');
                    }
            }, 500);

            //temporisation

            $(document).on('click','.orgsList p',function(){
                $(".user_org").val($(this).text());
                org_id = $(this).text();
            });


        </script>
        <h2><?php echo __('Statistics'); ?>&nbsp;<i class="help-tip icon-question-sign" href="#statistics"></i></h2>
        <p>
            <?php echo __('Statistics of tickets organized by department, help topic, and agent.');?>
        </p>
        <ul class="clean tabs">
            <?php
$first = true;
$groups = $report->enumTabularGroups();
foreach ($groups as $g=>$desc) { ?>
                <li class="<?php echo $first ? 'active' : ''; ?>">
                    <a href="#<?php echo Format::slugify($g); ?>">
                        <?php echo Format::htmlchars($desc); ?>
                    </a>
                </li>
                <?php
    $first = false;
} ?>
        </ul>
        <?php
$first = true;
foreach ($groups as $g=>$desc) {
    $data = $report->getTabularData($g); ?>
            <div class="tab_content <?php echo (!$first) ? 'hidden' : ''; ?>" id="<?php echo Format::slugify($g); ?>">
                <table class="dashboard-stats table">
                    <tbody>
                        <tr>
                            <?php
    foreach ($data['columns'] as $j=>$c) { ?>
                                <th <?php if ($j===0 ) echo 'width="30%" class="flush-left"'; ?>>
                                    <?php echo Format::htmlchars($c); ?>
                                </th>
                                <?php
    } ?>
                        </tr>
                    </tbody>
                    <tbody>
                        <?php
    foreach ($data['data'] as $i=>$row) {
        echo '<tr>';
        foreach ($row as $j=>$td) {
            if ($j === 0) { ?>
                            <th class="flush-left">
                                <?php echo Format::htmlchars($td); ?>
                            </th>
                            <?php       }
            else { ?>
                                <td>
                                    <?php echo Format::htmlchars($td);
                if ($td) { // TODO Add head map
                }
                echo '</td>';
            }
        }
        echo '</tr>';
    }
    $first = false; ?>
                    </tbody>
                </table>
                <div style="margin-top: 5px">
                    <button type="submit" class="link button" name="export" value="<?php echo Format::htmlchars($g); ?>"> <i class="icon-download"></i>
                        <?php echo __('Export'); ?>
                            </a>
                </div>
            </div>
            <?php
}
?>
    </form>
    <script>
        $.drawPlots(<?php echo JsonDataEncoder::encode($report->getPlotData()); ?>);
    </script>
