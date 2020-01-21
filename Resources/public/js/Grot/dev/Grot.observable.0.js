// //https://bumbu.github.io/javascript-observer-publish-subscribe-pattern/
/**
 * @var {Object} Grot "Top" namespace asignado a un object literal
 */
var Observable = {
    observers: []
    , addObserver: function(topic, observer) {
        this.observers[topic] || (this.observers[topic] = [])

        this.observers[topic].push(observer)
    }
    , removeObserver: function(topic, observer) {
        if (!this.observers[topic])
            return;

        var index = this.observers[topic].indexOf(observer)

        if (~index) {
            this.observers[topic].splice(index, 1)
        }
    }
    , notifyObservers: function(topic, message) {
        if (!this.observers[topic])
            return;

        for (var i = this.observers[topic].length - 1; i >= 0; i--) {
            this.observers[topic][i](message)
        };
    }
}

Observable.addObserver('cart', function(message){
    console.log("First observer message:" + message)
})

Observable.addObserver('notificatons', function(message){
    console.log("Second observer message:" + message)
})

Observable.notifyObservers('cart', 'test 1')
// First observer message:test 1

Observable.notifyObservers('notificatons', 'test 2')
// Second observer message:test 2



/*

Grot.observerList = function () {
    this.observerList = [];
}

Grot.observerList.prototype.add = function( obj ){
    return this.observerList.push( obj );
};

Grot.observerList.prototype.empty = function(){
    this.observerList= [];
};
Grot.observerList.prototype.count = function(){
    return this.observerList.length;
};

Grot.observerList.prototype.get = function( index ){
    if( index > -1 && index < this.observerList.length ){
        return this.observerList[ index ];
    }
};

Grot.observerList.prototype.indexOf = function( obj, startIndex ){
    var i = startIndex;

    while( i < this.observerList.length ){
        if( this.observerList[i] === obj ){
            return i;
        }
        i++;
    }

    return -1;
};

Grot.observerList.prototype.removeAt = function( index ){
    this.observerList.splice( index, 1 );
};


Grot.subject = function () {
    this.observers = new Grot.observerList();
}

Grot.subject.prototype.addObserver = function( observer ){
    this.observers.add( observer );
};

Grot.subject.prototype.removeObserver = function( observer ){
    this.observers.removeAt( this.observers.indexOf( observer, 0 ) );
};

Grot.subject.prototype.notify = function( context ){
    var observerCount = this.observers.count();
    for(var i=0; i < observerCount; i++){
        this.observers.get(i).update( context );

    }
};


*/
