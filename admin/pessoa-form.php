<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('cm_person_add_page')) {
    function cm_person_add_page() {
        global $wpdb;
        $pessoas_table = $wpdb->prefix . 'cm_pessoas';
        $message = '';

        // Detectar se é edição
        if (isset($_GET['id'])) {
            $person_id = intval($_GET['id']);
            $person = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$pessoas_table} WHERE id = %d", $person_id));

            $name_value = $person ? $person->name : '';
            $email_value = $person ? $person->email : '';
        } else {
            $person_id = 0;
            $name_value = '';
            $email_value = '';
        }

        // Processar submissão do formulário
        if(isset($_POST['cm_person_submit'])) {
            $name  = sanitize_text_field($_POST['name']);
            $email = sanitize_email($_POST['email']);

            if(strlen($name) <= 5) {
                $message = '<div class="notice notice-error"><p>O nome deve ter mais de 5 caracteres.</p></div>';
            } elseif(!is_email($email)) {
                $message = '<div class="notice notice-error"><p>Email inválido.</p></div>';
            } else {
                if($person_id) {
                    // UPDATE
                    $updated = $wpdb->update(
                        $pessoas_table,
                        ['name' => $name, 'email' => $email],
                        ['id' => $person_id],
                        ['%s','%s'],
                        ['%d']
                    );
                    if($updated !== false) {
                        $message = '<div class="notice notice-success"><p>Dados pessoais atualizados com sucesso!</p></div>';
                    } else {
                        $message = '<div class="notice notice-error"><p>Erro ao atualizar os dados.</p></div>';
                    }
                } else {
                    // INSERT
                    $inserted = $wpdb->insert(
                        $pessoas_table,
                        ['name'=>$name, 'email'=>$email],
                        ['%s','%s']
                    );
                    if($inserted) {
                        $message = '<div class="notice notice-success"><p>Dados pessoais adicionados com sucesso!</p></div>';
                    } else {
                        $message = '<div class="notice notice-error"><p>Erro ao adicionar. Email já existe!</p></div>';
                    }
                }
            }

            // Atualizar valores do formulário após submissão
            $name_value = $name;
            $email_value = $email;
        }

        // Exibir formulário
        echo '<div class="wrap">';
        echo '<h1>' . ($person_id ? 'Editar Dados Pessoais' : 'Adicionar Dados Pessoais') . '</h1>';

        if($message) echo $message;
        ?>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th><label for="name">Nome</label></th>
                    <td><input name="name" type="text" id="name" class="regular-text" placeholder="Introduza o nome completo" value="<?php echo esc_attr($name_value); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="email">Email</label></th>
                    <td><input name="email" type="email" id="email" class="regular-text" placeholder="Introduza o seu Email" value="<?php echo esc_attr($email_value); ?>" required></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="cm_person_submit" class="button button-primary" value="<?php echo ($person_id ? 'Atualizar Dados' : 'Adicionar Dados'); ?>">
            </p>
        </form>
        <?php
        echo '</div>';
    }
}
