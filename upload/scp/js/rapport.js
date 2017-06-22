moment.locale('fr');

/*CONFIG*/
app.factory('stockFactory',['$http','$rootScope','$httpParamSerializerJQLike',function($http,$rootScope,$httpParamSerializerJQLike){
    var stock;
    return {
        query : function(stock) {
             return $http({method: 'POST',
                    url: './ajaxs.php/stock/'+stock,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                })
                .then(function(data){
                    stock = data;
                    return stock;
                })
         },
         getSN : function(reference,agent){
             return $http({method: 'POST',
                    url: './ajaxs.php/stock/sn/'+reference+'/'+agent,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                });
         },
         createDocEntete : function(org,stock){
             return $http({method: 'POST',
                    url: './ajaxs.php/docSage/entete/'+org+'/'+stock,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                });
         },
         createDocument : function(org,stock,lines){
             return $http({method: 'POST',
                    url: './ajaxs.php/docSage/createDoc',
                    data: {org:org,
                           stock:stock,
                           lines:lines},
                    headers: {'Content-Type': 'application/json'}
                });
         },
         getStock: function() {
             return stock;
         }
    }
}]);


//récupération des informations (Rapports et horaires) + Ajout d'un rapport ou maj d'un horaires
app.factory('rapportFactory',['$http','$window',function($http,$window){
   return{
       getRapports: function(ticketID) {
             //return the promise.
             return $http({method: 'POST',
                            url: './Request/Rapport.php',
                            data: $.param({request: 'getRapports',
                                           ticketID:ticketID
                                          }),
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                        })
                       .then(function(result) {
                            //resolve the promise as the data
                            return result.data;
                        });
        },
        getHoraires: function(rapportID) {
             //return the promise.
             return $http({method: 'POST',
                            url: './Request/Rapport.php',
                            data: $.param({request: 'getRapportsHoraires',
                                           rapportID:rapportID
                                          }),
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                        })
                       .then(function(result) {
                            //resolve the promise as the data
                            return result.data;
                        });
        },
       getStock: function(rapportID) {
             //return the promise.
             return $http({method: 'POST',
                            url: './Request/Rapport.php',
                            data: $.param({request: 'getRapportStock',
                                           rapportID:rapportID
                                          }),
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                        })
                       .then(function(result) {
                            //resolve the promise as the data
                            return result.data;
                        });
        },
       addHR: function(data){
           return $http({method: 'POST',
                            url: './Request/Rapport.php',
                            data: data,
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                        });
       },
       printRapport : function(ticketID,rapportID,img=undefined){
            return $http({method: 'POST',
                url: './tickets.php?id='+ticketID+'&a=printR&idR='+rapportID,
                data: {img:img},
                headers: {'Content-Type': 'application/json'}
            })
            .then(function(data){
                return data.data;
                //var win = $window.open(data.data, 'Download');
                //$window.location.assign(data.data);
                //$window.open(data.data);
            });
       }
   };
}]);

app.controller("rapportCtrl",["$scope","rapportFactory","stockFactory","$rootScope", function($scope,rapportFactory,stockFactory,$rootScope){
    //Init
    $scope.init = function(ticketID,staffID,TopicID){
        $scope.ticketID = ticketID;
        $scope.staffID = staffID;
        $scope.TopicID = TopicID;
        //Récupération des rapports ainsi que des horaires
        rapportFactory.getRapports($scope.ticketID).then(function(rapports){
            if(rapports.length > 0){
            $scope.rapports = rapports;
            $scope.rapportID = $scope.rapports[0].id;

            //Récupération des horaires pour chaque rapports.
            angular.forEach($scope.rapports,function(value,key){
                rapportFactory.getHoraires(value.id).then(function(horaires){
                    value.horaires = horaires;
                    value.totalHours = moment.duration(0,'h');
                    angular.forEach(value.horaires,function(horaire,key){
                        horaire.arrive_inter = moment(horaire.arrive_inter,"YYYY/MM/DD HH:mm:ss");
                        horaire.depart_inter = moment(horaire.depart_inter,"YYYY/MM/DD HH:mm:ss");

                        var temp = moment.duration(horaire.depart_inter.diff(horaire.arrive_inter));
                        horaire.nbHours = temp._data.hours + ":" + temp._data.minutes;

                        //temps total sur un rapport
                        value.totalHours.add(temp._data.hours,'h');
                        value.totalHours.add(temp._data.minutes,'m');
                    });
                });
                rapportFactory.getStock(value.id).then(function(stock){
                    value.stock = stock;
                });
            });
            }
        });
    }

    $scope.initRapport = function(rapports){
        if(rapports.length > 0){
            $scope.rapports = rapports;
            $scope.rapportID = $scope.rapports[0].id;

            //Récupération des horaires pour chaque rapports.
            angular.forEach($scope.rapports,function(value,key){
                //value.horaires = horaires;
                value.totalHours = moment.duration(0,'h');
                angular.forEach(value.horaires,function(horaire,key){
                    horaire.arrive_inter = moment(horaire.arrive_inter,"YYYY/MM/DD HH:mm:ss");
                    horaire.depart_inter = moment(horaire.depart_inter,"YYYY/MM/DD HH:mm:ss");

                    var temp = moment.duration(horaire.depart_inter.diff(horaire.arrive_inter));
                    horaire.nbHours = temp._data.hours + ":" + temp._data.minutes;

                        //temps total sur un rapport
                    value.totalHours.add(temp._data.hours,'h');
                    value.totalHours.add(temp._data.minutes,'m');
                 });
            });
            if(!$scope.$$phase) {
                $scope.$apply();
            }
        }
    }

    $scope.setRapportID = function($event,id,rapportID){
        $('.col-md-4.rapport').removeClass('active');
        $('#'+id+'.col-md-4.rapport').addClass('active');
        $('.rapports tbody tr').removeClass('active');
        $($event.currentTarget).addClass('active');
        $('.eachRapport.active').removeClass('active');
        $('#'+id+'.eachRapport').addClass('active');
        $scope.rapportID = rapportID;
    }

    $scope.tempsPasse = function(duration){
        var totalHours = duration.as('seconds');
        var days = Math.floor(totalHours / 27900);
        if(days >= 1){
            totalHours = totalHours - (days * 27900);
        }
        var hours = Math.floor(totalHours / 3600);
        if(hours >= 1){
            totalHours = totalHours - (hours * 3600);
        }
        var minutes = Math.floor(totalHours / 60);
        return "<p>" + days + "  Jours</p> \
                <p>" + hours + "  Heures & " + minutes + " Minutes</p>";
    }

    $scope.addRapport = function(){

        //Récupération des contrat ou instal (a changer)
        if($('input[value="Contrat"]').is(':checked')){
            $scope.contrat = $('#selectContrat').val();
            $scope.instal = 0;
        } else if($('input[value="Formation"]').is(':checked')){
            $scope.instal = 0;
            $scope.contrat = 0;
        } else {
            $scope.instal = $('#selectInstal').val();
            $scope.contrat = 0;
        }

        var comments = $('#new_symptomesObservations').val();

        var data = $.param({request: 'addHoraires',
                        ticket_id:$scope.ticketID,
                        rapport_id:null,
                        topic_id:$scope.TopicID,
                        agent_id:$scope.staffID,
                        date_inter:$scope.date_new_inter,
                        arrive_inter:$scope.arrive_new_inter,
                        depart_inter:$scope.depart_new_inter,
                        symptomesObservations:comments,
                        contrat:$scope.contrat,
                        instal:$scope.instal,
                        sortieStock:JSON.stringify($scope.stockOut)
                        });
        rapportFactory.addHR(data);
        location.reload();
        //window.location = window.location.href;
    }

    $scope.insertOrUpdateHoraire = function(){
        var comments = $('#symptomesObservations').val();

        if($scope.toUpdate !== undefined){
            var data = $.param({request: 'updateHoraire',
                        horaire_id:$scope.toUpdate,
                        date_inter:$scope.date_inter,
                        arrive_inter:$scope.arrive_inter,
                        depart_inter:$scope.depart_inter,
                        symptomesObservations:comments
                    });
            rapportFactory.addHR(data);
        } else {
            var data = $.param({request: 'addHoraires',
                        ticket_id:null,
                        rapport_id:$scope.rapportID,
                        agent_id:null,
                        date_inter:$scope.date_inter,
                        arrive_inter:$scope.arrive_inter,
                        depart_inter:$scope.depart_inter,
                        symptomesObservations:comments,
                        contrat:null,
                        instal:null
                        });
            rapportFactory.addHR(data);
        }
        location.reload();
    }

    $scope.showUpdate = function(idR,idH,idHoraire){
        if($scope.alreadyEditingHoraire == undefined || $scope.alreadyEditingHoraire == false){
            if(idR !== undefined){
                $scope.toUpdate = idHoraire;
                    //Affectation des champs
                var horaire = $scope.rapports[idR].horaires[idH];

                $scope.date_inter = horaire.arrive_inter.format('DD/MM/YYYY');
                $scope.arrive_inter = horaire.arrive_inter.format('HH:mm');
                $scope.depart_inter = horaire.depart_inter.format('HH:mm');
                $('#symptomesObservations').val(horaire.comment);
                $('#addTimeDiv .redactor-editor').last().html(horaire.comment);
                $('#addTimeDiv .redactor-editor').last().attr('placeholder','');
            } else {
                $scope.toUpdate = undefined;
            }
            $('#addTimeDiv').css('display','block');
            $scope.alreadyEditingHoraire = true;
        }
    }

    $scope.unShowUpdate = function($event){
        $($event.currentTarget).parent().css('display','none');
        $scope.alreadyEditingHoraire = false;
    }

    /*STOCK*/
    $scope.getStock = function(stock){
        //$scope.agent = agent;

        //display balls
        $('.fixed-right').css('display','none');
        $('.ticket_right').css('height','auto');
        $('.balls').css('display','block');

        stockFactory.query(stock).then(function(data){
            $scope.stock = data.data;
            $rootScope.$broadcast('STOCK', {stocks:$scope.stock,stock:stock});
            $('.balls').css('display','none');
            $('.stock').css('display','block');

        });
    }

    $scope.$on('STOCKOUT', function(response,stockOut) {
        $scope.$apply(function() {
            $scope.stockOut = stockOut;
        });
    })

    $scope.pdfjsframe = undefined;
    $scope.displayPDF = function(pdf) {
        //Ajout de l'iframe.
        if($scope.pdfjsframe == undefined){
            $('#signature .modal-body').append('<iframe id="pdfFrame" src="./viewer.html#zoom=page-fit"></iframe>');
        } else {
            $('#signature .modal-body #pdfFrame').replaceWith('<iframe id="pdfFrame" src="./viewer.html#zoom=page-fit"></iframe>');
        }

        $scope.pdfjsframe = document.getElementById('pdfFrame');
        //Ajout du PDF dans la vue
        $scope.pdfjsframe.onload = function() {
            var pdfApp = $scope.pdfjsframe.contentWindow.PDFViewerApplication;
            pdfApp.open(pdf);
        };
    }

    $scope.removePDFView = function($event){
        var button = $event.currentTarget;
        if($(button).siblings().text() == "Valider")
            $scope.cancelSignature($event);
        else
            $scope.cancelPDF();
    }

    $scope.createPDF = function(img=undefined){
        rapportFactory.printRapport($scope.ticketID,$scope.rapportID,img).then(function(pdf){
            $scope.displayPDF(pdf);
        });
    }

    $scope.cancelPDF = function(){
        $('#signature').modal('toggle');
        $('#pdfFrame').remove();
        $scope.pdfjsframe = undefined;
    }

    $scope.printRapport = function(){
        $('#signature').modal('toggle');
        $scope.createPDF();
    }

    $scope.signaturePad = undefined;
    $scope.displaySignature = function($event){
        if($($event.currentTarget).text() == "Valider"){
            //window.open($scope.signaturePad.toDataURL());
            $scope.validSignature($event,$scope.signaturePad.toDataURL("image/jpeg"));
            //console.log($scope.signaturePad.toDataURL());
        } else {
            $($event.currentTarget).text('Valider');

            var canvas = $("#signature-pad");
            canvas.css('display','block');
            canvas[0].width = $('#signature .modal-body').width();
            canvas[0].height = '300';

            if($scope.signaturePad === undefined){
                $scope.signaturePad = new SignaturePad(canvas[0], {
                  backgroundColor: 'rgb(255, 255, 255)',
                  penColor: 'rgb(0, 0, 0)',
                  minWidth: 1,
                  maxWidth: 1,
                  dotSize: 1,
                  throttle: 50
                });
            }
        }
    }

    $scope.cancelSignature = function($event){
        $($event.currentTarget).siblings().text('Signer');
        $("#signature-pad").css('display','none');
        $scope.signaturePad = undefined
    }

    $scope.validSignature = function($event,img){
        $($event.currentTarget).text('Signer');
        $("#signature-pad").css('display','none');
        $scope.signaturePad = undefined

        $scope.createPDF(img);
    }

}]);

//filtre de capitalization.
app.filter('capitalize', function() {
    return function(input) {
      return (!!input) ? input.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();}) : '';
    }
});

//filtre de formatage de moment date
app.filter('mFormat', function() {
    return function(input, format) {
      return (!!input) ? input.format(format) : '';
    }
});

app.filter('pastTimes', function() {
    return function(input, $scope) {
      return (!!input) ? $scope.tempsPasse(input) : '';
    }
});

app.controller("stockCtrl",["$scope","stockFactory","$rootScope","$compile", function($scope,stockFactory,$rootScope,$compile){
    $scope.$on('STOCK', function(response,stock) {
        $scope.stock = stock.stocks;
        $scope.staffStock = stock.stock;
        $scope.stockOut = JSON.parse(JSON.stringify($scope.stock));
        $scope.displayStock = JSON.parse(JSON.stringify($scope.stock));
        $.each($scope.stockOut,function(key,value){
             value.quantite = 0;
        });
    })

    $scope.manageStock = function(index,reference,dir,$event){
        var max = $scope.stock[index].quantite;
        //VARIABLES
        var obj = $scope.stockOut[index];
        var button = $event.currentTarget;

        if (dir == 'up') {
            if (obj.quantite < max) {
                //TEST SI L'ARTICLE EST SERIALISE.
                if (obj.suiviStock == 1){
                    //Récupération des numero de serie lié à cet article.
                    var tr = $(button).closest('tr');
                    tr.children().hide();
                    tr.append('<td colspan="3"><div class="loadingSN"><div class="ball"></div><div class="ball1"></div></div></td>');
                    stockFactory.getSN(reference,$scope.staffStock).then(function(SN){
                        //Trier les sn par rapport a ceux deja selectionner
                        var serialNumbers = SN.data.filter(function(e) { return obj.sn.indexOf(e) == -1; });

                        //affichage du numero de serie
                        $('.loadingSN',tr).parent().remove();
                        tr.append('<td colspan="2"><select></select></td>');
                        var select = tr.find('select');
                        $.each(serialNumbers,function(key,value){
                            select.append('<option>'+value+'</option>');
                        });
                        tr.append($compile("<td><button class='btn btn-sucess' ng-click=\"manageSN($event,'"+index+"','up')\">Valider</button></td>")($scope));
                    });
                } else {
                   obj.quantite += 1;
                   $scope.displayStock[index].quantite = parseInt($scope.displayStock[index].quantite) - 1;
                }
            }
        } else {
            if (obj.quantite > 0) {
                if (obj.suiviStock == 1){
                    //Récupération des numero de serie lié à cet article.
                    var tr = $(button).closest('tr');
                    tr.children().hide();

                    //affichage du numero de serie
                    $('.loadingSN',tr).parent().remove();
                    tr.append('<td colspan="2"><select></select></td>');
                    var select = tr.find('select');
                    $.each(obj.sn,function(key,value){
                        select.append('<option>'+value+'</option>');
                    });
                    tr.append($compile("<td><button class='btn btn-sucess' ng-click=\"manageSN($event,'"+index+"','dwn')\">Valider</button></td>")($scope));
                    /*obj.quantite -= 1;*/
                } else {
                    obj.quantite -= 1;
                    $scope.displayStock[index].quantite = parseInt($scope.displayStock[index].quantite) + 1;
                }
            }
        }

    }

    $scope.manageSN = function($event,index,dir){
        //décompte en temps réel
        dir == 'up' ? $scope.stock[index].quantite += 1 : $scope.stock[index].quantite -= 1;

        var obj = $scope.stockOut[index];
        dir == 'up' ? obj.quantite += 1 : obj.quantite -= 1;
        //get selected serial number
        var button = $event.currentTarget;
        var sn = $(button).parent().prev().children('select').val();

        //ajout du serial number
        dir == 'up' ? obj.sn.push(sn) : obj.sn.splice(obj.sn.indexOf(sn),1);

        //MAJ VISUEL
        var tr = $(button).closest('tr');
        tr.children().slice(-2).remove();
        tr.children().show();
    }

    $scope.createDocument = function(org){
        var obj = $scope.stockOut.filter(function(e) { return e.quantite > 0});
        //console.log(obj);

        $('.balls').css('display','block');
        stockFactory.createDocument(org,$scope.stock[0].stock,JSON.stringify(obj)).then(function(){
            $('.balls').css('display','none');
            $('.ticket_right').prepend('<div class="order-success svg"> \
              <svg xmlns="http://www.w3.org/2000/svg" width="72px" height="72px"> \
                <g fill="none" stroke="#8EC343" stroke-width="2"> \
                  <circle cx="36" cy="36" r="35" style="stroke-dasharray:240px, 240px; stroke-dashoffset: 480px;"></circle> \
                  <path d="M17.417,37.778l9.93,9.909l25.444-25.393" style="stroke-dasharray:50px, 50px; stroke-dashoffset: 0px;"></path> \
                </g> \
              </svg> \
            <script id="jsbin-source-css" type="text/css"></div>');

            setTimeout( function(){
                $('.order-success').remove();
                $('.stock').css('display','none');
                $('.fixed-right').css('display','block');
                $rootScope.$broadcast('STOCKOUT', obj);
            }  , 1500 );

        });
    }

}]);




































/*function Rapports(ticketID){
    var self = this;

    RapportAjax.getRapports(ticketID,function(report){
        report = $.parseJSON(report);
        self.rapports = [];
        $(report).each(function(number,rapport){
            RapportAjax.getRapportsHoraires(rapport['id'],function(horaires){
                horaires = $.parseJSON(horaires);
                var object = new Rapport(rapport['id']
                                        ,rapport['firstname']
                                        ,rapport['lastname']
                                        ,rapport['date_rapport']
                                        ,rapport['date_inter']
                                        ,rapport['num_affaire']
                                        ,rapport['contrat']
                                        ,rapport['instal']);
                $(horaires).each(function(num,hor){
                    object.horaires.push(new horaire(hor['id']
                                                    ,hor['arrive_inter']
                                                    ,hor['depart_inter']
                                                    ,hor['comment']));
                });
                self.rapports.push(object);
            });
        });
    });

    self.addHoraires = function(ticket_id,rapport_id,agent_id,date_inter,arrive_inter,depart_inter,symptomesObservations,contrat,instal,num_affaire,callback){
        RapportAjax.addHoraires(ticket_id,rapport_id,agent_id,date_inter,arrive_inter,depart_inter,symptomesObservations,contrat,instal,num_affaire,function(){
            console.log(rapport_id);
            callback();
        });
    }

    self.getHoraire = function(id){
        var horaires = [];
        $(self.rapports).each(function(number,rapp){
            $(rapp.horaires).each(function(number,hor){
                if(hor.id == id) horaires.push(hor);
            });
        });
        return horaires[0];
    }

    self.updateHoraire = function(horaire_id,date_inter,arrive_inter,depart_inter,symptomesObservations,callback){
        RapportAjax.updateHoraire(horaire_id,date_inter,arrive_inter,depart_inter,symptomesObservations,function(){
            callback();
        });
    }
}


function Rapport(id,firstname,lastname,date_rapport,date_inter,num_affaire,contrat,instal){
    var self = this;
    self.id = id;
    self.firstname = firstname;
    self.lastname = lastname;
    self.date_rapport = date_rapport;
    self.date_inter = date_inter;
    self.num_affaire = num_affaire;
    self.contrat = contrat;
    self.instal = instal;
    self.horaires = [];
}

function horaire(id,arrive_inter,depart_inter,comment){

    var self = this;
    self.id = id;
    self.arrive_inter = arrive_inter;
    self.depart_inter = depart_inter;
    self.comment = comment;
}

class RapportAjax{

    static doAjax(data,callback){
        $.ajax({
            url:'./Request/Rapport.php'
            ,method:'POST'
            ,data:data
        }).success(callback);
    }

    static getRapports(ticketID,callback){
        var data = {
            request:'getRapports'
            ,ticketID:ticketID
        };
        this.doAjax(data,callback);
    }

    static getRapportsHoraires(rapportID,callback){
        var data = {
            request:'getRapportsHoraires'
            ,rapportID:rapportID
        };
        this.doAjax(data,callback);
    }

    static addHoraires(ticket_id,rapport_id,agent_id,date_inter,arrive_inter,depart_inter,symptomesObservations,contrat,instal,num_affaire,callback){
         var data = {
            request:'addHoraires'
             ,ticket_id:ticket_id
             ,rapport_id:rapport_id
             ,agent_id:agent_id
             ,date_inter:date_inter
             ,arrive_inter:arrive_inter
             ,depart_inter:depart_inter
             ,symptomesObservations:symptomesObservations
             ,contrat:contrat
             ,instal:instal
             ,num_affaire:num_affaire
            };
         this.doAjax(data,callback);
    }

    static updateHoraire(horaire_id,date_inter,arrive_inter,depart_inter,symptomesObservations,callback){
         var data = {
            request:'updateHoraire'
            ,horaire_id
            ,date_inter
            ,arrive_inter
            ,depart_inter
            ,symptomesObservations
        };
        this.doAjax(data,callback);
    }
}*/
