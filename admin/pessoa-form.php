<?php
if (!defined('ABSPATH')) exit;

function cm_person_add_page() {
    global $wpdb;
    $pessoas_table = $wpdb->prefix . 'cm_pessoas';
    $message = '';

    if(isset($_POST['cm_person_submit'])) {
        $name  = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);

        if(strlen($name) <= 5) {
            $message = '<div class="notice notice-error"><p>O nome deve ter mais de 5 caracteres.</p></div>';
        } elseif(!is_email($email)) {
            $message = '<div class="notice notice-error"><p>Email inválido.</p></div>';
        } else {
            $inserted = $wpdb->insert(
                $pessoas_table,
                ['name'=>$name, 'email'=>$email],
                ['%s','%s']
            );
            if($inserted) {
                $message = '<div class="notice notice-success"><p>Dados pessoais adicionados com sucesso!</p></div>';
            } else {
                $message = '<div class="notice notice-error"><p>Erro ao adicionar. Email já existe?</p></div>';
            }
        }
    }

    echo '<div class="wrap">';
    echo '<h1>Adicionar Dados Pessoais</h1>';
    echo $message;
    ?>
    <form method="post">
        <table class="form-table">
            <tr><th><label for="name">Nome</label></th><td><input name="name" type="text" id="name" class="regular-text" placeholder="Introduza o nome completo" required></td></tr>
            <tr><th><label for="email">Email</label></th><td><input name="email" type="email" id="email" class="regular-text" placeholder="Introduza o seu Email" required></td></tr>
        </table>
        <p class="submit"><input type="submit" name="cm_person_submit" class="button button-primary" value="Adicionar Dados"></p>
    </form>
    <?php
    echo '</div>';
}
 