/*
 * JS propio del Sistema LAS. jQuery, Bootstrap 4, AdminLTE y los plugins (DataTables, Select2,
 * SweetAlert2, Tempus Dominus, Inputmask, bs-custom-file-input) los carga AdminLTE desde public/vendor.
 * Este módulo se ejecuta después de esos scripts y antes de DOMContentLoaded.
 */
import './bootstrap';
import { iniciarDataTables } from './datatables-es';
import { iniciarConfirmaciones } from './confirmar';
import { iniciarEnvioFormularios } from './form-submit';
import { iniciarSelect2 } from './select2-init';
import { iniciarMascaras } from './mascaras';
import { iniciarFechas } from './fechas';

const $ = window.jQuery;

if ($) {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    $(() => {
        iniciarDataTables();
        iniciarConfirmaciones();
        iniciarEnvioFormularios();
        iniciarSelect2(document);
        iniciarMascaras(document);
        iniciarFechas(document);

        $('body').tooltip({ selector: '[data-toggle="tooltip"]', trigger: 'hover focus' });

        if (window.bsCustomFileInput) {
            window.bsCustomFileInput.init();
        }
    });
}
