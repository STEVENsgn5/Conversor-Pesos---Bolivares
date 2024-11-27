document.addEventListener('DOMContentLoaded', function() {
    function convertir() {
        // Obtener el valor del monto y limpiar caracteres no numéricos
        var montoInput = document.getElementById('monto').value.replace(/\$/g, '').replace(/\./g, '').replace(/,/g, '');
        var amount = parseFloat(montoInput);

        // Verificar si el monto es un número válido y mayor que cero
        if (isNaN(amount) || amount <= 0) {
            document.getElementById('total-ves').value = '';
            document.getElementById('total-usd').value = '';
            return;
        }

        // Obtener las tasas de cambio y parsearlas como números
        var copToVesRate = parseFloat(colgirosData.cop_ves_rate);
        var vesToUsdRate = parseFloat(colgirosData.ves_usd_rate);

        // Verificar si las tasas de cambio son válidas
        if (isNaN(copToVesRate) || isNaN(vesToUsdRate)) {
            console.error('Tasas de cambio inválidas.');
            return;
        }

        // Realizar las conversiones
        var totalVES = amount / copToVesRate;
        var totalUSD = totalVES / vesToUsdRate;

        // Formatear y mostrar los resultados
        document.getElementById('total-ves').value = formatCurrency(totalVES.toFixed(2), 'VES');
        document.getElementById('total-usd').value = formatCurrency(totalUSD.toFixed(2), 'USD');
    }

    function formatCOPInput(event) {
        var input = event.target;
        var value = input.value.replace(/\D/g, '');
        
        // Si el valor está vacío, limpiar el campo sin añadir '$'
        if (value === '') {
            input.value = '';
            return;
        }

        var formattedValue = '$ ' + parseInt(value).toLocaleString('es-CO');
        input.value = formattedValue;
    }

    function formatCurrency(value, currency) {
        // Verificar si el valor es NaN
        if (isNaN(value)) {
            return '';
        }

        if (currency === 'COP') {
            return '$ ' + parseInt(value).toLocaleString('es-CO');
        } else if (currency === 'USD') {
            return '$ ' + parseFloat(value).toLocaleString('es-ES', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        } else if (currency === 'VES') {
            return parseFloat(value).toLocaleString('de-DE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + ' VES';
        } else {
            return parseFloat(value).toLocaleString('es-ES', { style: 'currency', currency: currency });
        }
    }

    function initDefaultAmount() {
        var amountStr = colgirosData.default_amount.toString().replace(/\D/g, '');
        var amount = parseInt(amountStr);
        if (!isNaN(amount)) {
            var formattedValue = '$ ' + amount.toLocaleString('es-CO');
            document.getElementById('monto').value = formattedValue;
        } else {
            document.getElementById('monto').value = '';
        }
        convertir();
    }

    function applyCustomStyles() {
        const styles = colgirosData.styles;
        const calculadora = document.querySelector('.calculadora');
        if (!calculadora) return; // Asegurarse de que la calculadora existe

        // Aplicar estilos generales
        calculadora.style.borderRadius = styles.border_radius + 'px';
        calculadora.style.opacity = styles.transparency / 100;
        calculadora.style.fontFamily = styles.font_family;
        calculadora.style.fontSize = styles.font_size + 'px';

        // Aplicar color de fondo de la calculadora con transparencia
        const bgColor = hexToRgbA(styles.calculator_background_color, styles.calculator_background_transparency / 100);
        calculadora.style.backgroundColor = bgColor;

        // Estilos para el título
        const h2 = calculadora.querySelector('h2');
        if (h2) {
            h2.style.color = styles.title_color;
            h2.style.fontWeight = styles.title_font_weight;
        }

        // Estilos para los párrafos (tasas de cambio)
        const pElements = calculadora.querySelectorAll('.calculadora p');
        pElements.forEach((p, index) => {
            p.style.color = styles.subtitle_color;
            p.style.fontWeight = styles.subtitle_font_weight;
            if (index === 0) {
                p.style.marginBottom = styles.rate_spacing + 'px'; // Aplicar espaciado al primer párrafo
            }
        });

        // Estilos para los inputs
        const inputs = calculadora.querySelectorAll('.form-group input');
        inputs.forEach(input => {
            input.style.textAlign = styles.amount_direction;
            const bgInputColor = hexToRgbA(styles.input_background_color, styles.input_background_transparency / 100);
            input.style.backgroundColor = bgInputColor;
            input.style.width = styles.input_width + '%';
            input.style.fontWeight = styles.amount_font_weight;
            input.style.color = styles.amount_color;
            input.style.borderRadius = styles.casilla_border_radius + 'px'; // Aplicar redondeo a las casillas
        });

        // Estilos para el botón
        const button = calculadora.querySelector('#convertir');
        if (button) {
            button.style.backgroundColor = styles.button_color;
            button.style.fontWeight = styles.amount_font_weight;

            // Aplicar sombra si está habilitada
            if (styles.shadow_enabled === '1') {
                calculadora.style.boxShadow = `0 4px 8px ${styles.shadow_color}`;
            } else {
                calculadora.style.boxShadow = 'none';
            }

            // Controlar espaciado vertical entre casillas
            const formGroups = calculadora.querySelectorAll('.form-group');
            formGroups.forEach(group => {
                group.style.marginBottom = styles.vertical_spacing + 'px';
            });

            // Eliminar event listeners previos para evitar duplicaciones
            button.removeEventListener('mouseover', buttonMouseOver);
            button.removeEventListener('mouseout', buttonMouseOut);

            // Asignar nuevos event listeners
            button.addEventListener('mouseover', buttonMouseOver);
            button.addEventListener('mouseout', buttonMouseOut);
        }
    }

    function buttonMouseOver() {
        const styles = colgirosData.styles;
        this.style.backgroundColor = darkenColor(styles.button_color, 20);
    }

    function buttonMouseOut() {
        const styles = colgirosData.styles;
        this.style.backgroundColor = styles.button_color;
    }

    function hexToRgbA(hex, alpha){
        var c;
        if(/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)){
            c = hex.substring(1).split('');
            if(c.length == 3){
                c = [c[0], c[0], c[1], c[1], c[2], c[2]];
            }
            c = '0x' + c.join('');
            return 'rgba('+[(c>>16)&255, (c>>8)&255, c&255].join(',')+','+alpha+')';
        }
        throw new Error('Bad Hex');
    }

    function darkenColor(hex, percent) {
        var num = parseInt(hex.slice(1),16),
            amt = Math.round(2.55 * percent),
            R = (num >> 16) - amt,
            G = (num >> 8 & 0x00FF) - amt,
            B = (num & 0x0000FF) - amt;
        return "#" + (
            0x1000000 + 
            (R<255?R<1?0:R:255)*0x10000 + 
            (G<255?G<1?0:G:255)*0x100 + 
            (B<255?B<1?0:B:255)
        ).toString(16).slice(1);
    }

    applyCustomStyles();

    document.getElementById('monto').addEventListener('input', formatCOPInput);
    document.getElementById('monto').addEventListener('input', convertir);
    document.getElementById('convertir').addEventListener('click', function() {
        // Verificar si la URL es válida antes de redirigir
        try {
            new URL(colgirosData.convert_url);
            window.location.href = colgirosData.convert_url;
        } catch (e) {
            console.error('URL de conversión inválida.');
        }
    });

    initDefaultAmount();
});
