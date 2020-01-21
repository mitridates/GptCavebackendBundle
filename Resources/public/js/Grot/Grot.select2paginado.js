(function(Grot, undefined) {
    /**                                                                                                                                                                                   
     * Valores por defecto para Select2
     * @property {Object[]} defaults
     */  
    let defaults = {
            options: {
                minimumInputLength: 3,
                width: '100%',
                placeholder: 'Select option',
                allowClear: true
            },
            parameters: {
                format: 'select2',
                term: '',
                page: 1
            },
            callbackOptions: {
                url: '', 
                dataType: 'json', 
                delay: 250, 
                method:'POST',
                cache: true
            }
    },
    /**                                                                                                                                                                                   
     * Metodos disponibles para grotte.select2paginado
     * @type {Object[]}
     */           
    methods=
    {
        /**                                                                                                                                                                                   
         * Unico metodo
         * @param {JSON} o Opciones.
         */         
        init : function(defaultArgs){

            let selectOptions = {};

            let callback = {
                data: function (params) {
                        return Object.assign({}, defaultArgs.parameters, params);//Agregamos parámetros para post/get
                },
                processResults: function (data, params) {
                    return {
                        results: data.out,
                        pagination: {
                         more: (typeof data.pager ==='undefined')?  false : data.pager.more//
                       }
                    };
                }
                };
            defaultArgs.options['ajax']= Object.assign(callback, defaultArgs.callbackOptions);

            //selectOptions['ajax']= Object.assign(callback, o.callbackOptions);//Creamos la propiedad "ajax" y asignamos los callback
            //Object.assign(selectOptions, o.options);//Agregamos el resto de opciones al select2
            //Añadimos la funcionalidad al selector
            this.select2(defaultArgs.options);
            //fix select2: se deshabilita al cargarlo dinamicamente en ventanas modales
            $.ui.dialog.prototype._allowInteraction = function (e) { return true; };
            return Grot;
        }
       };
    
   /**                                                                                                                                                                                   
    * Añadimos a parent
    * @name select2paginado
    * @public
    * @function
    * @param {string} path url
    * @param {Object[]|null} o optiones de select2
     */         
    Grot.prototype.select2paginado = function(path, o) {
            let thisDefaults = Object.create(defaults),
                options = o||{};

        thisDefaults.callbackOptions.url = path;
        Object.keys(options).forEach(function(key) {
            thisDefaults.options[key]= options[key];
        })
        methods.init.call($(this._selector), thisDefaults);
        return this;

        };       
}( window.Grot = window.Grot || {}));