<?php
/**
 * Plugin Name: CM — Contact Manager
 * Description: Plugin para gerir Pessoas e Contactos (teste).
 * Version: 1.0.6
 * Author: Diogo Amorim
 */

if (!defined('ABSPATH')) {
    exit; // Bloqueia acesso direto
}

/**
 * Criação das tabelas ao ativar o plugin
 */
register_activation_hook(__FILE__, 'cm_activate_plugin');

function cm_activate_plugin() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $pessoas  = $wpdb->prefix . 'cm_pessoas';
    $contactos = $wpdb->prefix . 'cm_contactos';

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Tabela de pessoas
    $sql1 = "CREATE TABLE $pessoas (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        avatar_svg LONGTEXT NULL,
        deleted_at DATETIME NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Tabela de contactos
    $sql2 = "CREATE TABLE $contactos (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        person_id BIGINT UNSIGNED NOT NULL,
        country_code VARCHAR(10) NOT NULL,
        number VARCHAR(9) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_contact (country_code, number),
        KEY person_idx (person_id),
        PRIMARY KEY (id)
    ) $charset_collate;";

    dbDelta($sql1);
    dbDelta($sql2);
}

/**
 *Criar menu no admin
 */
add_action('admin_menu', function () {
    add_menu_page(
        'Contact Manager',        // Título da página
        'Contact Management',        // Nome no menu
        'manage_options',            // Permissão
        'cm_people',                 // Slug da página
        'cm_people_page',            // Função callback
        'dashicons-id-alt',          // Ícone
        26                           // Posição
    );
});

// Adiciona o form para introduzir os dados da pessoa e a lista de todos os dados das pessoas
require_once plugin_dir_path(__FILE__) . 'admin/pessoa-form.php';
require_once plugin_dir_path(__FILE__) . 'admin/pessoa-list.php';

// Adiciona menu Nova Pessoa
add_action('admin_menu', function() {
    add_submenu_page(
        'cm_people',             // Parent slug
        'Adicionar Pessoa',      // Page title
        'Adicionar Pessoa',      // Menu title
        'manage_options',        // Capability
        'cm_person_add',         // Menu slug
        'cm_person_add_page'     // Callback
    );
});

// Adiciona o form para introduzir o contacto
require_once plugin_dir_path(__FILE__) . 'admin/contacto-form.php';

add_action('admin_menu', function() {
    add_submenu_page(
        'cm_people',           // Parent slug
        'Novo Contacto',       // Page title
        'Novo Contacto',       // Menu title
        'manage_options',      // Capability
        'cm_contact_add',      // Menu Slug
        'cm_contact_add_page'  // Callback
    );
});
 // Lista de contactos
require_once plugin_dir_path(__FILE__) . 'admin/contacto-list.php';

add_action('admin_menu', function() {
    add_submenu_page(
        'cm_people',              // Parent slug
        'Lista de Contactos',     // Page title
        'Lista de Contactos',     // Menu title
        'manage_options',         // Capability necessária
        'cm_contact_list',        // Menu slug
        'cm_contact_list_page'    // Callback 
    );
});

// Lista para página publica
require_once plugin_dir_path(__FILE__) . 'public-people.php';


