<?php
if (!defined('ABSPATH')) exit;

function cm_public_people_shortcode($atts) {
    global $wpdb;
    $pessoas_table   = $wpdb->prefix . 'cm_pessoas';
    $contactos_table = $wpdb->prefix . 'cm_contactos';

    // Capturar filtros do GET
    $filter_name   = isset($_GET['filter_name']) ? sanitize_text_field($_GET['filter_name']) : '';
    $filter_email  = isset($_GET['filter_email']) ? sanitize_text_field($_GET['filter_email']) : '';
    $filter_number = isset($_GET['filter_number']) ? sanitize_text_field($_GET['filter_number']) : '';

    // Construir query base
    $query  = "SELECT * FROM $pessoas_table WHERE deleted_at IS NULL";
    $params = [];

    if ($filter_name) {
        $query    .= " AND name LIKE %s";
        $params[] = '%' . $wpdb->esc_like($filter_name) . '%';
    }
    if ($filter_email) {
        $query    .= " AND email LIKE %s";
        $params[] = '%' . $wpdb->esc_like($filter_email) . '%';
    }

    $query   .= " ORDER BY id DESC";
    $pessoas  = $params ? $wpdb->get_results($wpdb->prepare($query, ...$params)) : $wpdb->get_results($query);

    ob_start(); ?>
    
    <form method="get" style="margin-bottom:20px;">
        <input type="text" name="filter_name" placeholder="Filtrar por nome" value="<?php echo esc_attr($filter_name); ?>">
        <input type="text" name="filter_email" placeholder="Filtrar por email" value="<?php echo esc_attr($filter_email); ?>">
        <input type="text" name="filter_number" placeholder="Filtrar por número" value="<?php echo esc_attr($filter_number); ?>">
        <input type="submit" value="Filtrar">
    </form>

    <?php if ($pessoas) : ?>
        <table style="width:100%; border-collapse: collapse; text-align:left;">
            <thead>
                <tr style="background:#f5f5f5;">
                    <th style="padding:8px; width:50px;">ID</th>
                    <th style="padding:8px; text-align:center; width:100px;">Avatar</th>
                    <th style="padding:8px;">Nome</th>
                    <th style="padding:8px;">Email</th>
                    <th style="padding:8px;">Contactos</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pessoas as $p) :
                $contacts_query = "SELECT * FROM {$contactos_table} WHERE person_id = %d";
                $contacts       = $wpdb->get_results($wpdb->prepare($contacts_query, $p->id));

                // Filtrar por número se houver filtro
                if ($filter_number) {
                    $contacts = array_filter($contacts, fn($c) => strpos($c->number, $filter_number) !== false);
                }
                ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:8px;"><?php echo intval($p->id); ?></td>
                    <td style="padding:8px; text-align:center;">
                        <?php if (!empty($p->avatar_svg)) : ?>
                            <div style="width:80px;height:80px;overflow:hidden;border-radius:50%;border:1px solid #ddd;margin:auto;">
                                <?php echo $p->avatar_svg; ?>
                            </div>
                        <?php else: ?>
                            <span style="color:#aaa;">Sem avatar</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:8px;"><?php echo esc_html($p->name); ?></td>
                    <td style="padding:8px;"><?php echo esc_html($p->email); ?></td>
                    <td style="padding:8px;">
                        <?php if ($contacts) : ?>
                            <ul style="margin:0; padding-left:20px;">
                                <?php foreach ($contacts as $c) : ?>
                                    <li><?php echo esc_html($c->country_code . ' ' . $c->number); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else : ?>
                            <span style="color:#777;">Nenhum contacto</span>
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
