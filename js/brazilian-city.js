;(function($, undefined) {
    $(function() {
        var originalState = 0;

        //Aplicando chosen nos selects
        $(".country-selector-list select").chosen({
            disable_search_threshold: 10,
            placeholder_text_single: "Selecione uma opção",
            no_results_text: "Nenhum registro encontrado."
        });
        
        //Aplicando load de cidades quando um estado é selecionado
        var stateSelect   = $('select[name*="state_id"]');
        stateSelect.change(function() {
            var $this = $(this);
            var $list = $this.parents('ul');
            var citySelect = $list.find('select[name*="city_id"]');

            if ($this.val() !== originalState) {
                originalState = $this.val();

                citySelect.empty();
                citySelect.html('<option value="">Carrengado...</option>').trigger("chosen:updated");

                loadCitiesList($this.val(), function(response) {
                    citySelect.empty();

                    var options   = '';
                    $.each(response, function(k, v) {
                        options += '<option value="'+k+'">'+v+'</option>';
                    });
                    citySelect.html(options).trigger("chosen:updated");

                });
            }
        });
        
        /**
         * Carrega a lista de cidades para o estado
         * @param int stateId
         * @param function callback
         * @returns void
         */
        function  loadCitiesList(stateId, callback) {
            var storageKey = "cities" + stateId;
            var cities = getLocalStorage(storageKey);

            if (cities !== null) {
                callback(JSON.parse(cities));
            } else {
                $.ajax({
                    url: AcfBrazilianCity.ajaxurl,
                    type: 'post',
                    dataType: 'json',
                    data: {
                        action: 'get_list_state_cities',
                        stateId: stateId
                    },
                    success: function (response) {
                        callback(response);
                        setLocalStorage(storageKey, JSON.stringify(response));
                    }
                });
            }
        }

        function setLocalStorage(key, value, expires) {
            if (expires === undefined || expires === 'null') { 
                var expires = 18000; 
            } // default: 5h

            var date = new Date();
            var schedule = Math.round((date.setSeconds(date.getSeconds()+expires))/1000);

            localStorage.setItem(key, value);
            localStorage.setItem(key+'_time', schedule);
        }

        function getLocalStorage(key) {
            var date     = new Date();
            var current = Math.round(+date/1000);

            // Get Schedule
            var stored_time = localStorage.getItem(key+'_time');

            if (stored_time === undefined || stored_time === 'null') { 
                var stored_time = 0; 
            }

            if (stored_time < current) {
                clearLocalStorage(key);
                return null;

            } else {
                return localStorage.getItem(key);
            }
        }

        function clearLocalStorage(key) {
            localStorage.removeItem(key);
            localStorage.removeItem(key+'_time');
        }

    });
})(jQuery);
