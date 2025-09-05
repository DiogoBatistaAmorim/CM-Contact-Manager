<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('cm_people_page')) {
    function cm_people_page() {
        global $wpdb;
        $pessoas_table = $wpdb->prefix . 'cm_pessoas';
        $message = '';

        // Soft delete
        if (isset($_GET['delete']) && check_admin_referer('cm_delete_person')) {
            $id = intval($_GET['delete']);
            $wpdb->update(
                $pessoas_table,
                ['deleted_at' => current_time('mysql')],
                ['id' => $id],
                ['%s'],
                ['%d']
            );
            echo '<div class="notice notice-success"><p>Pessoa eliminada com sucesso.</p></div>';
        }

        // Vai buscar apenas pessoas ativas
        $pessoas = $wpdb->get_results("SELECT * FROM $pessoas_table WHERE deleted_at IS NULL ORDER BY id DESC");

        echo '<div class="wrap">';
        echo '<h1>Lista de Pessoas</h1>';

        // Botão para adicionar nova pessoa
        echo '<a href="' . admin_url('admin.php?page=cm_person_add') . '" class="page-title-action">Adicionar Nova Pessoa</a>';

        if ($pessoas) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead>
                    <tr>
                        <th width="50">ID</th>
                        <th width="100" style="text-align:center;">Avatar</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th width="250">Ações</th>
                    </tr>
                  </thead>
                  <tbody>';

            foreach ($pessoas as $p) {
                $edit_url         = admin_url('admin.php?page=cm_person_add&id=' . $p->id);
                $delete_url       = wp_nonce_url(admin_url('admin.php?page=cm_people&delete=' . $p->id), 'cm_delete_person');
                $contact_add_url  = admin_url('admin.php?page=cm_contact_add&person_id=' . $p->id);
                $contact_list_url = admin_url('admin.php?page=cm_contact_list&person_id=' . $p->id);

                echo '<tr>';
                echo '<td>' . intval($p->id) . '</td>';

                // Avatar (se existir)
                echo '<td style="text-align:center;">';
                if (!empty($p->avatar_svg)) {
                    echo '<div style="width:80px;height:80px;overflow:hidden;border-radius:50%;border:1px solid #ddd;margin:auto;display:flex;align-items:center;justify-content:center;">' 
                        . $p->avatar_svg . 
                        '</div>';
                } else {
                    echo '<span style="color:#aaa;">Sem avatar</span>';
                }
                echo '</td>';

                echo '<td>' . esc_html($p->name) . '</td>';
                echo '<td>' . esc_html($p->email) . '</td>';

                // Coluna de ações
                echo '<td>';
                echo '<a href="' . $edit_url . '">Editar</a> | ';
                echo '<a href="' . $contact_add_url . '">Adicionar Contacto</a> | ';
                echo '<a href="' . $contact_list_url . '">Ver Contactos</a> | ';
                echo '<a href="' . $delete_url . '" onclick="return confirm(\'Tem certeza que deseja apagar esta pessoa?\')">Apagar</a>';
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
