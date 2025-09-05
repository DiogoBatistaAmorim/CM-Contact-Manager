<?php
if (!defined('ABSPATH')) exit;

function cm_contact_add_page() {
    global $wpdb;
    $contactos_table = $wpdb->prefix . 'cm_contactos';
    $pessoas_table   = $wpdb->prefix . 'cm_pessoas';
    $message = '';

    // Carregar lista de pessoas (para escolher a quem pertence o contacto)
    $pessoas = $wpdb->get_results("SELECT id, name FROM {$pessoas_table} WHERE deleted_at IS NULL ORDER BY name ASC");

    // Processar submissão
    if (isset($_POST['cm_contact_submit'])) {
        $person_id    = intval($_POST['person_id']);
        $country_code = sanitize_text_field($_POST['country_code']);
        $number       = sanitize_text_field($_POST['number']);

        if (!$person_id) {
            $message = '<div class="notice notice-error"><p>Selecione uma pessoa.</p></div>';
        } elseif (strlen($number) < 5) {
            $message = '<div class="notice notice-error"><p>O número deve ter pelo menos 5 dígitos.</p></div>';
        } else {
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
                $message = '<div class="notice notice-error"><p>Erro ao adicionar contacto. Já existe este número?</p></div>';
            }
        }
    }

    echo '<div class="wrap">';
    echo '<h1>Adicionar Contacto</h1>';
    echo $message;
    ?>
    <form method="post">
        <table class="form-table">
            <tr>
                <th><label for="person_id">Pessoa</label></th>
                <td>
                    <select name="person_id" id="person_id" required>
                        <option value="">-- Selecionar Pessoa --</option>
                        <?php foreach ($pessoas as $p): ?>
                            <option value="<?php echo $p->id; ?>"><?php echo esc_html($p->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="country_code">País</label></th>
                <td>
                    <select name="country_code" id="country_code" required>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="number">Número</label></th>
                <td><input type="text" name="number" id="number" class="regular-text" placeholder="Número de telefone" required></td>
            </tr>
        </table>
        <p class="submit"><input type="submit" name="cm_contact_submit" class="button button-primary" value="Adicionar Contacto"></p>
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
                        select.appendChild(option);
                    }
                });
            })
            .catch(err => {
                console.error("Erro ao carregar países:", err);
            });
    });
    </script>
    <?php
    echo '</div>';
}
