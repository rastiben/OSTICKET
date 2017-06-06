<div class="block rapportListe" style="text-align:center">
       <div class="loading blank">
               <div class="info">
                   <h3>Récupération des informations</h3>
                   <div class="sk-circle">
                    <div class="windows8">
                        <div class="wBall" id="wBall_1">
                            <div class="wInnerBall"></div>
                        </div>
                        <div class="wBall" id="wBall_2">
                            <div class="wInnerBall"></div>
                        </div>
                        <div class="wBall" id="wBall_3">
                            <div class="wInnerBall"></div>
                        </div>
                        <div class="wBall" id="wBall_4">
                            <div class="wInnerBall"></div>
                        </div>
                        <div class="wBall" id="wBall_5">
                            <div class="wInnerBall"></div>
                        </div>
                    </div>
               </div>
           </div>
        </div>

        <table class="rapports table table-striped" style="margin-bottom:0px">
           <thead>
               <tr>
                    <th><strong>Ticket</strong></th>
                    <th><strong>Date Création</strong></th>
                    <th><strong>Client</strong><span data-filter="org" class="glyphicon glyphicon-filter" aria-hidden="true"></span></th>
                    <th><strong>Auteur</strong><span data-filter="auteur" class="glyphicon glyphicon-filter" aria-hidden="true"></span></th>
                    <th><strong>Type</strong><span data-filter="type" class="glyphicon glyphicon-filter" aria-hidden="true"></span></th>
                </tr>
           </thead>
           <tbody>
            <tr ng_click="setRapportID($event,$index,rapport.id)" ng-repeat="rapport in rapports track by $index" ng-class="$first ? 'active' : ''" id="{{$index}}">
                <td>{{rapport.id}}<span ng-show="rapport.stock.length" class="glyphicon glyphicon-shopping-cart"></span></td>
                <td>{{rapport.date_rapport}}</td>
                <td>{{rapport.ticket__user__org_name}}</td>
                <td>{{rapport.staff__firstname}} {{rapport.staff__lastname}}</td>
                <td>{{rapport.topic__topic}}</td>
            </tr>
        </tbody>
        </table>

        <?php
        if ($count) {
            echo sprintf('<ul class="pagination">%s</ul>',
                    $pageNav->getBSPageLinks(false,false));
        }
        ?>
    </div>

    <div ng-repeat="rapport in rapports track by $index" style="float:none" class="eachRapport col-md-12 col-lg-12 col-xs-12" ng-class="$first ? 'active' : ''" id="{{$index}}">
        <div class="col-md-12 col-xs-12 rapport">
            <div class="col-lg-4 col-md-12 col-xs-12" id="borderIdentity">
                <div class="identity" id="{{rapport.id}}">
                    <div class="title">
                        <div class="titling"></div>
                        <div class="commentTitle horaires">
                            <p>Rapports n° {{rapport.id}} :</p>
                        </div>
                    </div>
                    <div class="content">
                        <span id="date_inter">Intervenant : {{rapport.firstname}} {{rapport.lastname}}</span>

                        <div ng-repeat="horaire in rapport.horaires" class="horaire">
                            <div>
                                <span ng-style="">Intervention du {{horaire.arrive_inter | mFormat:'dddd DD MMMM YYYY' | capitalize}}</span>
                            </div>
                            <div>
                                <div class="floatSDL">
                                <p id="startDate">{{horaire.arrive_inter | mFormat:'HH:mm' }}</p>
                                </div>
                                <div class="floatEDR">
                                <p id="endDate">{{horaire.depart_inter | mFormat:'HH:mm' }}</p>
                                </div>
                                <p class="greenLine">{{horaire.nbHours}}</p>
                            </div>
                        </div>

                    </div>
                    <div class="totalHour">
                        <div id="totalTitle">
                            <span>Total</span>
                        </div>
                        <div id="total" ng-bind-html="rapport.totalHours | pastTimes:this">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 col-md-12 col-xs-12" id="borderProperty">
                <div class="property" id="<?php echo $rapport['id'] ?>">
                    <div class="title">
                        <div class="titling"></div>
                        <h4>Description : </h4>
                    </div>
                    <div class="content">
                    <div ng-repeat="horaire in rapport.horaires">
                            <div class="comment">
                                <div class="titleComment">
                                <div class="green"></div>
                                    <div class="commentTitle"><p>Intervention du {{horaire.arrive_inter | mFormat:'dddd DD MMMM YYYY' | capitalize}}</p></div>
                                </div>
                            <div class="commentContent">
                                    <span ng-bind-html="horaire.comment"></span>
                                    <hr style="margin-top: 10px;margin-bottom: 10px;">
                                         <!--SORTIE DE STOCK-->
                                    <div class="articleSortie" style="margin-bottom:0px" ng-repeat="article in rapport.stock">{{article.reference}} ({{article.quantite}})</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
