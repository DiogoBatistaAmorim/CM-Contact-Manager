<?php
if (!defined('ABSPATH')) exit;

function cm_contact_add_page() {
    global $wpdb;
    $contactos_table = $wpdb->prefix . 'cm_contactos';
    $pessoas_table   = $wpdb->prefix . 'cm_pessoas';
    $message = '';

    // Verificar se estamos em modo edição
    $contact_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $person_id_value = '';
    $country_code_value = '';
    $number_value = '';

    if ($contact_id) {
        $contact = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$contactos_table} WHERE id = %d", $contact_id));
        if ($contact) {
            $person_id_value    = $contact->person_id;
            $country_code_value = $contact->country_code;
            $number_value       = $contact->number;
        }
    }

    // Processar submissão do formulário ANTES de qualquer saída HTML
    if (isset($_POST['cm_contact_submit'])) {
        $person_id    = intval($_POST['person_id']);
        $country_code = sanitize_text_field($_POST['country_code']);
        $number       = sanitize_text_field($_POST['number']);

        if (!$person_id) {
            $message = '<div class="notice notice-error"><p>Selecione uma pessoa.</p></div>';
        } elseif (!preg_match('/^[0-9]{9}$/', $number)) {
            $message = '<div class="notice notice-error"><p>O número deve ter exatamente 9 dígitos.</p></div>';
        } else {
            if ($contact_id) {
                // UPDATE
                $updated = $wpdb->update(
                    $contactos_table,
                    [
                        'person_id'    => $person_id,
                        'country_code' => $country_code,
                        'number'       => $number
                    ],
                    ['id' => $contact_id],
                    ['%d','%s','%s'],
                    ['%d']
                );
                if ($updated !== false) {
                    $message = '<div class="notice notice-success"><p>Contacto atualizado com sucesso!</p></div>';
                } else {
                    $message = '<div class="notice notice-error"><p>Erro ao atualizar contacto.</p></div>';
                }
            } else {
                // INSERT
                $inserted = $wpdb->insert(
                    $contactos_table,
                    [
                        'person_id'    => $person_id,
                        'country_code' => $country_code,
                        'number'       => $number
                    ],
                    ['%d','%s','%s']
                );
                if ($inserted) {
                    $message = '<div class="notice notice-success"><p>Contacto adicionado com sucesso!</p></div>';
                } else {
                    $message = '<div class="notice notice-error"><p>Erro ao adicionar contacto. Já existe este número!</p></div>';
                }
            }
        }

        // Atualizar valores do formulário mesmo em caso de erro
        $person_id_value    = $person_id;
        $country_code_value = $country_code;
        $number_value       = $number;
    }

    // Carregar lista de pessoas (ativas)
    $pessoas = $wpdb->get_results("SELECT id, name FROM {$pessoas_table} WHERE deleted_at IS NULL ORDER BY name ASC");

    // Exibir formulário
    echo '<div class="wrap">';
    echo '<h1>' . ($contact_id ? 'Editar Contacto' : 'Adicionar Contacto') . '</h1>';
    if ($message) echo $message;
    ?>
    <form method="post">
        <table class="form-table">
            <tr>
                <th><label for="person_id">Pessoa</label></th>
                <td>
                    <select name="person_id" id="person_id" required>
                        <option value="">-- Selecionar Pessoa --</option>
                        <?php foreach ($pessoas as $p): ?>
                            <option value="<?php echo $p->id; ?>" <?php selected($p->id, $person_id_value); ?>>
                                <?php echo esc_html($p->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="country_code">País</label></th>
                <td>
                    <select name="country_code" id="country_code" required>
                        <option value="">-- Carregar países... --</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="number">Número</label></th>
                <td><input type="text" name="number" id="number" class="regular-text" placeholder="Número de telefone" value="<?php echo esc_attr($number_value); ?>" required></td>
            </tr>
        </table>
        <p class="submit"><input type="submit" name="cm_contact_submit" class="button button-primary" value="<?php echo ($contact_id ? 'Atualizar Contacto' : 'Adicionar Contacto'); ?>"></p>
    </form>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        fetch("https://restcountries.com/v3.1/all?fields=idd,name")
            .then(res => res.json())
            .then(data => {
                let select = document.getElementById("country_code");
                select.innerHTML = '<option value="">-- Selecionar País --</option>';
                data.forEach(country => {
                    if (country.idd && country.idd.root) {
                        let code = country.idd.root + (country.idd.suffixes ? country.idd.suffixes[0] : "");
                        let option = document.createElement("option");
                        option.value = code;
                        option.text = country.name.common + " (" + code + ")";
                        if (code === "<?php echo esc_js($country_code_value); ?>") {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    }
                });
            })
            .catch(err => console.error("Erro ao carregar países:", err));
    });
    </script>
    <?php
    echo '</div>';
}
