{{-- Contraseña + confirmación (restablecer y definir contraseña). --}}
<x-form.input name="password" type="password" label="Nueva contraseña" :requerido="true" autocomplete="new-password"
              ayuda="Mínimo 8 caracteres, una letra mayúscula y un número" />
<x-form.input name="password_confirmation" type="password" label="Confirmar contraseña" :requerido="true" autocomplete="new-password" />
