<?php

class acf_brazilian_city_field extends acf_field {

    var $settings, $defaults;

    public function __construct()
    {
        $this->name = 'COUNTRY_FIELD';
        $this->label = 'Cidade Brasileira';
        $this->category = __("Basic",'acf');

        $this->defaults = array(
            "city_name"    => '',
            "state_name"   => '',
            "city_id"      => 0,
            "state_id"     => '',
        );

        parent::__construct();

        $this->settings = array(
            'path' => apply_filters('acf/helpers/get_path', __FILE__),
            'dir' => apply_filters('acf/helpers/get_dir', __FILE__),
            'version' => '1.0.1'
        );
    }
    
    

    function create_field( $field )
    {
        global $wpdb;
        
        $field['value'] = isset($field['value']) ? $field['value'] : '';
        
        $fieldName  = $field['name'];
        $city_id    = (isset($field['value']['city_id'])) ? $field['value']['city_id'] : 0;
        $state_id   = (isset($field['value']['state_id'])) ? $field['value']['state_id'] : 0;

        
        $cities     = $this->list_cities($state_id);

        //Carregando Estados
        $states = array("__" => "");
        $statesResults = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."states ORDER BY name ASC");
        foreach ($statesResults AS $state)
        {
            $states[$state->id] = $state->name;
        }
        
        ?>

            <ul class="country-selector-list">
                <li id="field-<?php echo $fieldName; ?>[state_id]">
                        <strong><?php _e("Selecione o estado", 'acf'); ?></strong><br />

                        <?php

                        $state_field = $field['name'] . '[state_id]';
                        do_action('acf/create_field', array(
                            'type'      =>  'select',
                            'name'    =>  $state_field,
                            'value'     =>  $state_id,
                            'choices' =>  $states,
                        ));

                        ?>
                </li>
                <li id="field-<?php echo $fieldName; ?>[city_id]">
                        <strong><?php _e("Selecione a cidade", 'acf'); ?></strong><br />
                        <?php

                        $city_field = $field['name'] . '[city_id]';
                        do_action('acf/create_field', array(
                            'type'    =>  'select',
                            'name'    =>  $city_field,
                            'value'   =>  $city_id,
                            'choices' =>  $cities,
                        ));

                        ?>
                </li>
            </ul>

        <?php
    }
    
    function update_value($value, $post_id, $field)
    {
        $value['city_name']    = $this->city_name($value['city_id']);
        $value['state_name']   = (isset($value['state_id']) && $value['state_id'] !== 0) ? $this->state_name($value['state_id']) : '';

        return $value;
    }

    function format_value_for_api($value, $post_id, $field)
    {
        
        $value['city_name']    = $this->city_name($value['city_id']);
        $value['state_name']   = (isset($value['state_id']) && $value['state_id'] !== 0) ? $this->state_name($value['state_id']) : '';

        return $value;
    }
    
    function input_admin_enqueue_scripts()
    {
        wp_register_script('acf-brazilian-city', $this->settings['dir'] . 'js/brazilian-city.js', array('acf-input'), $this->settings['version']);
        wp_register_script('acf-input-chosen', $this->settings['dir'] . 'js/chosen.jquery.min.js', array('jquery'), $this->settings['version']);
        wp_register_style('acf-input-chosen', $this->settings['dir'] . 'css/chosen.min.css', array(), $this->settings['version']);

        wp_localize_script( 'acf-brazilian-city', "AcfBrazilianCity", array(
            "ajaxurl" => admin_url("admin-ajax.php"),
        ) );

        // scripts
        wp_enqueue_script(array(
            'acf-brazilian-city',
            'acf-input-chosen',
        ));

        // styles
        wp_enqueue_style(array(
            'acf-input-chosen',
        ));
    }

    


    /**
     * Retorna todas as cidades de um determinado estado
     * @global type $wpdb
     * @param string $state_id identificador do estados Ex.: 'ES'
     * @return array "ID" => "Nome"
     */
    protected function list_cities($state_id)
    {
        global $wpdb;
        $cities_results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."cities WHERE state_id ='".$state_id."' ORDER BY name ASC");
        $cities = array(); 

        foreach ($cities_results AS $city)
        {
            $cities[$city->id] = $city->name;
        }

        return $cities;
    }

    /**
     * Retorna o nome de uma cidade especifica
     * @global type $wpdb
     * @param int $city_id identificador da cidade
     * @return mixed
     */
    protected function city_name($city_id)
    {
        global $wpdb;
        $city = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."cities WHERE id = '".$city_id."'");

        if ($city)
        {
            return $city->name;
        }
        else
        {
            return false;
        }
    }

    /**
     *  Retorna o nome de um estado especifico
        * @global type $wpdb
        * @param int $state_id
        * @return mixed
        */
    protected function state_name($state_id)
    {
        global $wpdb;
        $state = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."states WHERE id = '".$state_id."'");

        if ($state) {
            return $state->name;
        } else {
            return false;
        }
    }
}

add_action('wp_ajax_get_list_state_cities', 'get_list_state_cities');
add_action('wp_ajax_nopriv_gt_list_state_cities', 'et_list_state_cities');
/**
 * Disponibilia via ajax a lista de cidades para um determinado estado
 * @global type $wpdb
 */
function get_list_state_cities()
{
    global $wpdb;

    $state_id =  substr(trim($_REQUEST['stateId']),0,2);

    $cities_results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."cities WHERE state_id ='".$state_id."' ORDER BY name ASC");
    $cities = array();

    if ($cities_results)
    {
        foreach ($cities_results AS $city)
        {
            $cities[$city->id] = $city->name;
        }
    }

    ob_end_clean();
    header("Content-Type: application/json");
    echo json_encode($cities);
    die();
}

new acf_brazilian_city_field();