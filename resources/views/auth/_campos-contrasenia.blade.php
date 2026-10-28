{{-- Contraseña + confirmación (restablecer y definir contraseña). --}}
<x-form.input name="password" type="password" label="Nueva contraseña" :requerido="true" autocomplete="new-password"
              :ayuda="\App\Rules\ContraseniaSegura::AYUDA" />
<x-form.input name="password_confirmation" type="password" label="Confirmar contraseña" :requerido="true" autocomplete="new-password" />
