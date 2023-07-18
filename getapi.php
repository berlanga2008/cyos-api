<?php
/* cyos */

function custom_api_init() {
    add_action('rest_api_init', 'register_custom_routes');
}

function register_custom_routes() {
    register_rest_route('cyos/v1', '/pedidos', array(
        'methods' => 'GET',
        'callback' => 'get_pedidos',
    ));

    register_rest_route('cyos/v1', '/clientes', array(
        'methods' => 'GET',
        'callback' => 'get_clientes',
    ));
}

function get_pedidos($request) {
    // Obtener los parámetros de fecha enviados en la solicitud
    $fecha1 = $request->get_param('fecha1');
    $fecha2 = $request->get_param('fecha2');

    // Realizar la consulta de los pedidos
    global $wpdb;
    $query = $wpdb->prepare("
        SELECT DISTINCT(p.id)
        FROM {$wpdb->prefix}posts p
        WHERE p.post_type = 'shop_order'
        AND (p.post_date > %s OR p.post_modified > %s)
        AND p.post_status = 'wc-completed'
        ORDER BY 1 DESC
    ", $fecha1, $fecha2);

    $pedidos = $wpdb->get_results($query);

    // Devolver los resultados en formato JSON
    return rest_ensure_response($pedidos);
}

function get_clientes($request) {
    // Obtener los parámetros de fecha enviados en la solicitud
    $fecha1 = $request->get_param('fecha1');
    $fecha2 = $request->get_param('fecha2');
    $fecha3 = $request->get_param('fecha3');

    // Realizar la consulta de los clientes
    global $wpdb;
    $query = $wpdb->prepare("
        SELECT DISTINCT(u.id)
        FROM {$wpdb->prefix}users u
        INNER JOIN {$wpdb->prefix}postmeta pm
        ON u.id = pm.meta_value
        INNER JOIN {$wpdb->prefix}posts p
        ON pm.post_id = p.id
        WHERE (u.user_registered > %s OR p.post_date > %s OR p.post_modified > %s)
        AND pm.meta_key = '_customer_user'
        AND p.post_type = 'shop_order'
        AND p.post_status = 'wc-completed'
        ORDER BY 1 DESC
    ", $fecha1, $fecha2, $fecha3);

    $clientes = $wpdb->get_results($query);

    // Devolver los resultados en formato JSON
    return rest_ensure_response($clientes);
}

add_action('init', 'custom_api_init');
