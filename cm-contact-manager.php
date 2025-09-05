<?php
/**
 * Plugin Name: CM — Contact Manager
 * Description: Plugin para gerir Pessoas e Contactos (teste).
 * Version: 1.0.1
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
        name VARCHAR(50) NOT NULL,
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
 * Passo 1: Criar menu no admin
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

/**
 * Função de callback da página principal
 */
function cm_people_page() {
    echo '<div class="wrap">';
    echo '<h1>Contact Management</h1>';
    echo '<p>As tabelas <code>cm_persons</code> e <code>cm_contacts</code> devem ter sido criadas na base de dados <strong>wp-diogoamorim</strong>.</p>';
    echo '</div>';
}
