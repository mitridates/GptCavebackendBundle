/*
 * Intenta obtiener lat,lng
 * Usa la API de geolocalización se publica a través del objeto navigator.geolocation.
 * Si no esta disponible/no soportado/denegado por el usuario se intenta con la API de google
 */
Grot.geo = function(ga_key){
    "use strict";
    this._ga_key = ga_key||false;
    this._errors = [];
    this._position,
    this._provider,
    this._setProvider = function(provider){
        this._provider = provider;
    },
    this._setPosition = function(lat,lng){
        this._position = {lat: lat,lng: lng };
        return this;
    };
    this.tryGeolocation();

};

Grot.geo.prototype.tryGoogleApi = function(){
    var context = this;
    if(!context._ga_key) {
        context._errors.push({title: "Google API no está definida!"});
        return;
    }
    jQuery.post( "https://www.googleapis.com/geolocation/v1/geolocate?key="+context._ga_key, function(success) {
        context._errors= [];
        context._setPosition(success.location.lat, success.location.lng)._setProvider('Google');
    }).fail(function(err) {
        context._errors.push({title: "API Geolocation error! "+err});
    });
};

Grot.geo.prototype.tryGeolocation = function(){
    var context = this;

    if (!window.location.protocol == 'https:' || ["localhost", "127.0.0.1"].indexOf(location.hostname) === -1) {
        context._errors.push({title: "Contexto inválido para navigator.geolocation!"});
        context.tryGoogleApi();
        return;
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(pos){
                context._setPosition(pos.coords.latitude, pos.coords.longitude)._setProvider('Browser');
            },
            function(error){
                context._errors.push({index: error.code, title: error.message});

                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        break;
                    case error.TIMEOUT:
                        break;
                    case error.POSITION_UNAVAILABLE:
                        break;
                }
                //context.tryGoogleApi();//da igual el error
            }/*,{maximumAge: 50000, timeout: 20000, enableHighAccuracy: true}*/
        );
    } else {
        context._errors.push({title: 'ERROR: Geolocation no soportada por este navegador.'});
        context.tryGoogleApi();
    }
};

Grot.geo.prototype.getPosition= function(){
    return this._position;
};

Grot.geo.prototype.getErrors= function(){
    return this._errors;
};
Grot.geo.prototype.toString= function(){
    return this._position.lat+','+this._position.lng;
};