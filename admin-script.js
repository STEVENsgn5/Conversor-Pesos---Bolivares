document.addEventListener('DOMContentLoaded', function() {
    const saveTemplateButton = document.getElementById('guardar_plantilla');
    const restoreTemplateButton = document.getElementById('restaurar_plantilla');
    const templateSelect = document.getElementById('plantillas_guardadas');

    saveTemplateButton.addEventListener('click', function() {
        let templateName = prompt('Ingrese un nombre para la plantilla');
        if (templateName) {
            templateName = templateName.trim();
            if (templateName === '') {
                alert('El nombre de la plantilla no puede estar vacío.');
                return;
            }

            let templates = JSON.parse(localStorage.getItem('colgirosTemplates')) || {};

            if (templates.hasOwnProperty(templateName)) {
                const overwrite = confirm('La plantilla ya existe. ¿Desea sobrescribirla?');
                if (!overwrite) {
                    return;
                }
            }

            const templateData = {
                border_radius: document.querySelector('input[name="colgiros_border_radius"]').value,
                transparency: document.querySelector('input[name="colgiros_transparency"]').value,
                title_font_weight: document.querySelector('select[name="colgiros_title_font_weight"]').value,
                subtitle_font_weight: document.querySelector('select[name="colgiros_subtitle_font_weight"]').value,
                amount_font_weight: document.querySelector('select[name="colgiros_amount_font_weight"]').value,
                font_size: document.querySelector('select[name="colgiros_font_size"]').value,
                title_color: document.querySelector('input[name="colgiros_title_color"]').value,
                subtitle_color: document.querySelector('input[name="colgiros_subtitle_color"]').value,
                amount_color: document.querySelector('input[name="colgiros_amount_color"]').value,
                button_color: document.querySelector('input[name="colgiros_button_color"]').value, // Nueva línea
                amount_direction: document.querySelector('select[name="colgiros_amount_direction"]').value,
                font_family: document.querySelector('select[name="colgiros_font_family"]').value,
                input_background_color: document.querySelector('input[name="colgiros_input_background_color"]').value,
                input_background_transparency: document.querySelector('input[name="colgiros_input_background_transparency"]').value,
                input_width: document.querySelector('input[name="colgiros_input_width"]').value,
                rate_spacing: document.querySelector('input[name="colgiros_rate_spacing"]').value,
                shadow_enabled: document.querySelector('input[name="colgiros_shadow_enabled"]').checked ? '1' : '0',
                shadow_color: document.querySelector('input[name="colgiros_shadow_color"]').value,
                calculator_background_color: document.querySelector('input[name="colgiros_calculator_background_color"]').value, // Nueva línea
                calculator_background_transparency: document.querySelector('input[name="colgiros_calculator_background_transparency"]').value, // Nueva línea
                casilla_border_radius: document.querySelector('input[name="colgiros_casilla_border_radius"]').value, // Nueva línea
                vertical_spacing: document.querySelector('input[name="colgiros_vertical_spacing"]').value // Nueva línea
            };

            templates[templateName] = templateData;
            localStorage.setItem('colgirosTemplates', JSON.stringify(templates));

            // Actualizar el select de plantillas
            if (![...templateSelect.options].some(option => option.value === templateName)) {
                const option = document.createElement('option');
                option.value = templateName;
                option.text = templateName;
                templateSelect.appendChild(option);
            }

            alert('Plantilla guardada correctamente.');
        }
    });

    restoreTemplateButton.addEventListener('click', function() {
        const selectedTemplate = templateSelect.value;
        if (selectedTemplate) {
            const templates = JSON.parse(localStorage.getItem('colgirosTemplates'));
            if (templates && templates[selectedTemplate]) {
                const templateData = templates[selectedTemplate];

                document.querySelector('input[name="colgiros_border_radius"]').value = templateData.border_radius;
                document.querySelector('input[name="colgiros_transparency"]').value = templateData.transparency;
                document.querySelector('select[name="colgiros_title_font_weight"]').value = templateData.title_font_weight;
                document.querySelector('select[name="colgiros_subtitle_font_weight"]').value = templateData.subtitle_font_weight;
                document.querySelector('select[name="colgiros_amount_font_weight"]').value = templateData.amount_font_weight;
                document.querySelector('select[name="colgiros_font_size"]').value = templateData.font_size;
                document.querySelector('input[name="colgiros_title_color"]').value = templateData.title_color;
                document.querySelector('input[name="colgiros_subtitle_color"]').value = templateData.subtitle_color;
                document.querySelector('input[name="colgiros_amount_color"]').value = templateData.amount_color;
                document.querySelector('input[name="colgiros_button_color"]').value = templateData.button_color; // Nueva línea
                document.querySelector('select[name="colgiros_amount_direction"]').value = templateData.amount_direction;
                document.querySelector('select[name="colgiros_font_family"]').value = templateData.font_family;
                document.querySelector('input[name="colgiros_input_background_color"]').value = templateData.input_background_color;
                document.querySelector('input[name="colgiros_input_background_transparency"]').value = templateData.input_background_transparency;
                document.querySelector('input[name="colgiros_input_width"]').value = templateData.input_width;
                document.querySelector('input[name="colgiros_rate_spacing"]').value = templateData.rate_spacing;
                document.querySelector('input[name="colgiros_shadow_enabled"]').checked = templateData.shadow_enabled === '1';
                document.querySelector('input[name="colgiros_shadow_color"]').value = templateData.shadow_color;
                document.querySelector('input[name="colgiros_calculator_background_color"]').value = templateData.calculator_background_color; // Nueva línea
                document.querySelector('input[name="colgiros_calculator_background_transparency"]').value = templateData.calculator_background_transparency; // Nueva línea
                document.querySelector('input[name="colgiros_casilla_border_radius"]').value = templateData.casilla_border_radius; // Nueva línea
                document.querySelector('input[name="colgiros_vertical_spacing"]').value = templateData.vertical_spacing; // Nueva línea

                // Opcional: Auto-guardar después de restaurar la plantilla
                if (confirm('¿Desea guardar automáticamente los cambios restaurados?')) {
                    // Submit the form to save the restored template
                    const form = document.querySelector('form');
                    if (form) {
                        form.submit();
                    }
                } else {
                    alert('Plantilla restaurada correctamente. No se ha guardado automáticamente. Asegúrese de hacer clic en "Guardar" para aplicar los cambios.');
                }
            } else {
                alert('La plantilla seleccionada no existe o está corrupta.');
            }
        } else {
            alert('Por favor, seleccione una plantilla para restaurar.');
        }
    });
});
