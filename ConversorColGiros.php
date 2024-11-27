<?php
/*
Plugin Name: Conversor ColGiros
Description: Conversor de divisas para COP a VES y USD con opciones de personalización avanzadas.
Version: 5.5
Author: PubliDigital
*/

if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente.
}

// Encolar scripts y estilos para el frontend
function colgiros_enqueue_scripts() {
    wp_enqueue_style('colgiros-style', plugin_dir_url(__FILE__) . 'style.css');
    wp_enqueue_script('colgiros-script', plugin_dir_url(__FILE__) . 'script.js', array('jquery'), '1.4', true);
    wp_localize_script('colgiros-script', 'colgirosData', array(
        'cop_ves_rate' => get_option('colgiros_cop_ves_rate', 102.9),
        'ves_usd_rate' => get_option('colgiros_cop_usd_rate', 39.79),
        'default_amount' => number_format(get_option('colgiros_default_amount', 10000), 0, '', '.'), // Se mantiene como string formateado
        'convert_url' => get_option('colgiros_convert_url', 'https://wa.link/pz8riu'),
        'rate_position' => get_option('colgiros_rate_position', 'initial'), // Nueva línea
        'styles' => array(
            'border_radius' => get_option('colgiros_border_radius', '15'),
            'transparency' => get_option('colgiros_transparency', '100'),
            'title_font_weight' => get_option('colgiros_title_font_weight', '400'),
            'subtitle_font_weight' => get_option('colgiros_subtitle_font_weight', '400'),
            'amount_font_weight' => get_option('colgiros_amount_font_weight', '400'),
            'font_size' => get_option('colgiros_font_size', '16'),
            'title_color' => get_option('colgiros_title_color', '#0057b7'),
            'subtitle_color' => get_option('colgiros_subtitle_color', '#333'),
            'amount_color' => get_option('colgiros_amount_color', '#0057b7'),
            'button_color' => get_option('colgiros_button_color', '#0057b7'), // Nueva opción
            'amount_direction' => get_option('colgiros_amount_direction', 'left'),
            'font_family' => get_option('colgiros_font_family', 'Montserrat'),
            'input_background_color' => get_option('colgiros_input_background_color', '#e6f0ff'),
            'input_background_transparency' => get_option('colgiros_input_background_transparency', '100'),
            'input_width' => get_option('colgiros_input_width', '100'),
            'rate_spacing' => get_option('colgiros_rate_spacing', '10'),
            'shadow_enabled' => get_option('colgiros_shadow_enabled', '1'),
            'shadow_color' => get_option('colgiros_shadow_color', '#000000'),
            'calculator_background_color' => get_option('colgiros_calculator_background_color', '#ffffff'), // Nueva opción
            'calculator_background_transparency' => get_option('colgiros_calculator_background_transparency', '100'), // Nueva opción
            'casilla_border_radius' => get_option('colgiros_casilla_border_radius', '10'), // Nueva opción
            'vertical_spacing' => get_option('colgiros_vertical_spacing', '20') // Nueva opción
        )
    ));
}
add_action('wp_enqueue_scripts', 'colgiros_enqueue_scripts');

// Encolar scripts y estilos para el admin
function colgiros_enqueue_admin_styles() {
    wp_enqueue_style('colgiros-admin-style', plugin_dir_url(__FILE__) . 'admin-style.css');
    wp_enqueue_script('colgiros-admin-script', plugin_dir_url(__FILE__) . 'admin-script.js', array('jquery'), '1.4', true);
}
add_action('admin_enqueue_scripts', 'colgiros_enqueue_admin_styles');

// Añadir menú de configuración
function colgiros_settings_menu() {
    add_menu_page(
        'Conversor ColGiros',
        'Conversor ColGiros',
        'manage_options',
        'colgiros-settings',
        'colgiros_settings_page',
        'dashicons-money',
        20
    );
}
add_action('admin_menu', 'colgiros_settings_menu');

// Página de configuración
function colgiros_settings_page() {
    ?>
    <div class="wrap">
        <h1>Configuración del Conversor ColGiros</h1>
        <div class="colgiros-admin-header">
            <span>Desarrollado por:</span>
            <a href="https://www.publibga.com" target="_blank">www.publibga.com</a>
        </div>
        <form method="post" action="options.php">
            <?php
            settings_fields('colgiros_settings_group');
            do_settings_sections('colgiros-settings');
            ?>
            <div class="colgiros-admin-actions">
                <?php submit_button('Guardar'); ?>
                <input type="button" class="button-secondary" value="Guardar Plantilla" id="guardar_plantilla">
                <select id="plantillas_guardadas" name="plantillas_guardadas">
                    <option value="">Seleccionar Plantilla</option>
                    <?php
                    $plantillas = get_option('colgiros_saved_templates', array());
                    foreach ($plantillas as $nombre => $config) {
                        echo '<option value="' . esc_attr($nombre) . '">' . esc_html($nombre) . '</option>';
                    }
                    ?>
                </select>
                <input type="button" class="button-secondary" value="Restaurar Plantilla" id="restaurar_plantilla">
            </div>
        </form>
    </div>
    <?php
}

// Registrar configuraciones
function colgiros_register_settings() {
    // Registro de configuraciones de tasa
    register_setting('colgiros_settings_group', 'colgiros_cop_ves_rate', 'sanitize_text_field');
    register_setting('colgiros_settings_group', 'colgiros_cop_usd_rate', 'sanitize_text_field');
    register_setting('colgiros_settings_group', 'colgiros_default_amount', 'sanitize_text_field');
    register_setting('colgiros_settings_group', 'colgiros_convert_url', 'sanitize_text_field');

    // Registro de configuraciones estéticas
    register_setting('colgiros_settings_group', 'colgiros_border_radius', 'sanitize_text_field');
    register_setting('colgiros_settings_group', 'colgiros_transparency', 'sanitize_text_field');
    register_setting('colgiros_settings_group', 'colgiros_title_font_weight', 'sanitize_text_field');
    register_setting('colgiros_settings_group', 'colgiros_subtitle_font_weight', 'sanitize_text_field');
    register_setting('colgiros_settings_group', 'colgiros_amount_font_weight', 'sanitize_text_field');
    register_setting('colgiros_settings_group', 'colgiros_font_size', 'sanitize_text_field');
    register_setting('colgiros_settings_group', 'colgiros_title_color', 'sanitize_hex_color');
    register_setting('colgiros_settings_group', 'colgiros_subtitle_color', 'sanitize_hex_color');
    register_setting('colgiros_settings_group', 'colgiros_amount_color', 'sanitize_hex_color');
    register_setting('colgiros_settings_group', 'colgiros_button_color', 'sanitize_hex_color'); // Nueva línea
    register_setting('colgiros_settings_group', 'colgiros_amount_direction', 'sanitize_text_field');
    register_setting('colgiros_settings_group', 'colgiros_font_family', 'sanitize_text_field');
    register_setting('colgiros_settings_group', 'colgiros_input_background_color', 'sanitize_hex_color');
    register_setting('colgiros_settings_group', 'colgiros_input_background_transparency', 'sanitize_text_field');
    register_setting('colgiros_settings_group', 'colgiros_input_width', 'sanitize_text_field');
    register_setting('colgiros_settings_group', 'colgiros_rate_spacing', 'sanitize_text_field');

    // Registro de nuevas configuraciones
    register_setting('colgiros_settings_group', 'colgiros_calculator_background_color', 'sanitize_hex_color'); // Nueva línea
    register_setting('colgiros_settings_group', 'colgiros_calculator_background_transparency', 'sanitize_text_field'); // Nueva línea
    register_setting('colgiros_settings_group', 'colgiros_casilla_border_radius', 'sanitize_text_field'); // Nueva línea
    register_setting('colgiros_settings_group', 'colgiros_vertical_spacing', 'sanitize_text_field'); // Nueva línea

    // Registro de configuración de posición de tasas
    register_setting('colgiros_settings_group', 'colgiros_rate_position', 'sanitize_text_field'); // Nueva línea

    // Registro de configuraciones de sombra
    register_setting('colgiros_settings_group', 'colgiros_shadow_enabled', 'sanitize_text_field');
    register_setting('colgiros_settings_group', 'colgiros_shadow_color', 'sanitize_hex_color');

    // Registro de configuraciones de plantillas
    register_setting('colgiros_settings_group', 'colgiros_saved_templates', 'colgiros_sanitize_saved_templates');

    // Sección de configuración de tasas
    add_settings_section('colgiros_settings_section', 'Tasas de Conversión', null, 'colgiros-settings');

    add_settings_field('colgiros_cop_ves_rate', 'Tasa COP a VES', 'colgiros_cop_ves_rate_callback', 'colgiros-settings', 'colgiros_settings_section');
    add_settings_field('colgiros_cop_usd_rate', 'Tasa VES a USD', 'colgiros_cop_usd_rate_callback', 'colgiros-settings', 'colgiros_settings_section');
    add_settings_field('colgiros_default_amount', 'Monto Base (COP)', 'colgiros_default_amount_callback', 'colgiros-settings', 'colgiros_settings_section');
    add_settings_field('colgiros_convert_url', 'Enlace de Convertir', 'colgiros_convert_url_callback', 'colgiros-settings', 'colgiros_settings_section');

    // Sección de configuración estética
    add_settings_section('colgiros_style_section', 'Estilo de la Calculadora', null, 'colgiros-settings');

    add_settings_field('colgiros_border_radius', 'Redondez de Bordes Generales', 'colgiros_border_radius_callback', 'colgiros-settings', 'colgiros_style_section');
    add_settings_field('colgiros_transparency', 'Transparencia General (%)', 'colgiros_transparency_callback', 'colgiros-settings', 'colgiros_style_section');
    add_settings_field('colgiros_title_font_weight', 'Peso de la Fuente (Títulos)', 'colgiros_title_font_weight_callback', 'colgiros-settings', 'colgiros_style_section');
    add_settings_field('colgiros_subtitle_font_weight', 'Peso de la Fuente (Subtítulos)', 'colgiros_subtitle_font_weight_callback', 'colgiros-settings', 'colgiros_style_section');
    add_settings_field('colgiros_amount_font_weight', 'Peso de la Fuente (Montos)', 'colgiros_amount_font_weight_callback', 'colgiros-settings', 'colgiros_style_section');
    add_settings_field('colgiros_font_size', 'Tamaño de la Fuente (px)', 'colgiros_font_size_callback', 'colgiros-settings', 'colgiros_style_section');
    add_settings_field('colgiros_title_color', 'Color de los Títulos', 'colgiros_title_color_callback', 'colgiros-settings', 'colgiros_style_section');
    add_settings_field('colgiros_subtitle_color', 'Color de los Subtítulos', 'colgiros_subtitle_color_callback', 'colgiros-settings', 'colgiros_style_section');
    add_settings_field('colgiros_amount_color', 'Color de los Montos', 'colgiros_amount_color_callback', 'colgiros-settings', 'colgiros_style_section');
    add_settings_field('colgiros_button_color', 'Color del Botón Convertir', 'colgiros_button_color_callback', 'colgiros-settings', 'colgiros_style_section'); // Nueva línea
    add_settings_field('colgiros_amount_direction', 'Dirección de los Montos', 'colgiros_amount_direction_callback', 'colgiros-settings', 'colgiros_style_section');
    add_settings_field('colgiros_font_family', 'Fuente de la Calculadora', 'colgiros_font_family_callback', 'colgiros-settings', 'colgiros_style_section');
    add_settings_field('colgiros_input_background_color', 'Color de Fondo de Casillas', 'colgiros_input_background_color_callback', 'colgiros-settings', 'colgiros_style_section');
    add_settings_field('colgiros_input_background_transparency', 'Transparencia de Fondo de Casillas (%)', 'colgiros_input_background_transparency_callback', 'colgiros-settings', 'colgiros_style_section');
    add_settings_field('colgiros_input_width', 'Ancho de las Casillas (%)', 'colgiros_input_width_callback', 'colgiros-settings', 'colgiros_style_section');
    add_settings_field('colgiros_rate_spacing', 'Espaciado entre Tasas (px)', 'colgiros_rate_spacing_callback', 'colgiros-settings', 'colgiros_style_section');

    // Añadir campo para la posición de las tasas
    add_settings_field(
        'colgiros_rate_position',
        'Posición de Tasas de Cambio',
        'colgiros_rate_position_callback',
        'colgiros-settings',
        'colgiros_settings_section' // Puedes cambiar la sección si prefieres una diferente
    );

    // Añadir campos para la sombra
    add_settings_field('colgiros_shadow_enabled', 'Activar Sombra', 'colgiros_shadow_enabled_callback', 'colgiros-settings', 'colgiros_style_section');
    add_settings_field('colgiros_shadow_color', 'Color de la Sombra', 'colgiros_shadow_color_callback', 'colgiros-settings', 'colgiros_style_section');

    // Añadir campos para el fondo de la calculadora
    add_settings_field('colgiros_calculator_background_color', 'Color de Fondo de la Calculadora', 'colgiros_calculator_background_color_callback', 'colgiros-settings', 'colgiros_style_section'); // Nueva línea
    add_settings_field('colgiros_calculator_background_transparency', 'Transparencia de Fondo de la Calculadora (%)', 'colgiros_calculator_background_transparency_callback', 'colgiros-settings', 'colgiros_style_section'); // Nueva línea

    // Añadir campos para los bordes y espaciado de las casillas
    add_settings_field('colgiros_casilla_border_radius', 'Redondez de Bordes de las Casillas (px)', 'colgiros_casilla_border_radius_callback', 'colgiros-settings', 'colgiros_style_section'); // Nueva línea
    add_settings_field('colgiros_vertical_spacing', 'Espaciado Vertical entre Casillas (px)', 'colgiros_vertical_spacing_callback', 'colgiros-settings', 'colgiros_style_section'); // Nueva línea
}
add_action('admin_init', 'colgiros_register_settings');

// Sanitización personalizada para plantillas guardadas
function colgiros_sanitize_saved_templates($input) {
    $sanitized = array();
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = array_map('sanitize_text_field', $value);
                if (isset($value['colgiros_shadow_color'])) {
                    $sanitized[$key]['colgiros_shadow_color'] = sanitize_hex_color($value['colgiros_shadow_color']);
                }
                if (isset($value['colgiros_calculator_background_color'])) {
                    $sanitized[$key]['colgiros_calculator_background_color'] = sanitize_hex_color($value['colgiros_calculator_background_color']);
                }
            }
        }
    }
    return $sanitized;
}

// Callbacks para las configuraciones de tasa
function colgiros_cop_ves_rate_callback() {
    $rate = get_option('colgiros_cop_ves_rate', 102.9);
    echo '<input type="number" step="0.01" name="colgiros_cop_ves_rate" value="' . esc_attr($rate) . '" />';
}

function colgiros_cop_usd_rate_callback() {
    $rate = get_option('colgiros_cop_usd_rate', 39.79);
    echo '<input type="number" step="0.01" name="colgiros_cop_usd_rate" value="' . esc_attr($rate) . '" />';
}

function colgiros_default_amount_callback() {
    $amount = get_option('colgiros_default_amount', 10000);
    echo '<input type="number" step="1" name="colgiros_default_amount" value="' . esc_attr($amount) . '" />';
}

function colgiros_convert_url_callback() {
    $url = get_option('colgiros_convert_url', 'https://wa.link/pz8riu');
    echo '<input type="url" name="colgiros_convert_url" value="' . esc_attr($url) . '" />';
}

// Callbacks para las configuraciones estéticas
function colgiros_border_radius_callback() {
    $border_radius = get_option('colgiros_border_radius', '15');
    echo '<input type="range" min="0" max="100" name="colgiros_border_radius" value="' . esc_attr($border_radius) . '" />';
}

function colgiros_transparency_callback() {
    $transparency = get_option('colgiros_transparency', '100');
    echo '<input type="range" min="0" max="100" name="colgiros_transparency" value="' . esc_attr($transparency) . '" />';
}

function colgiros_title_font_weight_callback() {
    $font_weight = get_option('colgiros_title_font_weight', '400');
    echo '<select name="colgiros_title_font_weight">
        <option value="100"' . selected($font_weight, '100', false) . '>100 (Thin)</option>
        <option value="200"' . selected($font_weight, '200', false) . '>200 (Extra Light)</option>
        <option value="300"' . selected($font_weight, '300', false) . '>300 (Light)</option>
        <option value="400"' . selected($font_weight, '400', false) . '>400 (Normal)</option>
        <option value="500"' . selected($font_weight, '500', false) . '>500 (Medium)</option>
        <option value="600"' . selected($font_weight, '600', false) . '>600 (Semi Bold)</option>
        <option value="700"' . selected($font_weight, '700', false) . '>700 (Bold)</option>
    </select>';
}

function colgiros_subtitle_font_weight_callback() {
    $font_weight = get_option('colgiros_subtitle_font_weight', '400');
    echo '<select name="colgiros_subtitle_font_weight">
        <option value="100"' . selected($font_weight, '100', false) . '>100 (Thin)</option>
        <option value="200"' . selected($font_weight, '200', false) . '>200 (Extra Light)</option>
        <option value="300"' . selected($font_weight, '300', false) . '>300 (Light)</option>
        <option value="400"' . selected($font_weight, '400', false) . '>400 (Normal)</option>
        <option value="500"' . selected($font_weight, '500', false) . '>500 (Medium)</option>
        <option value="600"' . selected($font_weight, '600', false) . '>600 (Semi Bold)</option>
        <option value="700"' . selected($font_weight, '700', false) . '>700 (Bold)</option>
    </select>';
}

function colgiros_amount_font_weight_callback() {
    $font_weight = get_option('colgiros_amount_font_weight', '400');
    echo '<select name="colgiros_amount_font_weight">
        <option value="100"' . selected($font_weight, '100', false) . '>100 (Thin)</option>
        <option value="200"' . selected($font_weight, '200', false) . '>200 (Extra Light)</option>
        <option value="300"' . selected($font_weight, '300', false) . '>300 (Light)</option>
        <option value="400"' . selected($font_weight, '400', false) . '>400 (Normal)</option>
        <option value="500"' . selected($font_weight, '500', false) . '>500 (Medium)</option>
        <option value="600"' . selected($font_weight, '600', false) . '>600 (Semi Bold)</option>
        <option value="700"' . selected($font_weight, '700', false) . '>700 (Bold)</option>
    </select>';
}

function colgiros_font_size_callback() {
    $font_size = get_option('colgiros_font_size', '16');
    echo '<select name="colgiros_font_size">
        <option value="12"' . selected($font_size, '12', false) . '>12px</option>
        <option value="14"' . selected($font_size, '14', false) . '>14px</option>
        <option value="16"' . selected($font_size, '16', false) . '>16px</option>
        <option value="18"' . selected($font_size, '18', false) . '>18px</option>
        <option value="20"' . selected($font_size, '20', false) . '>20px</option>
        <option value="22"' . selected($font_size, '22', false) . '>22px</option>
    </select>';
}

function colgiros_title_color_callback() {
    $title_color = get_option('colgiros_title_color', '#0057b7');
    echo '<input type="color" name="colgiros_title_color" value="' . esc_attr($title_color) . '" />';
}

function colgiros_subtitle_color_callback() {
    $subtitle_color = get_option('colgiros_subtitle_color', '#333');
    echo '<input type="color" name="colgiros_subtitle_color" value="' . esc_attr($subtitle_color) . '" />';
}

function colgiros_amount_color_callback() {
    $amount_color = get_option('colgiros_amount_color', '#0057b7');
    echo '<input type="color" name="colgiros_amount_color" value="' . esc_attr($amount_color) . '" />';
}

function colgiros_button_color_callback() {
    $button_color = get_option('colgiros_button_color', '#0057b7');
    echo '<input type="color" name="colgiros_button_color" value="' . esc_attr($button_color) . '" />';
}

function colgiros_amount_direction_callback() {
    $amount_direction = get_option('colgiros_amount_direction', 'left');
    echo '<select name="colgiros_amount_direction">
        <option value="left"' . selected($amount_direction, 'left', false) . '>Izquierda</option>
        <option value="right"' . selected($amount_direction, 'right', false) . '>Derecha</option>
    </select>';
}

function colgiros_font_family_callback() {
    $font_family = get_option('colgiros_font_family', 'Montserrat');
    $fonts = array('Montserrat', 'Roboto', 'Open Sans', 'Lato', 'Oswald', 'Source Sans Pro', 'Raleway', 'PT Sans', 'Merriweather', 'Poppins', 'Nunito', 'Quicksand', 'Rubik', 'Work Sans', 'Zilla Slab');
    echo '<select name="colgiros_font_family">';
    foreach ($fonts as $font) {
        echo '<option value="' . esc_attr($font) . '"' . selected($font_family, $font, false) . '>' . esc_html($font) . '</option>';
    }
    echo '</select>';
}

function colgiros_input_background_color_callback() {
    $input_background_color = get_option('colgiros_input_background_color', '#e6f0ff');
    echo '<input type="color" name="colgiros_input_background_color" value="' . esc_attr($input_background_color) . '" />';
}

function colgiros_input_background_transparency_callback() {
    $input_background_transparency = get_option('colgiros_input_background_transparency', '100');
    echo '<input type="range" min="0" max="100" name="colgiros_input_background_transparency" value="' . esc_attr($input_background_transparency) . '" />';
}

function colgiros_input_width_callback() {
    $input_width = get_option('colgiros_input_width', '100');
    echo '<input type="range" min="10" max="100" name="colgiros_input_width" value="' . esc_attr($input_width) . '" />';
}

function colgiros_rate_spacing_callback() {
    $spacing = get_option('colgiros_rate_spacing', '10');
    echo '<input type="number" min="0" name="colgiros_rate_spacing" value="' . esc_attr($spacing) . '" /> px';
}

// Callback para la posición de las tasas de cambio
function colgiros_rate_position_callback() {
    $position = get_option('colgiros_rate_position', 'initial');
    ?>
    <select name="colgiros_rate_position">
        <option value="initial" <?php selected($position, 'initial'); ?>>Posición Inicial (parte superior del formulario)</option>
        <option value="new" <?php selected($position, 'new'); ?>>Nueva Posición (debajo de cada resultado)</option>
    </select>
    <?php
}

// Callbacks para las configuraciones de sombra
function colgiros_shadow_enabled_callback() {
    $shadow_enabled = get_option('colgiros_shadow_enabled', '1');
    echo '<input type="checkbox" name="colgiros_shadow_enabled" value="1" ' . checked(1, $shadow_enabled, false) . ' /> Habilitar sombra';
}

function colgiros_shadow_color_callback() {
    $shadow_color = get_option('colgiros_shadow_color', '#000000');
    echo '<input type="color" name="colgiros_shadow_color" value="' . esc_attr($shadow_color) . '" />';
}

// Callbacks para el fondo de la calculadora
function colgiros_calculator_background_color_callback() {
    $calc_bg_color = get_option('colgiros_calculator_background_color', '#ffffff');
    echo '<input type="color" name="colgiros_calculator_background_color" value="' . esc_attr($calc_bg_color) . '" />';
}

function colgiros_calculator_background_transparency_callback() {
    $calc_bg_transparency = get_option('colgiros_calculator_background_transparency', '100');
    echo '<input type="range" min="0" max="100" name="colgiros_calculator_background_transparency" value="' . esc_attr($calc_bg_transparency) . '" />';
}

// Callbacks para los bordes y espaciado de las casillas
function colgiros_casilla_border_radius_callback() {
    $casilla_border_radius = get_option('colgiros_casilla_border_radius', '10');
    echo '<input type="range" min="0" max="50" name="colgiros_casilla_border_radius" value="' . esc_attr($casilla_border_radius) . '" /> px';
}

function colgiros_vertical_spacing_callback() {
    $vertical_spacing = get_option('colgiros_vertical_spacing', '20');
    echo '<input type="number" min="0" name="colgiros_vertical_spacing" value="' . esc_attr($vertical_spacing) . '" /> px';
}

// Shortcode para la calculadora
function colgiros_shortcode() {
    $rate_position = get_option('colgiros_rate_position', 'initial'); // Obtener la posición de las tasas
    ob_start(); ?>
    <div class="calculadora">
        <h2>Convertidor</h2>
        <?php if ($rate_position === 'initial') : ?>
            <p class="rate-ves">Tasa actual COP a VES: <?php echo get_option('colgiros_cop_ves_rate', 102.9); ?></p>
            <p class="rate-usd">Tasa actual VES a USD: <?php echo get_option('colgiros_cop_usd_rate', 39.79); ?></p>
        <?php endif; ?>
        <div class="form-group">
            <label for="monto">Importe en Pesos a transferir (COP):</label>
            <input type="text" id="monto" name="monto" placeholder="$10.000" />
        </div>
        <div class="form-group">
            <label for="total-ves">Total en Bolívares (VES):</label>
            <?php if ($rate_position === 'new') : ?>
                <p class="rate-ves">Tasa actual COP a VES: <?php echo get_option('colgiros_cop_ves_rate', 102.9); ?></p>
            <?php endif; ?>
            <input type="text" id="total-ves" name="total-ves" readonly />
        </div>
        <div class="form-group">
            <label for="total-usd">Total en Dólares (USD):</label>
            <?php if ($rate_position === 'new') : ?>
                <p class="rate-usd">Tasa actual VES a USD: <?php echo get_option('colgiros_cop_usd_rate', 39.79); ?></p>
            <?php endif; ?>
            <input type="text" id="total-usd" name="total-usd" readonly />
        </div>
        <button id="convertir">Convertir</button>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('colgiros_conversor', 'colgiros_shortcode');
?>
