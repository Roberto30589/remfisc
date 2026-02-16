export function useRut() {
    const formatRut = (rut) => {
        if (!rut) return '';
        rut = rut.replace(/[^0-9kK]/g, '');
        let formattedRut = '';
        const lastChar = rut.slice(-1).toLowerCase();
        let body = rut.slice(0, -1);
        let dv = '';

        if (['k', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'].includes(lastChar) && rut.length > 1) {
            dv = '-' + lastChar;
            body = rut.slice(0, -1);
        } else {
            body = rut;
        }

        let j = 0;
        for (let i = body.length - 1; i >= 0; i--) {
            formattedRut = body[i] + formattedRut;
            j++;
            if (j % 3 === 0 && i !== 0) {
                formattedRut = '.' + formattedRut;
            }
        }
        return formattedRut + dv;
    };
    // Función para validar el dígito verificador del RUT (esta no cambia)
    const validateRut = (rut) => {
        if (!rut) return false;
        rut = rut.replace(/[^0-9kK]/g, '').toUpperCase();
        if (rut.length < 2) return false;

        let rutClean = rut.slice(0, -1);
        let dv = rut.slice(-1);

        if (dv === 'K' && rutClean.length === 0) return false;

        let sum = 0;
        let multiplier = 2;

        for (let i = rutClean.length - 1; i >= 0; i--) {
            sum += parseInt(rutClean[i]) * multiplier;
            multiplier++;
            if (multiplier > 7) {
                multiplier = 2;
            }
        }

        const calculatedDv = 11 - (sum % 11);
        let expectedDv = '';

        if (calculatedDv === 11) {
            expectedDv = '0';
        } else if (calculatedDv === 10) {
            expectedDv = 'K';
        } else {
            expectedDv = calculatedDv.toString();
        }
        return expectedDv === dv;
    };
    return { formatRut, validateRut };
}

