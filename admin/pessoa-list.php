<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('cm_people_page')) {
    function cm_people_page() {
        global $wpdb;
        $pessoas_table = $wpdb->prefix . 'cm_pessoas';
        $message = '';

        if(isset($_GET['delete']) && check_admin_referer('cm_delete_person')) {
            $id = intval($_GET['delete']);
            $wpdb->delete(
                "{$wpdb->prefix}cm_pessoas",
                ['id' => $id],
                ['%d']
            );
            echo '<div class="notice notice-success"><p>Pessoa eliminada com sucesso.</p></div>';
        }


        // Obter lista de pessoas não apagadas
        $pessoas = $wpdb->get_results("SELECT * FROM $pessoas_table WHERE deleted_at IS NULL ORDER BY id DESC");

        echo '<div class="wrap">';
        echo '<h1>Lista de Pessoas</h1>';

        // Mostrar mensagem, se existir
        if($message) echo $message;

        // Botão para adicionar nova pessoa
        echo '<a href="' . admin_url('admin.php?page=cm_person_add') . '" class="page-title-action">Adicionar Nova Pessoa</a>';

        if($pessoas) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>ID</th><th>Nome</th><th>Email</th><th>Ações</th></tr></thead><tbody>';
            foreach($pessoas as $p) {
                $edit_url = admin_url('admin.php?page=cm_person_add&id='.$p->id);
                $delete_url = wp_nonce_url(admin_url('admin.php?page=cm_people&delete='.$p->id), 'cm_delete_person');

                echo '<tr>';
                echo '<td>'.$p->id.'</td>';
                echo '<td>'.esc_html($p->name).'</td>';
                echo '<td>'.esc_html($p->email).'</td>';
                echo '<td>';
                echo '<a href="'.$edit_url.'">Editar</a> | ';
                echo '<a href="'.$delete_url.'" onclick="return confirm(\'Tem certeza que deseja apagar esta pessoa?\')">Apagar</a>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p>Nenhuma pessoa encontrada.</p>';
        }

        echo '</div>';
    }
}
