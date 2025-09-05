<?php
if (!defined('ABSPATH')) exit;

function cm_public_people_shortcode($atts) {
    global $wpdb;
    $pessoas_table = $wpdb->prefix . 'cm_pessoas';
    $contactos_table = $wpdb->prefix . 'cm_contactos';

    // Filtros
    $filter_name = isset($_GET['filter_name']) ? sanitize_text_field($_GET['filter_name']) : '';
    $filter_email = isset($_GET['filter_email']) ? sanitize_text_field($_GET['filter_email']) : '';
    $filter_number = isset($_GET['filter_number']) ? sanitize_text_field($_GET['filter_number']) : '';

    // Query base
    $query = "SELECT * FROM {$pessoas_table} WHERE deleted_at IS NULL";
    $params = [];
    $formats = [];

    if ($filter_name) {
        $query .= " AND name LIKE %s";
        $params[] = '%' . $wpdb->esc_like($filter_name) . '%';
        $formats[] = '%s';
    }
    if ($filter_email) {
        $query .= " AND email LIKE %s";
        $params[] = '%' . $wpdb->esc_like($filter_email) . '%';
        $formats[] = '%s';
    }
    $query .= " ORDER BY id DESC";

    // Executa query
    $pessoas = $params ? $wpdb->get_results($wpdb->prepare($query, ...$params)) : $wpdb->get_results($query);

    ob_start();
    ?>
    <form method="get" style="margin-bottom:20px;">
        <input type="hidden" name="page_id" value="<?php echo get_the_ID(); ?>">
        <input type="text" name="filter_name" placeholder="Filtrar por nome" value="<?php echo esc_attr($filter_name); ?>">
        <input type="text" name="filter_email" placeholder="Filtrar por email" value="<?php echo esc_attr($filter_email); ?>">
        <input type="text" name="filter_number" placeholder="Filtrar por número" value="<?php echo esc_attr($filter_number); ?>">
        <input type="submit" value="Filtrar">
    </form>

    <?php if ($pessoas) : ?>
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Avatar</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Contactos</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pessoas as $p) :
                // Puxar contactos da pessoa
                $contacts_query = "SELECT * FROM {$contactos_table} WHERE person_id = %d";
                $contacts = $wpdb->get_results($wpdb->prepare($contacts_query, $p->id));

                // Filtrar contactos se filtro de número preenchido
                if ($filter_number) {
                    $contacts = array_filter($contacts, function($c) use ($filter_number) {
                        return strpos($c->number, $filter_number) !== false;
                    });
                }
                ?>
                <tr>
                    <td><?php echo $p->id; ?></td>
                    <td>
                        <?php if (!empty($p->avatar_svg)) : ?>
                            <div style="width:40px;height:40px;"><?php echo $p->avatar_svg; ?></div>
                        <?php else : ?>
                            <span style="color:#aaa;">Sem avatar</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html($p->name); ?></td>
                    <td><?php echo esc_html($p->email); ?></td>
                    <td>
                        <?php if ($contacts) : ?>
                            <ul>
                            <?php foreach ($contacts as $c) : ?>
                                <li><?php echo esc_html($c->country_code . ' ' . $c->number); ?></li>
                            <?php endforeach; ?>
                            </ul>
                        <?php else : ?>
                            Nenhum contacto
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p>Nenhuma pessoa encontrada.</p>
    <?php endif;

    return ob_get_clean();
}
add_shortcode('cm_public_people', 'cm_public_people_shortcode');
