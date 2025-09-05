<?php
if (!defined('ABSPATH')) exit;

function cm_contact_list_page() {
    global $wpdb;
    $contactos_table = $wpdb->prefix . 'cm_contactos';
    $pessoas_table   = $wpdb->prefix . 'cm_pessoas';
    $message = '';

    // Carregar lista de pessoas ativas
    $pessoas = $wpdb->get_results("SELECT id, name FROM {$pessoas_table} WHERE deleted_at IS NULL ORDER BY name ASC");

    // Pessoa selecionada
    $person_id = isset($_GET['person_id']) ? intval($_GET['person_id']) : ($pessoas ? $pessoas[0]->id : 0);

    // Processar delete de contacto
    if (isset($_GET['delete']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'cm_delete_contact')) {
        $contact_id = intval($_GET['delete']);
        $deleted = $wpdb->delete($contactos_table, ['id' => $contact_id], ['%d']);
        if ($deleted) {
            $message = '<div class="notice notice-success"><p>Contacto eliminado com sucesso.</p></div>';
        } else {
            $message = '<div class="notice notice-error"><p>Erro ao eliminar contacto.</p></div>';
        }
    }

    // Lista de contactos da pessoa selecionada
    $contacts = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$contactos_table} WHERE person_id = %d ORDER BY id DESC", $person_id));

    echo '<div class="wrap">';
    echo '<h1>Lista de Contactos</h1>';

    // Mostrar mensagem se existir
    if ($message) echo $message;

    // Dropdown para selecionar pessoa
    echo '<form method="get" style="margin-bottom:20px;">';
    echo '<input type="hidden" name="page" value="cm_contact_list">';
    echo '<select name="person_id" onchange="this.form.submit()">';
    foreach ($pessoas as $p) {
        echo '<option value="' . $p->id . '" ' . selected($p->id, $person_id, false) . '>' . esc_html($p->name) . '</option>';
    }
    echo '</select>';
    echo '</form>';

    // Mostrar lista de contactos
    if ($contacts) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Código País</th><th>Número</th><th>Ações</th></tr></thead><tbody>';
        foreach ($contacts as $c) {
            echo '<tr>';
            echo '<td>' . $c->id . '</td>';
            echo '<td>' . esc_html($c->country_code) . '</td>';
            echo '<td>' . esc_html($c->number) . '</td>';
            echo '<td>';
            echo '<a href="' . admin_url('admin.php?page=cm_contact_add&id=' . $c->id) . '">Editar</a> | ';
            $delete_url = wp_nonce_url(admin_url('admin.php?page=cm_contact_list&delete=' . $c->id . '&person_id=' . $person_id), 'cm_delete_contact');
            echo '<a href="' . $delete_url . '" onclick="return confirm(\'Tem certeza que deseja eliminar este contacto?\')">Apagar</a>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>Nenhum contacto encontrado para esta pessoa.</p>';
    }

    echo '</div>';
}
